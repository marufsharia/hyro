<?php

namespace Marufsharia\Hyro\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Marufsharia\Hyro\Http\Requests\Api\RoleCreateRequest;
use Marufsharia\Hyro\Http\Requests\Api\RoleUpdateRequest;
use Marufsharia\Hyro\Http\Resources\RoleResource;
use Marufsharia\Hyro\Http\Resources\UserResource;
use Marufsharia\Hyro\Models\Privilege;
use Marufsharia\Hyro\Models\Role;

class RoleController extends BaseController
{
    public function __construct()
    {
        $this->middleware('hyro.privilege:roles.view')->only(['index', 'show']);
        $this->middleware('hyro.privilege:roles.create')->only(['store']);
        $this->middleware('hyro.privilege:roles.update')->only(['update']);
        $this->middleware('hyro.privilege:roles.delete')->only(['destroy']);
    }

    /**
     * Get all roles.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            [$perPage, $page] = $this->getPaginationParams($request);
            [$sortBy, $sortOrder] = $this->getSortingParams($request);
            $filters = $this->getFilteringParams($request);

            $query = Role::query();

            // Apply filters
            if (isset($filters['slug'])) {
                $query->where('slug', 'like', "%{$filters['slug']}%");
            }

            if (isset($filters['name'])) {
                $query->where('name', 'like', "%{$filters['name']}%");
            }

            if (isset($filters['protected'])) {
                $query->where('is_protected', (bool) $filters['protected']);
            }

            if (isset($filters['system'])) {
                $query->where('is_system', (bool) $filters['system']);
            }

            // Apply sorting
            $query->orderBy($sortBy, $sortOrder);

            // Paginate results
            $roles = $query->paginate($perPage, ['*'], 'page', $page);

            // Include relationships if requested
            $include = $request->input('include', '');
            if (str_contains($include, 'privileges')) {
                $roles->load('privileges');
            }

            if (str_contains($include, 'users')) {
                $roles->load('users');
            }

            return $this->collectionResponse(
                RoleResource::collection($roles),
                'Roles retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Create a new role.
     */
    public function store(RoleCreateRequest $request): JsonResponse
    {
        try {
            $role = DB::transaction(function () use ($request) {
                $role = Role::create([
                    'slug' => $request->input('slug'),
                    'name' => $request->input('name'),
                    'description' => $request->input('description'),
                    'is_protected' => $request->input('is_protected', false),
                    'is_system' => $request->input('is_system', false),
                    'metadata' => $request->input('metadata'),
                ]);

                // Grant privileges if provided
                if ($request->has('privileges')) {
                    foreach ($request->input('privileges') as $privilege) {
                        $role->grantPrivilege(
                            $privilege['slug'],
                            $privilege['reason'] ?? null,
                            $privilege['conditions'] ?? null,
                            isset($privilege['expires_at']) ? now()->parse($privilege['expires_at']) : null
                        );
                    }
                }

                // Log the creation
                $this->logAuditAction('role_created', $role, [
                    'created_by' => $request->user()->id,
                    'privileges' => $request->input('privileges', []),
                ]);

                return $role;
            });

            return $this->resourceResponse(
                new RoleResource($role),
                'Role created successfully'
            )->setStatusCode(201);
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Get a specific role.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $role = Role::findOrFail($id);

            // Include relationships if requested
            $include = $request->input('include', '');
            if (str_contains($include, 'privileges')) {
                $role->load('privileges');
            }

            if (str_contains($include, 'users')) {
                $role->load('users');
            }

            return $this->resourceResponse(
                new RoleResource($role),
                'Role retrieved successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->handleModelNotFound('Role');
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Update a role.
     */
    public function update(RoleUpdateRequest $request, string $id): JsonResponse
    {
        try {
            $role = Role::findOrFail($id);

            // Store old values for audit log
            $oldValues = $role->toArray();

            $role = DB::transaction(function () use ($request, $role, $oldValues) {
                // Update role attributes
                if ($request->has('name')) {
                    $role->name = $request->input('name');
                }

                if ($request->has('description')) {
                    $role->description = $request->input('description');
                }

                if ($request->has('metadata')) {
                    $role->metadata = $request->input('metadata');
                }

                $role->save();

                // Log the update
                $this->logAuditAction('role_updated', $role, [
                    'old_values' => $oldValues,
                    'new_values' => $role->toArray(),
                    'updated_by' => $request->user()->id,
                ]);

                return $role;
            });

            return $this->resourceResponse(
                new RoleResource($role),
                'Role updated successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->handleModelNotFound('Role');
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Delete a role.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $role = Role::findOrFail($id);

            // Prevent deletion of protected roles
            if ($role->is_protected && !$request->input('force', false)) {
                return $this->errorResponse(
                    'Cannot delete protected role',
                    'protected_role',
                    403,
                    null,
                    ['role' => $role->slug]
                );
            }

            DB::transaction(function () use ($role, $request) {
                // Store role data for audit log before deletion
                $roleData = $role->toArray();
                $userCount = $role->users()->count();
                $privilegeCount = $role->privileges()->count();

                // Delete the role
                $role->delete();

                // Log the deletion
                $this->logAuditAction('role_deleted', null, [
                    'deleted_role' => $roleData,
                    'affected_users' => $userCount,
                    'affected_privileges' => $privilegeCount,
                    'deleted_by' => $request->user()->id,
                ]);
            });

            return $this->successResponse(null, 'Role deleted successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->handleModelNotFound('Role');
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Grant a privilege to a role.
     */
    public function grantPrivilege(Request $request, string $roleId): JsonResponse
    {
        try {
            $this->requirePrivilege('privileges.grant');

            $role = Role::findOrFail($roleId);

            $request->validate([
                'slug' => ['required', 'string', 'max:255'],
                'reason' => ['sometimes', 'string', 'max:500'],
                'conditions' => ['sometimes', 'array'],
                'expires_at' => ['sometimes', 'nullable', 'date', 'after:now'],
            ]);

            $role->grantPrivilege(
                $request->input('slug'),
                $request->input('reason'),
                $request->input('conditions'),
                $request->input('expires_at')
            );

            return $this->successResponse([
                'role' => new RoleResource($role),
                'privilege' => $request->input('slug'),
                'granted_at' => now()->toISOString(),
            ], 'Privilege granted successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->handleModelNotFound('Role');
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Revoke a privilege from a role.
     */
    public function revokePrivilege(Request $request, string $roleId, string $privilegeSlug): JsonResponse
    {
        try {
            $this->requirePrivilege('privileges.grant');

            $role = Role::findOrFail($roleId);

            $role->revokePrivilege($privilegeSlug);

            return $this->successResponse([
                'role' => new RoleResource($role),
                'privilege' => $privilegeSlug,
                'revoked_at' => now()->toISOString(),
            ], 'Privilege revoked successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->handleModelNotFound('Role');
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Get role's privileges.
     */
    public function privileges(Request $request, string $roleId): JsonResponse
    {
        try {
            $this->requirePrivilege('roles.view');

            $role = Role::findOrFail($roleId);

            $privileges = $role->getCachedPrivilegeSlugs();

            return $this->successResponse([
                'role_id' => $role->id,
                'privileges' => $privileges,
                'count' => count($privileges),
            ], 'Role privileges retrieved successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->handleModelNotFound('Role');
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Get users with this role.
     */
    public function users(Request $request, string $roleId): JsonResponse
    {
        try {
            $this->requirePrivilege('roles.view');

            $role = Role::findOrFail($roleId);

            [$perPage, $page] = $this->getPaginationParams($request);
            [$sortBy, $sortOrder] = $this->getSortingParams($request);

            $users = $role->users()
                ->orderBy($sortBy, $sortOrder)
                ->paginate($perPage, ['*'], 'page', $page);

            return $this->collectionResponse(
                UserResource::collection($users),
                'Role users retrieved successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->handleModelNotFound('Role');
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Check if role has a specific privilege.
     */
    public function hasPrivilege(Request $request, string $roleId, string $privilege): JsonResponse
    {
        try {
            $this->requirePrivilege('roles.view');

            $role = Role::findOrFail($roleId);

            $hasPrivilege = $role->hasPrivilege($privilege);

            return $this->successResponse([
                'role_id' => $role->id,
                'privilege' => $privilege,
                'has_privilege' => $hasPrivilege,
            ], 'Privilege check completed');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->handleModelNotFound('Role');
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }
}

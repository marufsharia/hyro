<?php

namespace Marufsharia\Hyro\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Marufsharia\Hyro\Http\Requests\Api\PrivilegeCreateRequest;
use Marufsharia\Hyro\Http\Requests\Api\PrivilegeUpdateRequest;
use Marufsharia\Hyro\Http\Resources\PrivilegeResource;
use Marufsharia\Hyro\Http\Resources\RoleResource;
use Marufsharia\Hyro\Models\Privilege;

class PrivilegeController extends BaseController
{
    public function __construct()
    {
        $this->middleware('hyro.privilege:privileges.view')->only(['index', 'show']);
        $this->middleware('hyro.privilege:privileges.create')->only(['store']);
        $this->middleware('hyro.privilege:privileges.update')->only(['update']);
        $this->middleware('hyro.privilege:privileges.delete')->only(['destroy']);
    }

    /**
     * Get all privileges.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            [$perPage, $page] = $this->getPaginationParams($request);
            [$sortBy, $sortOrder] = $this->getSortingParams($request);
            $filters = $this->getFilteringParams($request);

            $query = Privilege::query();

            // Apply filters
            if (isset($filters['slug'])) {
                $query->where('slug', 'like', "%{$filters['slug']}%");
            }

            if (isset($filters['name'])) {
                $query->where('name', 'like', "%{$filters['name']}%");
            }

            if (isset($filters['category'])) {
                $query->where('category', $filters['category']);
            }

            if (isset($filters['wildcard'])) {
                $query->where('is_wildcard', (bool) $filters['wildcard']);
            }

            if (isset($filters['protected'])) {
                $query->where('is_protected', (bool) $filters['protected']);
            }

            // Apply sorting
            $query->orderBy($sortBy, $sortOrder);

            // Paginate results
            $privileges = $query->paginate($perPage, ['*'], 'page', $page);

            // Include relationships if requested
            $include = $request->input('include', '');
            if (str_contains($include, 'roles')) {
                $privileges->load('roles');
            }

            return $this->collectionResponse(
                PrivilegeResource::collection($privileges),
                'Privileges retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Create a new privilege.
     */
    public function store(PrivilegeCreateRequest $request): JsonResponse
    {
        try {
            $privilege = DB::transaction(function () use ($request) {
                $isWildcard = str_contains($request->input('slug'), '*');

                $privilege = Privilege::create([
                    'slug' => $request->input('slug'),
                    'name' => $request->input('name'),
                    'description' => $request->input('description'),
                    'category' => $request->input('category'),
                    'priority' => $request->input('priority', 50),
                    'is_protected' => $request->input('is_protected', false),
                    'is_wildcard' => $isWildcard,
                    'wildcard_pattern' => $isWildcard ? $request->input('slug') : null,
                    'metadata' => $request->input('metadata'),
                ]);

                // Log the creation
                $this->logAuditAction('privilege_created', $privilege, [
                    'created_by' => $request->user()->id,
                ]);

                return $privilege;
            });

            return $this->resourceResponse(
                new PrivilegeResource($privilege),
                'Privilege created successfully'
            )->setStatusCode(201);
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Get a specific privilege.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $privilege = Privilege::findOrFail($id);

            // Include relationships if requested
            $include = $request->input('include', '');
            if (str_contains($include, 'roles')) {
                $privilege->load('roles');
            }

            return $this->resourceResponse(
                new PrivilegeResource($privilege),
                'Privilege retrieved successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->handleModelNotFound('Privilege');
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Update a privilege.
     */
    public function update(PrivilegeUpdateRequest $request, string $id): JsonResponse
    {
        try {
            $privilege = Privilege::findOrFail($id);

            // Store old values for audit log
            $oldValues = $privilege->toArray();

            $privilege = DB::transaction(function () use ($request, $privilege, $oldValues) {
                // Update privilege attributes
                if ($request->has('name')) {
                    $privilege->name = $request->input('name');
                }

                if ($request->has('description')) {
                    $privilege->description = $request->input('description');
                }

                if ($request->has('category')) {
                    $privilege->category = $request->input('category');
                }

                if ($request->has('priority')) {
                    $privilege->priority = $request->input('priority');
                }

                if ($request->has('metadata')) {
                    $privilege->metadata = $request->input('metadata');
                }

                $privilege->save();

                // Log the update
                $this->logAuditAction('privilege_updated', $privilege, [
                    'old_values' => $oldValues,
                    'new_values' => $privilege->toArray(),
                    'updated_by' => $request->user()->id,
                ]);

                return $privilege;
            });

            return $this->resourceResponse(
                new PrivilegeResource($privilege),
                'Privilege updated successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->handleModelNotFound('Privilege');
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Delete a privilege.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $privilege = Privilege::findOrFail($id);

            // Prevent deletion of protected privileges
            if ($privilege->is_protected && !$request->input('force', false)) {
                return $this->errorResponse(
                    'Cannot delete protected privilege',
                    'protected_privilege',
                    403,
                    null,
                    ['privilege' => $privilege->slug]
                );
            }

            DB::transaction(function () use ($privilege, $request) {
                // Store privilege data for audit log before deletion
                $privilegeData = $privilege->toArray();
                $roleCount = $privilege->roles()->count();

                // Delete the privilege
                $privilege->delete();

                // Log the deletion
                $this->logAuditAction('privilege_deleted', null, [
                    'deleted_privilege' => $privilegeData,
                    'affected_roles' => $roleCount,
                    'deleted_by' => $request->user()->id,
                ]);
            });

            return $this->successResponse(null, 'Privilege deleted successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->handleModelNotFound('Privilege');
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Get roles with this privilege.
     */
    public function roles(Request $request, string $privilegeId): JsonResponse
    {
        try {
            $this->requirePrivilege('privileges.view');

            $privilege = Privilege::findOrFail($privilegeId);

            [$perPage, $page] = $this->getPaginationParams($request);
            [$sortBy, $sortOrder] = $this->getSortingParams($request);

            $roles = $privilege->roles()
                ->orderBy($sortBy, $sortOrder)
                ->paginate($perPage, ['*'], 'page', $page);

            return $this->collectionResponse(
                RoleResource::collection($roles),
                'Privilege roles retrieved successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->handleModelNotFound('Privilege');
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Expand a wildcard privilege.
     */
    public function expand(Request $request, string $privilegeId): JsonResponse
    {
        try {
            $this->requirePrivilege('privileges.view');

            $privilege = Privilege::findOrFail($privilegeId);

            if (!$privilege->is_wildcard) {
                return $this->errorResponse(
                    'Privilege is not a wildcard',
                    'not_wildcard',
                    400,
                    null,
                    ['privilege' => $privilege->slug]
                );
            }

            $expanded = Privilege::expandWildcard($privilege->wildcard_pattern);

            return $this->successResponse([
                'wildcard' => $privilege->slug,
                'expanded' => $expanded,
                'count' => count($expanded),
            ], 'Wildcard privilege expanded successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->handleModelNotFound('Privilege');
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Check if privilege matches a specific slug.
     */
    public function matches(Request $request, string $privilegeId, string $slug): JsonResponse
    {
        try {
            $this->requirePrivilege('privileges.view');

            $privilege = Privilege::findOrFail($privilegeId);

            $matches = $privilege->matches($slug);

            return $this->successResponse([
                'privilege_id' => $privilege->id,
                'privilege_slug' => $privilege->slug,
                'test_slug' => $slug,
                'matches' => $matches,
                'is_wildcard' => $privilege->is_wildcard,
            ], 'Privilege match check completed');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->handleModelNotFound('Privilege');
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Get privilege categories.
     */
    public function categories(Request $request): JsonResponse
    {
        try {
            $this->requirePrivilege('privileges.view');

            $categories = Privilege::distinct()
                ->whereNotNull('category')
                ->pluck('category')
                ->toArray();

            return $this->successResponse([
                'categories' => $categories,
                'count' => count($categories),
            ], 'Privilege categories retrieved successfully');
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Search privileges.
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $this->requirePrivilege('privileges.view');

            $request->validate([
                'q' => ['required', 'string', 'min:2', 'max:255'],
            ]);

            $query = $request->input('q');

            $privileges = Privilege::where('slug', 'like', "%{$query}%")
                ->orWhere('name', 'like', "%{$query}%")
                ->orWhere('description', 'like', "%{$query}%")
                ->limit(20)
                ->get();

            return $this->collectionResponse(
                PrivilegeResource::collection($privileges),
                'Privileges search completed'
            );
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }
}

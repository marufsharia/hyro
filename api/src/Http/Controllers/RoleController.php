<?php

namespace Marufsharia\Hyro\Api\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Marufsharia\Hyro\Api\Http\Resources\RoleResource;
use Marufsharia\Hyro\Api\Http\Resources\UserResource;
use Marufsharia\Hyro\Models\Role;

class RoleController extends BaseCrudController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('hyro.privilege:roles.view')->only(['index', 'show']);
        $this->middleware('hyro.privilege:roles.create')->only(['store']);
        $this->middleware('hyro.privilege:roles.update')->only(['update']);
        $this->middleware('hyro.privilege:roles.delete')->only(['destroy']);
    }

    protected function getModelClass(): string
    {
        return Role::class;
    }

    protected function getResourceClass(): string
    {
        return RoleResource::class;
    }

    protected function getCreateRules(): array
    {
        return [
            'slug' => ['required', 'string', 'max:255', 'unique:' . config('hyro.database.tables.roles', 'hyro_roles') . ',slug'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'is_protected' => ['sometimes', 'boolean'],
            'is_system' => ['sometimes', 'boolean'],
            'metadata' => ['sometimes', 'nullable', 'array'],
            'privileges' => ['sometimes', 'array'],
            'privileges.*.slug' => ['required_with:privileges', 'string'],
            'privileges.*.reason' => ['sometimes', 'string'],
            'privileges.*.conditions' => ['sometimes', 'array'],
            'privileges.*.expires_at' => ['sometimes', 'date'],
        ];
    }

    protected function getUpdateRules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }

    protected function getSearchableFields(): array
    {
        return ['name', 'slug'];
    }

    protected function getFilterableFields(): array
    {
        return ['is_protected', 'is_system'];
    }

    protected function getIncludableRelationships(): array
    {
        return ['privileges', 'users'];
    }

    protected function afterCreate(Model $model, Request $request): void
    {
        // Grant privileges if provided
        if ($request->has('privileges')) {
            foreach ($request->input('privileges') as $privilege) {
                $model->grantPrivilege(
                    $privilege['slug'],
                    $privilege['reason'] ?? null,
                    $privilege['conditions'] ?? null,
                    isset($privilege['expires_at']) ? now()->parse($privilege['expires_at']) : null
                );
            }
        }
    }

    protected function beforeDelete(Model $model): void
    {
        // Prevent deletion of protected roles
        if ($model->is_protected && !request()->input('force', false)) {
            throw new \Exception('Cannot delete protected role');
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


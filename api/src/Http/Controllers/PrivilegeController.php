<?php

namespace Marufsharia\Hyro\Api\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Marufsharia\Hyro\Api\Http\Resources\PrivilegeResource;
use Marufsharia\Hyro\Api\Http\Resources\RoleResource;
use Marufsharia\Hyro\Models\Privilege;

class PrivilegeController extends BaseCrudController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('hyro.privilege:privileges.view')->only(['index', 'show']);
        $this->middleware('hyro.privilege:privileges.create')->only(['store']);
        $this->middleware('hyro.privilege:privileges.update')->only(['update']);
        $this->middleware('hyro.privilege:privileges.delete')->only(['destroy']);
    }

    protected function getModelClass(): string
    {
        return Privilege::class;
    }

    protected function getResourceClass(): string
    {
        return PrivilegeResource::class;
    }

    protected function getCreateRules(): array
    {
        return [
            'slug' => ['required', 'string', 'max:255', 'unique:' . config('hyro.database.tables.privileges', 'hyro_privileges') . ',slug'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'category' => ['sometimes', 'nullable', 'string', 'max:100'],
            'priority' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'is_protected' => ['sometimes', 'boolean'],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }

    protected function getUpdateRules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'category' => ['sometimes', 'nullable', 'string', 'max:100'],
            'priority' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }

    protected function getSearchableFields(): array
    {
        return ['name', 'slug', 'description'];
    }

    protected function getFilterableFields(): array
    {
        return ['category', 'is_wildcard', 'is_protected'];
    }

    protected function getIncludableRelationships(): array
    {
        return ['roles'];
    }

    protected function prepareDataForCreate(array $data): array
    {
        $isWildcard = str_contains($data['slug'], '*');
        
        return array_merge($data, [
            'priority' => $data['priority'] ?? 50,
            'is_protected' => $data['is_protected'] ?? false,
            'is_wildcard' => $isWildcard,
            'wildcard_pattern' => $isWildcard ? $data['slug'] : null,
        ]);
    }

    protected function beforeDelete(Model $model): void
    {
        // Prevent deletion of protected privileges
        if ($model->is_protected && !request()->input('force', false)) {
            throw new \Exception('Cannot delete protected privilege');
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


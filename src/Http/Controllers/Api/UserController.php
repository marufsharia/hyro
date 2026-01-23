<?php

namespace Marufsharia\Hyro\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Marufsharia\Hyro\Http\Requests\Api\UserCreateRequest;
use Marufsharia\Hyro\Http\Requests\Api\UserUpdateRequest;
use Marufsharia\Hyro\Http\Resources\UserResource;
use Marufsharia\Hyro\Http\Resources\UserSuspensionResource;
use Marufsharia\Hyro\Models\UserSuspension;
use Marufsharia\Hyro\Services\TokenSynchronizationService;

class UserController extends BaseController
{
    /**
     * The token synchronization service.
     */
    private TokenSynchronizationService $tokenService;

    public function __construct(TokenSynchronizationService $tokenService)
    {
        $this->tokenService = $tokenService;
        $this->middleware('hyro.privilege:users.view')->only(['index', 'show']);
        $this->middleware('hyro.privilege:users.create')->only(['store']);
        $this->middleware('hyro.privilege:users.update')->only(['update']);
        $this->middleware('hyro.privilege:users.delete')->only(['destroy']);
    }

    /**
     * Get all users.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $userModel = Config::get('hyro.models.user');

            [$perPage, $page] = $this->getPaginationParams($request);
            [$sortBy, $sortOrder] = $this->getSortingParams($request);
            $filters = $this->getFilteringParams($request);

            // Start query
            $query = $userModel::query();

            // Apply filters
            if (isset($filters['email'])) {
                $query->where('email', 'like', "%{$filters['email']}%");
            }

            if (isset($filters['name'])) {
                $query->where('name', 'like', "%{$filters['name']}%");
            }

            if (isset($filters['role'])) {
                $query->whereHas('roles', function ($q) use ($filters) {
                    $q->where('slug', $filters['role']);
                });
            }

            // Apply sorting
            $query->orderBy($sortBy, $sortOrder);

            // Paginate results
            $users = $query->paginate($perPage, ['*'], 'page', $page);

            // Include relationships if requested
            $include = $request->input('include', '');
            if (str_contains($include, 'roles')) {
                $users->load('roles');
            }

            if (str_contains($include, 'suspensions')) {
                $users->load('suspensions');
            }

            return $this->collectionResponse(
                UserResource::collection($users),
                'Users retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Create a new user.
     */
    public function store(UserCreateRequest $request): JsonResponse
    {
        try {
            $userModel = Config::get('hyro.models.user');

            $user = DB::transaction(function () use ($request, $userModel) {
                $user = $userModel::create([
                    'name' => $request->input('name'),
                    'email' => $request->input('email'),
                    'password' => Hash::make($request->input('password')),
                ]);

                // Assign roles if provided
                if ($request->has('roles')) {
                    $user->syncRoles($request->input('roles'), false);
                }

                // Assign default role if no roles provided
                if (!$request->has('roles') || empty($request->input('roles'))) {
                    $defaultRole = Config::get('hyro.registration.default_role', 'user');
                    if ($defaultRole) {
                        $user->assignRole($defaultRole, 'Auto-assigned on creation');
                    }
                }

                // Log the creation
                $this->logAuditAction('user_created', $user, [
                    'created_by' => $request->user()->id,
                    'roles' => $request->input('roles', []),
                ]);

                return $user;
            });

            return $this->resourceResponse(
                new UserResource($user),
                'User created successfully'
            )->setStatusCode(201);
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Get a specific user.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $userModel = Config::get('hyro.models.user');
            $user = $userModel::findOrFail($id);

            // Include relationships if requested
            $include = $request->input('include', '');
            if (str_contains($include, 'roles')) {
                $user->load('roles');
            }

            if (str_contains($include, 'suspensions')) {
                $user->load('suspensions');
            }

            if (str_contains($include, 'tokens')) {
                $user->load('tokens');
            }

            return $this->resourceResponse(
                new UserResource($user),
                'User retrieved successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->handleModelNotFound('User');
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Update a user.
     */
    public function update(UserUpdateRequest $request, string $id): JsonResponse
    {
        try {
            $userModel = Config::get('hyro.models.user');
            $user = $userModel::findOrFail($id);

            // Store old values for audit log
            $oldValues = $user->toArray();

            $user = DB::transaction(function () use ($request, $user) {
                // Update basic info
                if ($request->has('name')) {
                    $user->name = $request->input('name');
                }

                if ($request->has('email')) {
                    $user->email = $request->input('email');
                }

                if ($request->has('password')) {
                    $user->password = Hash::make($request->input('password'));
                }

                $user->save();

                // Update roles if provided
                if ($request->has('roles')) {
                    $user->syncRoles($request->input('roles'), $request->input('detach_existing', true));
                }

                // Log the update
                $this->logAuditAction('user_updated', $user, [
                    'old_values' => $oldValues,
                    'new_values' => $user->toArray(),
                    'updated_by' => $request->user()->id,
                    'roles_updated' => $request->has('roles'),
                ]);

                return $user;
            });

            return $this->resourceResponse(
                new UserResource($user),
                'User updated successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->handleModelNotFound('User');
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Delete a user.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $userModel = Config::get('hyro.models.user');
            $user = $userModel::findOrFail($id);

            // Prevent deleting yourself
            if ($user->id === $request->user()->id) {
                return $this->errorResponse(
                    'You cannot delete your own account',
                    'self_deletion_prevented',
                    403
                );
            }

            DB::transaction(function () use ($user, $request) {
                // Store user data for audit log before deletion
                $userData = $user->toArray();

                // Revoke all tokens first
                $tokenCount = $user->tokens()->count();
                $user->tokens()->delete();

                // Delete the user
                $user->delete();

                // Log the deletion
                $this->logAuditAction('user_deleted', null, [
                    'deleted_user' => $userData,
                    'token_count' => $tokenCount,
                    'deleted_by' => $request->user()->id,
                ]);
            });

            return $this->successResponse(null, 'User deleted successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->handleModelNotFound('User');
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Assign a role to a user.
     */
    public function assignRole(Request $request, string $userId): JsonResponse
    {
        try {
            $this->requirePrivilege('roles.assign');

            $userModel = Config::get('hyro.models.user');
            $user = $userModel::findOrFail($userId);

            $request->validate([
                'role' => ['required', 'string', 'max:255'],
                'reason' => ['sometimes', 'string', 'max:500'],
                'expires_at' => ['sometimes', 'nullable', 'date', 'after:now'],
            ]);

            $user->assignRole(
                $request->input('role'),
                $request->input('reason'),
                $request->input('expires_at')
            );

            // Sync tokens if enabled
            $this->tokenService->checkAndSyncIfNeeded($user);

            return $this->successResponse([
                'user' => new UserResource($user),
                'role' => $request->input('role'),
                'assigned_at' => now()->toISOString(),
            ], 'Role assigned successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->handleModelNotFound('User');
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Remove a role from a user.
     */
    public function removeRole(Request $request, string $userId, string $role): JsonResponse
    {
        try {
            $this->requirePrivilege('roles.assign');

            $userModel = Config::get('hyro.models.user');
            $user = $userModel::findOrFail($userId);

            $user->removeRole($role);

            // Sync tokens if enabled
            $this->tokenService->checkAndSyncIfNeeded($user);

            return $this->successResponse([
                'user' => new UserResource($user),
                'role' => $role,
                'removed_at' => now()->toISOString(),
            ], 'Role removed successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->handleModelNotFound('User');
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Suspend a user.
     */
    public function suspend(Request $request, string $userId): JsonResponse
    {
        try {
            $this->requirePrivilege('users.suspend');

            $userModel = Config::get('hyro.models.user');
            $user = $userModel::findOrFail($userId);

            $request->validate([
                'reason' => ['required', 'string', 'max:500'],
                'details' => ['sometimes', 'string', 'max:1000'],
                'duration' => ['sometimes', 'nullable', 'integer', 'min:1'], // in minutes
            ]);

            $duration = $request->input('duration');
            $durationSeconds = $duration ? $duration * 60 : null;

            $user->suspend(
                $request->input('reason'),
                $request->input('details'),
                $durationSeconds
            );

            return $this->successResponse([
                'user' => new UserResource($user),
                'suspended_at' => now()->toISOString(),
                'suspended_until' => $durationSeconds
                    ? now()->addSeconds($durationSeconds)->toISOString()
                    : null,
                'reason' => $request->input('reason'),
            ], 'User suspended successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->handleModelNotFound('User');
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Unsuspend a user.
     */
    public function unsuspend(Request $request, string $userId): JsonResponse
    {
        try {
            $this->requirePrivilege('users.suspend');

            $userModel = Config::get('hyro.models.user');
            $user = $userModel::findOrFail($userId);

            $user->unsuspend();

            return $this->successResponse([
                'user' => new UserResource($user),
                'unsuspended_at' => now()->toISOString(),
            ], 'User unsuspended successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->handleModelNotFound('User');
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Get user's suspensions.
     */
    public function suspensions(Request $request, string $userId): JsonResponse
    {
        try {
            $this->requirePrivilege('users.view');

            $userModel = Config::get('hyro.models.user');
            $user = $userModel::findOrFail($userId);

            [$perPage, $page] = $this->getPaginationParams($request);
            [$sortBy, $sortOrder] = $this->getSortingParams($request);

            $suspensions = $user->suspensions()
                ->orderBy($sortBy, $sortOrder)
                ->paginate($perPage, ['*'], 'page', $page);

            return $this->collectionResponse(
                UserSuspensionResource::collection($suspensions),
                'Suspensions retrieved successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->handleModelNotFound('User');
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Get user's active suspension.
     */
    public function activeSuspension(Request $request, string $userId): JsonResponse
    {
        try {
            $this->requirePrivilege('users.view');

            $userModel = Config::get('hyro.models.user');
            $user = $userModel::findOrFail($userId);

            $suspension = $user->activeSuspension();

            if (!$suspension) {
                return $this->successResponse([
                    'active_suspension' => null,
                ], 'No active suspension found');
            }

            return $this->resourceResponse(
                new UserSuspensionResource($suspension),
                'Active suspension retrieved successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->handleModelNotFound('User');
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Get user's roles.
     */
    public function roles(Request $request, string $userId): JsonResponse
    {
        try {
            $this->requirePrivilege('users.view');

            $userModel = Config::get('hyro.models.user');
            $user = $userModel::findOrFail($userId);

            $roles = $user->hyroRoleSlugs();

            return $this->successResponse([
                'user_id' => $user->id,
                'roles' => $roles,
                'count' => count($roles),
            ], 'User roles retrieved successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->handleModelNotFound('User');
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Get user's privileges.
     */
    public function privileges(Request $request, string $userId): JsonResponse
    {
        try {
            $this->requirePrivilege('users.view');

            $userModel = Config::get('hyro.models.user');
            $user = $userModel::findOrFail($userId);

            $privileges = $user->hyroPrivilegeSlugs();

            return $this->successResponse([
                'user_id' => $user->id,
                'privileges' => $privileges,
                'count' => count($privileges),
            ], 'User privileges retrieved successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->handleModelNotFound('User');
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Sync user's tokens.
     */
    public function syncTokens(Request $request, string $userId): JsonResponse
    {
        try {
            $this->requirePrivilege('tokens.update');

            $userModel = Config::get('hyro.models.user');
            $user = $userModel::findOrFail($userId);

            $this->tokenService->checkAndSyncIfNeeded($user);

            return $this->successResponse([
                'user_id' => $user->id,
                'synced_at' => now()->toISOString(),
            ], 'User tokens synced successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->handleModelNotFound('User');
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Revoke all user's tokens.
     */
    public function revokeAllTokens(Request $request, string $userId): JsonResponse
    {
        try {
            $this->requirePrivilege('tokens.delete');

            $userModel = Config::get('hyro.models.user');
            $user = $userModel::findOrFail($userId);

            $tokenCount = $user->tokens()->count();
            $user->tokens()->delete();

            return $this->successResponse([
                'user_id' => $user->id,
                'tokens_revoked' => $tokenCount,
                'revoked_at' => now()->toISOString(),
            ], "All {$tokenCount} tokens revoked successfully");
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->handleModelNotFound('User');
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }
}

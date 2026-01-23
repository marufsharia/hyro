<?php

namespace Marufsharia\Hyro\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Marufsharia\Hyro\Http\Requests\Api\LoginRequest;
use Marufsharia\Hyro\Http\Requests\Api\RegisterRequest;
use Marufsharia\Hyro\Http\Requests\Api\TokenCreateRequest;
use Marufsharia\Hyro\Http\Resources\TokenResource;
use Marufsharia\Hyro\Http\Resources\UserResource;
use Marufsharia\Hyro\Services\TokenSynchronizationService;

class AuthController extends BaseController
{
    /**
     * The token synchronization service.
     */
    private TokenSynchronizationService $tokenService;

    public function __construct(TokenSynchronizationService $tokenService)
    {
        $this->tokenService = $tokenService;

        // Auth endpoints don't require authentication except logout
        $this->middleware('auth:sanctum')->only(['logout', 'tokens', 'createToken', 'revokeToken']);
    }

    /**
     * Authenticate user and return token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $credentials = $request->only('email', 'password');

            if (!Auth::attempt($credentials)) {
                return $this->errorResponse(
                    'Invalid credentials',
                    'authentication_failed',
                    401
                );
            }

            $user = Auth::user();

            // Check if user is suspended
            if (method_exists($user, 'isSuspended') && $user->isSuspended()) {
                Auth::logout();

                $suspension = $user->activeSuspension();
                return $this->errorResponse(
                    'Account suspended',
                    'account_suspended',
                    403,
                    null,
                    [
                        'reason' => $suspension?->reason,
                        'suspended_until' => $suspension?->suspended_until?->toISOString(),
                    ]
                );
            }

            // Create token
            $token = $this->tokenService->createToken(
                $user,
                'API Login',
                $request->input('abilities', []),
                $request->input('expires_at')
            );

            // Log the login
            $this->logAuditAction('user_logged_in', $user, [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return $this->successResponse([
                'user' => new UserResource($user),
                'token' => $token,
                'token_type' => 'bearer',
                'expires_at' => $request->input('expires_at'),
            ], 'Login successful');
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $userModel = Config::get('hyro.models.user');

            $user = $userModel::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
            ]);

            // Assign default role if configured
            $defaultRole = Config::get('hyro.registration.default_role', 'user');
            if ($defaultRole && method_exists($user, 'assignRole')) {
                $user->assignRole($defaultRole, 'Auto-assigned on registration');
            }

            // Create token for the new user
            $token = $this->tokenService->createToken(
                $user,
                'Registration Token',
                ['*'], // Default abilities
                now()->addDays(30)
            );

            // Log the registration
            $this->logAuditAction('user_registered', $user);

            return $this->successResponse([
                'user' => new UserResource($user),
                'token' => $token,
                'token_type' => 'bearer',
                'expires_at' => now()->addDays(30)->toISOString(),
            ], 'Registration successful', 201);
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Logout the authenticated user (revoke current token).
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Revoke the current token
            $currentToken = $user->currentAccessToken();
            $currentToken->delete();

            // Log the logout
            $this->logAuditAction('user_logged_out', $user);

            return $this->successResponse(null, 'Logged out successfully');
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Get the authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $include = $request->input('include', '');

            // Build response data
            $data = ['user' => new UserResource($user)];

            // Include additional data if requested
            if (str_contains($include, 'roles') && method_exists($user, 'roles')) {
                $data['roles'] = $user->hyroRoleSlugs();
            }

            if (str_contains($include, 'privileges') && method_exists($user, 'hyroPrivilegeSlugs')) {
                $data['privileges'] = $user->hyroPrivilegeSlugs();
            }

            if (str_contains($include, 'suspension') && method_exists($user, 'activeSuspension')) {
                $data['suspension'] = $user->activeSuspension();
            }

            return $this->successResponse($data, 'User retrieved successfully');
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Refresh the current token.
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Revoke current token
            $currentToken = $user->currentAccessToken();
            $currentToken->delete();

            // Create new token
            $newToken = $this->tokenService->createToken(
                $user,
                'Refreshed Token',
                $currentToken->abilities,
                now()->addDays(30)
            );

            // Log the token refresh
            $this->logAuditAction('token_refreshed', $user);

            return $this->successResponse([
                'token' => $newToken,
                'token_type' => 'bearer',
                'expires_at' => now()->addDays(30)->toISOString(),
            ], 'Token refreshed successfully');
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Get all tokens for the authenticated user.
     */
    public function tokens(Request $request): JsonResponse
    {
        try {
            $this->requirePrivilege('tokens.view');

            $user = $request->user();
            $tokens = $user->tokens()->paginate($this->perPage);

            return $this->collectionResponse(
                TokenResource::collection($tokens),
                'Tokens retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Create a new API token.
     */
    public function createToken(TokenCreateRequest $request): JsonResponse
    {
        try {
            $this->requirePrivilege('tokens.create');

            $user = $request->user();

            $token = $this->tokenService->createToken(
                $user,
                $request->input('name'),
                $request->input('abilities', []),
                $request->input('expires_at')
            );

            // Log token creation
            $this->logAuditAction('token_created', $user, [
                'name' => $request->input('name'),
                'abilities' => $request->input('abilities', []),
                'expires_at' => $request->input('expires_at'),
            ]);

            return $this->successResponse([
                'token' => $token,
                'token_type' => 'bearer',
                'expires_at' => $request->input('expires_at'),
            ], 'Token created successfully', 201);
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Revoke a specific token.
     */
    public function revokeToken(Request $request, string $tokenId): JsonResponse
    {
        try {
            $this->requirePrivilege('tokens.delete');

            $user = $request->user();
            $token = $user->tokens()->findOrFail($tokenId);

            $token->delete();

            // Log token revocation
            $this->logAuditAction('token_revoked', $user, [
                'token_id' => $tokenId,
            ]);

            return $this->successResponse(null, 'Token revoked successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->handleModelNotFound('Token');
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Revoke all tokens for the authenticated user.
     */
    public function revokeAllTokens(Request $request): JsonResponse
    {
        try {
            $this->requirePrivilege('tokens.delete');

            $user = $request->user();
            $tokenCount = $user->tokens()->count();

            $user->tokens()->delete();

            // Log mass token revocation
            $this->logAuditAction('all_tokens_revoked', $user, [
                'token_count' => $tokenCount,
            ]);

            return $this->successResponse(null, "All {$tokenCount} tokens revoked successfully");
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Check if the user has a specific privilege.
     */
    public function checkPrivilege(Request $request, string $privilege): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return $this->errorResponse('Unauthorized', 'unauthorized', 401);
            }

            $hasPrivilege = method_exists($user, 'hasPrivilege') && $user->hasPrivilege($privilege);

            return $this->successResponse([
                'privilege' => $privilege,
                'has_privilege' => $hasPrivilege,
                'user_id' => $user->id,
            ], 'Privilege check completed');
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Check if the user has a specific role.
     */
    public function checkRole(Request $request, string $role): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return $this->errorResponse('Unauthorized', 'unauthorized', 401);
            }

            $hasRole = method_exists($user, 'hasRole') && $user->hasRole($role);

            return $this->successResponse([
                'role' => $role,
                'has_role' => $hasRole,
                'user_id' => $user->id,
            ], 'Role check completed');
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }
}

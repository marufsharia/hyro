<?php

namespace Marufsharia\Hyro\Http\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Marufsharia\Hyro\Models\AuditLog;
use Symfony\Component\HttpFoundation\Response;

abstract class HyroMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$requirements): Response
    {
        $user = Auth::user();

        // If no user is authenticated, deny access
        if (!$user) {
            return $this->unauthorizedResponse($request, 'No authenticated user');
        }

        // Check if user is suspended
        if (method_exists($user, 'isSuspended') && $user->isSuspended()) {
            return $this->suspendedResponse($request, $user);
        }

        // Perform the specific authorization check
        $authorized = $this->checkAuthorization($user, $requirements, $request);

        if (!$authorized) {
            return $this->unauthorizedResponse($request, $this->getFailureReason($user, $requirements));
        }

        // Log successful authorization if auditing is enabled
        $this->logAuthorization($request, $user, $requirements, true);

        return $next($request);
    }

    /**
     * Check if the user is authorized.
     */
    abstract protected function checkAuthorization($user, array $requirements, Request $request): bool;

    /**
     * Get the failure reason for logging.
     */
    abstract protected function getFailureReason($user, array $requirements): string;

    /**
     * Get the middleware name for logging.
     */
    abstract protected function getMiddlewareName(): string;

    /**
     * Handle unauthorized access.
     */
    protected function unauthorizedResponse(Request $request, string $reason): Response
    {
        $this->logAuthorization($request, Auth::user(), [], false, $reason);

        if ($request->expectsJson() || $request->is('api/*')) {
            return new JsonResponse([
                'error' => [
                    'code' => 'unauthorized',
                    'message' => $this->getErrorMessage('unauthorized'),
                    'details' => Config::get('app.debug') ? $reason : null,
                    'timestamp' => now()->toISOString(),
                    'request_id' => $request->header('X-Request-ID') ?? uniqid(),
                ]
            ], Response::HTTP_FORBIDDEN, [
                'X-Hyro-Middleware' => $this->getMiddlewareName(),
                'X-Hyro-Reason' => $reason,
            ]);
        }

        // For web requests, throw AuthorizationException which Laravel will handle
        throw new AuthorizationException($this->getErrorMessage('unauthorized'));
    }

    /**
     * Handle suspended user access.
     */
    protected function suspendedResponse(Request $request, $user): Response
    {
        $suspension = $user->activeSuspension();
        $reason = $suspension ? $suspension->reason : 'User account is suspended';

        $this->logAuthorization($request, $user, [], false, 'user_suspended');

        if ($request->expectsJson() || $request->is('api/*')) {
            return new JsonResponse([
                'error' => [
                    'code' => 'account_suspended',
                    'message' => $this->getErrorMessage('suspended'),
                    'details' => $reason,
                    'suspended_until' => $suspension?->suspended_until?->toISOString(),
                    'timestamp' => now()->toISOString(),
                ]
            ], Response::HTTP_FORBIDDEN, [
                'X-Hyro-Middleware' => $this->getMiddlewareName(),
                'X-Hyro-Suspension' => 'active',
            ]);
        }

        // For web requests, redirect to suspension page
        return redirect()->route('hyro.suspended')
            ->with('error', $this->getErrorMessage('suspended'))
            ->with('suspension_reason', $reason);
    }

    /**
     * Get error message based on type.
     */
    protected function getErrorMessage(string $type): string
    {
        $messages = [
            'unauthorized' => Lang::get('hyro::messages.unauthorized',
                ['middleware' => $this->getMiddlewareName()]
            ),
            'suspended' => Lang::get('hyro::messages.suspended'),
            'invalid_role' => Lang::get('hyro::messages.invalid_role'),
            'invalid_privilege' => Lang::get('hyro::messages.invalid_privilege'),
            'invalid_ability' => Lang::get('hyro::messages.invalid_ability'),
        ];

        return $messages[$type] ?? 'Unauthorized access';
    }

    /**
     * Log authorization attempt.
     */
    protected function logAuthorization(
        Request $request,
                $user,
        array $requirements,
        bool $success,
        ?string $failureReason = null
    ): void {
        if (!Config::get('hyro.auditing.enabled', true)) {
            return;
        }

        $middlewareName = $this->getMiddlewareName();
        $event = $success ? 'middleware_authorized' : 'middleware_denied';

        AuditLog::log($event, $user, null, [
            'middleware' => $middlewareName,
            'requirements' => $requirements,
            'success' => $success,
            'failure_reason' => $failureReason,
            'path' => $request->path(),
            'method' => $request->method(),
        ], [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'tags' => ['middleware', $middlewareName],
        ]);
    }

    /**
     * Validate requirements array.
     */
    protected function validateRequirements(array $requirements, int $min = 1, ?int $max = null): void
    {
        if (count($requirements) < $min) {
            throw new \InvalidArgumentException(
                "{$this->getMiddlewareName()} middleware requires at least {$min} parameter(s)"
            );
        }

        if ($max !== null && count($requirements) > $max) {
            throw new \InvalidArgumentException(
                "{$this->getMiddlewareName()} middleware accepts at most {$max} parameter(s)"
            );
        }
    }
}

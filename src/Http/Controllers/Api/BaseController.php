<?php

namespace Marufsharia\Hyro\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use Marufsharia\Hyro\Models\AuditLog;

abstract class BaseController extends Controller
{
    /**
     * Default pagination per page.
     */
    protected int $perPage = 20;

    /**
     * Maximum per page limit.
     */
    protected int $maxPerPage = 100;

    /**
     * The audit event for this controller.
     */
    protected ?string $auditEvent = null;

    public function __construct()
    {
        $this->middleware('hyro.api.enabled');

        if (Config::get('hyro.api.rate_limit.enabled', true)) {
            $this->middleware('throttle:hyro-api');
        }
    }

    /**
     * Success response helper.
     */
    protected function successResponse(
        mixed $data = null,
        string $message = 'Success',
        int $status = 200,
        array $headers = []
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => [
                'timestamp' => now()->toISOString(),
                'version' => Config::get('hyro.api.version', '1.0'),
            ],
        ];

        // Add request ID if available
        if ($requestId = request()->header('X-Request-ID')) {
            $response['meta']['request_id'] = $requestId;
        }

        return response()->json($response, $status, $headers);
    }

    /**
     * Error response helper.
     */
    protected function errorResponse(
        string $message,
        string $code = 'error',
        int $status = 400,
        ?array $errors = null,
        ?array $details = null
    ): JsonResponse {
        $response = [
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message,
                'details' => $details,
            ],
            'meta' => [
                'timestamp' => now()->toISOString(),
            ],
        ];

        if ($errors) {
            $response['error']['errors'] = $errors;
        }

        // Add request ID if available
        if ($requestId = request()->header('X-Request-ID')) {
            $response['meta']['request_id'] = $requestId;
        }

        return response()->json($response, $status);
    }

    /**
     * Resource response helper.
     */
    protected function resourceResponse(JsonResource $resource, string $message = 'Success'): JsonResponse
    {
        return $this->successResponse($resource, $message);
    }

    /**
     * Collection response helper.
     */
    protected function collectionResponse(ResourceCollection $collection, string $message = 'Success'): JsonResponse
    {
        return $this->successResponse($collection, $message);
    }

    /**
     * Get pagination parameters from request.
     */
    protected function getPaginationParams(Request $request): array
    {
        $perPage = $request->get('per_page', $this->perPage);
        $page = $request->get('page', 1);

        // Validate and sanitize
        $perPage = min(max(1, (int) $perPage), $this->maxPerPage);
        $page = max(1, (int) $page);

        return [$perPage, $page];
    }

    /**
     * Get sorting parameters from request.
     */
    protected function getSortingParams(Request $request): array
    {
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = strtolower($request->get('sort_order', 'desc'));

        // Validate sort order
        $sortOrder = in_array($sortOrder, ['asc', 'desc']) ? $sortOrder : 'desc';

        return [$sortBy, $sortOrder];
    }

    /**
     * Get filtering parameters from request.
     */
    protected function getFilteringParams(Request $request): array
    {
        $filters = $request->except(['page', 'per_page', 'sort_by', 'sort_order', 'include']);

        // Remove empty filters
        return array_filter($filters, function ($value) {
            return $value !== null && $value !== '';
        });
    }

    /**
     * Log API action for auditing.
     */
    protected function logAuditAction(
        string $event,
        mixed $subject = null,
        ?array $changes = null,
        ?array $metadata = null
    ): void {
        if (!Config::get('hyro.auditing.enabled', true)) {
            return;
        }

        AuditLog::log($event, $subject, null, $changes, array_merge([
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
        ], $metadata ?? []));
    }

    /**
     * Validate the user has a specific privilege.
     */
    protected function requirePrivilege(string $privilege): void
    {
        $user = request()->user();

        if (!$user || !$user->hasPrivilege($privilege)) {
            abort(403, 'Insufficient privileges');
        }
    }

    /**
     * Validate the user has a specific role.
     */
    protected function requireRole(string $role): void
    {
        $user = request()->user();

        if (!$user || !$user->hasRole($role)) {
            abort(403, 'Insufficient privileges');
        }
    }

    /**
     * Validate the user has any of the given roles.
     */
    protected function requireAnyRole(array $roles): void
    {
        $user = request()->user();

        if (!$user || !$user->hasAnyRole($roles)) {
            abort(403, 'Insufficient privileges');
        }
    }

    /**
     * Build pagination links.
     */
    protected function buildPaginationLinks(string $path, array $queryParams, int $currentPage, int $lastPage): array
    {
        $links = [
            'first' => $this->buildPageUrl($path, $queryParams, 1),
            'last' => $this->buildPageUrl($path, $queryParams, $lastPage),
            'prev' => $currentPage > 1 ? $this->buildPageUrl($path, $queryParams, $currentPage - 1) : null,
            'next' => $currentPage < $lastPage ? $this->buildPageUrl($path, $queryParams, $currentPage + 1) : null,
        ];

        return array_filter($links);
    }

    /**
     * Build page URL with query parameters.
     */
    private function buildPageUrl(string $path, array $queryParams, int $page): string
    {
        $queryParams['page'] = $page;
        return url($path) . '?' . http_build_query($queryParams);
    }

    /**
     * Handle validation errors.
     */
    protected function handleValidationErrors(\Illuminate\Validation\ValidationException $e): JsonResponse
    {
        return $this->errorResponse(
            'Validation failed',
            'validation_error',
            422,
            $e->errors()
        );
    }

    /**
     * Handle model not found.
     */
    protected function handleModelNotFound(string $model = 'Resource'): JsonResponse
    {
        return $this->errorResponse(
            "{$model} not found",
            'not_found',
            404
        );
    }

    /**
     * Handle authorization errors.
     */
    protected function handleAuthorizationError(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->errorResponse(
            $message,
            'unauthorized',
            403
        );
    }

    /**
     * Handle server errors.
     */
    protected function handleServerError(\Exception $e): JsonResponse
    {
        $message = Config::get('app.debug')
            ? $e->getMessage()
            : 'An internal server error occurred';

        $details = Config::get('app.debug')
            ? ['trace' => $e->getTraceAsString()]
            : null;

        return $this->errorResponse(
            $message,
            'server_error',
            500,
            null,
            $details
        );
    }
}

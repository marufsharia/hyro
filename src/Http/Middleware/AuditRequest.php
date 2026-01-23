<?php

namespace Marufsharia\Hyro\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Marufsharia\Hyro\Models\AuditLog;
use Symfony\Component\HttpFoundation\Response;

class AuditRequest
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ?string $event = null): Response
    {
        $response = $next($request);

        // Only audit if enabled
        if (!Config::get('hyro.auditing.enabled', true)) {
            return $response;
        }

        // Determine event name
        $eventName = $event ?? $this->determineEventName($request, $response);

        // Skip if event is excluded
        if ($this->isExcludedEvent($eventName)) {
            return $response;
        }

        $this->logRequest($request, $response, $eventName);

        return $response;
    }

    /**
     * Determine event name based on request and response.
     */
    private function determineEventName(Request $request, Response $response): string
    {
        $method = $request->method();
        $status = $response->getStatusCode();

        // Map HTTP methods and status codes to events
        if ($status >= 400 && $status < 500) {
            return 'client_error';
        }

        if ($status >= 500) {
            return 'server_error';
        }

        $methodEvents = [
            'GET' => 'view',
            'POST' => 'create',
            'PUT' => 'update',
            'PATCH' => 'update',
            'DELETE' => 'delete',
        ];

        return $methodEvents[$method] ?? 'request';
    }

    /**
     * Check if event is excluded from auditing.
     */
    private function isExcludedEvent(string $event): bool
    {
        $excludedEvents = Config::get('hyro.auditing.exclude_events', [
            'view', // Often too noisy
        ]);

        // Also exclude by pattern
        $excludedPatterns = Config::get('hyro.auditing.exclude_patterns', [
            '*.css',
            '*.js',
            '*.png',
            '*.jpg',
            '*.ico',
        ]);

        foreach ($excludedPatterns as $pattern) {
            if (fnmatch($pattern, $event)) {
                return true;
            }
        }

        return in_array($event, $excludedEvents) || in_array('*', $excludedEvents);
    }

    /**
     * Log the request.
     */
    private function logRequest(Request $request, Response $response, string $event): void
    {
        $user = Auth::user();
        $statusCode = $response->getStatusCode();

        // Skip logging for certain status codes
        $excludedStatusCodes = Config::get('hyro.auditing.exclude_status_codes', [404]);
        if (in_array($statusCode, $excludedStatusCodes)) {
            return;
        }

        // Prepare data for logging
        $data = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'status_code' => $statusCode,
            'content_length' => $response->headers->get('Content-Length'),
            'duration' => $this->getRequestDuration(),
        ];

        // Include request data if configured (be careful with sensitive data!)
        if (Config::get('hyro.auditing.log_request_data', false)) {
            $data['request_data'] = $this->sanitizeRequestData($request);
        }

        // Include response data if configured
        if (Config::get('hyro.auditing.log_response_data', false) && $statusCode >= 400) {
            $data['response_data'] = $this->sanitizeResponseData($response);
        }

        AuditLog::log($event, $user, null, $data, [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'tags' => ['http_request', strtolower($request->method()), "status_{$statusCode}"],
            'description' => "{$request->method()} {$request->path()} → {$statusCode}",
        ]);
    }

    /**
     * Get request duration in milliseconds.
     */
    private function getRequestDuration(): float
    {
        if (defined('LARAVEL_START')) {
            return round((microtime(true) - LARAVEL_START) * 1000, 2);
        }

        return 0.0;
    }

    /**
     * Sanitize request data to remove sensitive information.
     */
    private function sanitizeRequestData(Request $request): array
    {
        $data = $request->all();

        // List of sensitive fields to redact
        $sensitiveFields = Config::get('hyro.auditing.sensitive_fields', [
            'password',
            'password_confirmation',
            'token',
            'api_key',
            'secret',
            'private_key',
            'credit_card',
            'ssn',
            'cvv',
        ]);

        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '***REDACTED***';
            }

            // Also check nested fields
            $this->redactNestedFields($data, $field);
        }

        return $data;
    }

    /**
     * Redact nested sensitive fields.
     */
    private function redactNestedFields(array &$data, string $field): void
    {
        foreach ($data as $key => &$value) {
            if (is_array($value)) {
                $this->redactNestedFields($value, $field);
            } elseif (is_string($key) && stripos($key, $field) !== false) {
                $value = '***REDACTED***';
            }
        }
    }

    /**
     * Sanitize response data.
     */
    private function sanitizeResponseData(Response $response): mixed
    {
        $content = $response->getContent();

        try {
            $data = json_decode($content, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                // Redact any sensitive fields in JSON response
                $sensitiveFields = Config::get('hyro.auditing.sensitive_fields', []);

                foreach ($sensitiveFields as $field) {
                    if (isset($data[$field])) {
                        $data[$field] = '***REDACTED***';
                    }
                }

                return $data;
            }
        } catch (\Exception $e) {
            // If not JSON or error, return truncated string
            return strlen($content) > 500 ? substr($content, 0, 500) . '...' : $content;
        }

        return null;
    }
}

<?php

namespace Marufsharia\Hyro\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Marufsharia\Hyro\Http\Requests\Api\SuspensionCreateRequest;
use Marufsharia\Hyro\Http\Resources\UserSuspensionResource;
use Marufsharia\Hyro\Models\UserSuspension;

class SuspensionController extends BaseController
{
    public function __construct()
    {
        $this->middleware('hyro.privilege:suspensions.view')->only(['index', 'show']);
        $this->middleware('hyro.privilege:suspensions.create')->only(['store']);
        $this->middleware('hyro.privilege:suspensions.update')->only(['update']);
    }

    /**
     * Get all suspensions.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            [$perPage, $page] = $this->getPaginationParams($request);
            [$sortBy, $sortOrder] = $this->getSortingParams($request);
            $filters = $this->getFilteringParams($request);

            $query = UserSuspension::query();

            // Apply filters
            if (isset($filters['user_id'])) {
                $query->where('user_id', $filters['user_id']);
            }

            if (isset($filters['active'])) {
                if ($filters['active'] === 'true' || $filters['active'] === '1') {
                    $query->active();
                } else {
                    $query->expired();
                }
            }

            if (isset($filters['reason'])) {
                $query->where('reason', 'like', "%{$filters['reason']}%");
            }

            if (isset($filters['suspended_by'])) {
                $query->where('suspended_by', $filters['suspended_by']);
            }

            // Apply sorting
            $query->orderBy($sortBy, $sortOrder);

            // Paginate results
            $suspensions = $query->paginate($perPage, ['*'], 'page', $page);

            // Include relationships if requested
            $include = $request->input('include', '');
            if (str_contains($include, 'user')) {
                $suspensions->load('user');
            }

            if (str_contains($include, 'suspender')) {
                $suspensions->load('suspender');
            }

            return $this->collectionResponse(
                UserSuspensionResource::collection($suspensions),
                'Suspensions retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Create a new suspension.
     */
    public function store(SuspensionCreateRequest $request): JsonResponse
    {
        try {
            $suspension = DB::transaction(function () use ($request) {
                $suspension = UserSuspension::create([
                    'user_id' => $request->input('user_id'),
                    'reason' => $request->input('reason'),
                    'details' => $request->input('details'),
                    'suspended_by' => $request->user()->id,
                    'suspended_until' => $request->input('suspended_until'),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'is_automatic' => $request->input('is_automatic', false),
                    'auto_reason_code' => $request->input('auto_reason_code'),
                ]);

                // Log the creation
                $this->logAuditAction('suspension_created', $suspension->user, [
                    'suspension_id' => $suspension->id,
                    'reason' => $suspension->reason,
                    'suspended_by' => $request->user()->id,
                ]);

                return $suspension;
            });

            return $this->resourceResponse(
                new UserSuspensionResource($suspension),
                'Suspension created successfully'
            )->setStatusCode(201);
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Get a specific suspension.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $suspension = UserSuspension::findOrFail($id);

            // Include relationships if requested
            $include = $request->input('include', '');
            if (str_contains($include, 'user')) {
                $suspension->load('user');
            }

            if (str_contains($include, 'suspender')) {
                $suspension->load('suspender');
            }

            if (str_contains($include, 'unsuspender')) {
                $suspension->load('unsuspender');
            }

            return $this->resourceResponse(
                new UserSuspensionResource($suspension),
                'Suspension retrieved successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->handleModelNotFound('Suspension');
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Update a suspension (unsuspend).
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $suspension = UserSuspension::findOrFail($id);

            $request->validate([
                'unsuspend' => ['required', 'boolean'],
            ]);

            if (!$request->input('unsuspend')) {
                return $this->errorResponse(
                    'Only unsuspension is supported via update',
                    'invalid_operation',
                    400
                );
            }

            // Check if already unsuspended
            if ($suspension->unsuspended_at) {
                return $this->errorResponse(
                    'Suspension is already unsuspended',
                    'already_unsuspended',
                    400
                );
            }

            $suspension = DB::transaction(function () use ($suspension, $request) {
                $suspension->unsuspend($request->user()->id);

                // Log the unsuspension
                $this->logAuditAction('suspension_updated', $suspension->user, [
                    'suspension_id' => $suspension->id,
                    'action' => 'unsuspended',
                    'unsuspended_by' => $request->user()->id,
                ]);

                return $suspension;
            });

            return $this->resourceResponse(
                new UserSuspensionResource($suspension),
                'Suspension unsuspended successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->handleModelNotFound('Suspension');
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Get active suspensions.
     */
    public function active(Request $request): JsonResponse
    {
        try {
            $this->requirePrivilege('suspensions.view');

            [$perPage, $page] = $this->getPaginationParams($request);

            $suspensions = UserSuspension::active()
                ->orderBy('suspended_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            // Include user relationship
            $suspensions->load('user');

            return $this->collectionResponse(
                UserSuspensionResource::collection($suspensions),
                'Active suspensions retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Get expired suspensions.
     */
    public function expired(Request $request): JsonResponse
    {
        try {
            $this->requirePrivilege('suspensions.view');

            [$perPage, $page] = $this->getPaginationParams($request);

            $suspensions = UserSuspension::expired()
                ->orderBy('suspended_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            // Include user relationship
            $suspensions->load('user');

            return $this->collectionResponse(
                UserSuspensionResource::collection($suspensions),
                'Expired suspensions retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Get suspension statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $this->requirePrivilege('suspensions.view');

            $total = UserSuspension::count();
            $active = UserSuspension::active()->count();
            $expired = UserSuspension::expired()->count();
            $manual = UserSuspension::where('is_automatic', false)->count();
            $automatic = UserSuspension::where('is_automatic', true)->count();

            // Get recent suspensions (last 30 days)
            $recent = UserSuspension::where('suspended_at', '>=', now()->subDays(30))
                ->count();

            // Get most common reasons
            $commonReasons = UserSuspension::select('reason', DB::raw('COUNT(*) as count'))
                ->groupBy('reason')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    return [
                        'reason' => $item->reason,
                        'count' => $item->count,
                    ];
                });

            return $this->successResponse([
                'total' => $total,
                'active' => $active,
                'expired' => $expired,
                'manual' => $manual,
                'automatic' => $automatic,
                'recent_30_days' => $recent,
                'common_reasons' => $commonReasons,
            ], 'Suspension statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }
}

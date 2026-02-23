<?php

namespace Marufsharia\Hyro\Api\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

abstract class BaseCrudController extends BaseController
{
    /**
     * Get the model class for this controller.
     */
    abstract protected function getModelClass(): string;

    /**
     * Get the resource class for this controller.
     */
    abstract protected function getResourceClass(): string;

    /**
     * Get the validation rules for create.
     */
    abstract protected function getCreateRules(): array;

    /**
     * Get the validation rules for update.
     */
    abstract protected function getUpdateRules(): array;

    /**
     * Get the model name for messages.
     */
    protected function getModelName(): string
    {
        return class_basename($this->getModelClass());
    }

    /**
     * Get searchable fields for filtering.
     */
    protected function getSearchableFields(): array
    {
        return ['name'];
    }

    /**
     * Get filterable fields.
     */
    protected function getFilterableFields(): array
    {
        return [];
    }

    /**
     * Get relationships to include.
     */
    protected function getIncludableRelationships(): array
    {
        return [];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            [$perPage, $page] = $this->getPaginationParams($request);
            [$sortBy, $sortOrder] = $this->getSortingParams($request);
            $filters = $this->getFilteringParams($request);

            $modelClass = $this->getModelClass();
            $query = $modelClass::query();

            // Apply search filter
            if (isset($filters['search'])) {
                $this->applySearchFilter($query, $filters['search']);
                unset($filters['search']);
            }

            // Apply other filters
            $this->applyFilters($query, $filters);

            // Apply sorting
            $query->orderBy($sortBy, $sortOrder);

            // Paginate results
            $results = $query->paginate($perPage, ['*'], 'page', $page);

            // Include relationships if requested
            $this->loadRelationships($results, $request);

            $resourceClass = $this->getResourceClass();
            
            return $this->collectionResponse(
                $resourceClass::collection($results),
                $this->getModelName() . 's retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Store a newly created resource.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate($this->getCreateRules());

            DB::beginTransaction();

            $modelClass = $this->getModelClass();
            $model = $modelClass::create($this->prepareDataForCreate($validated));

            $this->afterCreate($model, $request);

            DB::commit();

            $this->logAuditAction(
                strtolower($this->getModelName()) . '_created',
                $model,
                ['new_values' => $model->toArray()]
            );

            $resourceClass = $this->getResourceClass();
            
            return $this->resourceResponse(
                new $resourceClass($model),
                $this->getModelName() . ' created successfully'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->handleValidationErrors($e);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleServerError($e);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $modelClass = $this->getModelClass();
            $model = $modelClass::findOrFail($id);

            // Load relationships if requested
            $this->loadRelationshipsForModel($model, $request);

            $resourceClass = $this->getResourceClass();
            
            return $this->resourceResponse(
                new $resourceClass($model),
                $this->getModelName() . ' retrieved successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->handleModelNotFound($this->getModelName());
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    /**
     * Update the specified resource.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate($this->getUpdateRules());

            $modelClass = $this->getModelClass();
            $model = $modelClass::findOrFail($id);

            $oldValues = $model->toArray();

            DB::beginTransaction();

            $model->update($this->prepareDataForUpdate($validated));

            $this->afterUpdate($model, $request);

            DB::commit();

            $this->logAuditAction(
                strtolower($this->getModelName()) . '_updated',
                $model,
                [
                    'old_values' => $oldValues,
                    'new_values' => $model->getChanges(),
                ]
            );

            $resourceClass = $this->getResourceClass();
            
            return $this->resourceResponse(
                new $resourceClass($model->fresh()),
                $this->getModelName() . ' updated successfully'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->handleValidationErrors($e);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->handleModelNotFound($this->getModelName());
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleServerError($e);
        }
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $modelClass = $this->getModelClass();
            $model = $modelClass::findOrFail($id);

            $oldValues = $model->toArray();

            DB::beginTransaction();

            $this->beforeDelete($model);

            $model->delete();

            DB::commit();

            $this->logAuditAction(
                strtolower($this->getModelName()) . '_deleted',
                $model,
                ['old_values' => $oldValues]
            );

            return $this->successResponse(
                null,
                $this->getModelName() . ' deleted successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->handleModelNotFound($this->getModelName());
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleServerError($e);
        }
    }

    /**
     * Apply search filter to query.
     */
    protected function applySearchFilter($query, string $search): void
    {
        $searchableFields = $this->getSearchableFields();
        
        if (empty($searchableFields)) {
            return;
        }

        $query->where(function($q) use ($search, $searchableFields) {
            foreach ($searchableFields as $field) {
                $q->orWhere($field, 'like', "%{$search}%");
            }
        });
    }

    /**
     * Apply filters to query.
     */
    protected function applyFilters($query, array $filters): void
    {
        $filterableFields = $this->getFilterableFields();

        foreach ($filters as $key => $value) {
            if (in_array($key, $filterableFields)) {
                $query->where($key, $value);
            }
        }
    }

    /**
     * Load relationships for paginated results.
     */
    protected function loadRelationships($results, Request $request): void
    {
        $include = $request->input('include', '');
        $relationships = array_filter(explode(',', $include));
        $includable = $this->getIncludableRelationships();

        $toLoad = array_intersect($relationships, $includable);

        if (!empty($toLoad)) {
            $results->load($toLoad);
        }
    }

    /**
     * Load relationships for a single model.
     */
    protected function loadRelationshipsForModel(Model $model, Request $request): void
    {
        $include = $request->input('include', '');
        $relationships = array_filter(explode(',', $include));
        $includable = $this->getIncludableRelationships();

        $toLoad = array_intersect($relationships, $includable);

        if (!empty($toLoad)) {
            $model->load($toLoad);
        }
    }

    /**
     * Prepare data before creating.
     */
    protected function prepareDataForCreate(array $data): array
    {
        return $data;
    }

    /**
     * Prepare data before updating.
     */
    protected function prepareDataForUpdate(array $data): array
    {
        return $data;
    }

    /**
     * Hook called after creating a model.
     */
    protected function afterCreate(Model $model, Request $request): void
    {
        // Override in child classes if needed
    }

    /**
     * Hook called after updating a model.
     */
    protected function afterUpdate(Model $model, Request $request): void
    {
        // Override in child classes if needed
    }

    /**
     * Hook called before deleting a model.
     */
    protected function beforeDelete(Model $model): void
    {
        // Override in child classes if needed
    }
}

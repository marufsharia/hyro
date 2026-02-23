<?php

namespace Marufsharia\Hyro\Core\Repositories;

use Illuminate\Support\Str;

abstract class BaseRepository
{
    protected $model;

    public function __construct()
    {
        $this->model = $this->getModelClass();
    }

    /**
     * Get the model class for this repository.
     * Must be implemented by child classes.
     */
    abstract protected function getModelClass(): string;

    /**
     * Get all records with optional filters.
     */
    public function all(array $filters = [])
    {
        $query = $this->model::query();

        $this->applyFilters($query, $filters);

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Apply filters to the query.
     * Can be overridden by child classes for custom filtering.
     */
    protected function applyFilters($query, array $filters)
    {
        if (isset($filters['search'])) {
            $this->applySearchFilter($query, $filters['search']);
        }

        return $query;
    }

    /**
     * Apply search filter.
     * Can be overridden by child classes.
     */
    protected function applySearchFilter($query, string $search)
    {
        // Default implementation - override in child classes
        return $query;
    }

    /**
     * Find record by ID.
     */
    public function find($id)
    {
        return $this->model::find($id);
    }

    /**
     * Create a new record.
     */
    public function create(array $data)
    {
        $data = $this->prepareDataForCreate($data);
        return $this->model::create($data);
    }

    /**
     * Prepare data before creating.
     * Can be overridden by child classes.
     */
    protected function prepareDataForCreate(array $data): array
    {
        if (!isset($data['slug']) && isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        return $data;
    }

    /**
     * Update record.
     */
    public function update($id, array $data)
    {
        $record = $this->find($id);
        
        if (!$record) {
            return false;
        }

        $data = $this->prepareDataForUpdate($data);

        return $record->update($data);
    }

    /**
     * Prepare data before updating.
     * Can be overridden by child classes.
     */
    protected function prepareDataForUpdate(array $data): array
    {
        if (isset($data['name']) && !isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        return $data;
    }

    /**
     * Delete record.
     */
    public function delete($id)
    {
        $record = $this->find($id);
        
        if (!$record) {
            return false;
        }

        return $record->delete();
    }
}


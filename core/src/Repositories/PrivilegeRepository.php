<?php

namespace Marufsharia\Hyro\Core\Repositories;

class PrivilegeRepository extends BaseRepository
{
    /**
     * Get the model class for this repository.
     */
    protected function getModelClass(): string
    {
        return config('hyro.database.models.privilege', \Marufsharia\Hyro\Models\Privilege::class);
    }

    /**
     * Apply filters to the query.
     */
    protected function applyFilters($query, array $filters)
    {
        parent::applyFilters($query, $filters);

        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        return $query;
    }

    /**
     * Apply search filter to query.
     */
    protected function applySearchFilter($query, string $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('slug', 'like', "%{$search}%");
        });
    }

    /**
     * Find privilege by slug
     */
    public function findBySlug($slug)
    {
        return $this->model::where('slug', $slug)->first();
    }

    /**
     * Get privileges grouped by category
     */
    public function groupedByCategory()
    {
        return $this->model::all()->groupBy('category');
    }
}



<?php

namespace Marufsharia\Hyro\Core\Repositories;

class AuditRepository
{
    protected $model;

    public function __construct()
    {
        $this->model = config('hyro.database.models.audit_log', \Marufsharia\Hyro\Models\AuditLog::class);
    }

    /**
     * Get all audit logs
     */
    public function all(array $filters = [])
    {
        $query = $this->model::query()->with('user');

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (isset($filters['model_type'])) {
            $query->where('model_type', $filters['model_type']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Find audit log by ID
     */
    public function find($id)
    {
        return $this->model::find($id);
    }

    /**
     * Log an action
     */
    public function log(array $data)
    {
        return $this->model::create([
            'user_id' => $data['user_id'] ?? auth()->id(),
            'action' => $data['action'],
            'model_type' => $data['model_type'] ?? null,
            'model_id' => $data['model_id'] ?? null,
            'old_values' => $data['old_values'] ?? null,
            'new_values' => $data['new_values'] ?? null,
            'ip_address' => $data['ip_address'] ?? request()->ip(),
            'user_agent' => $data['user_agent'] ?? request()->userAgent(),
        ]);
    }

    /**
     * Get logs for a specific user
     */
    public function forUser($userId, $limit = 10)
    {
        return $this->model::where('user_id', $userId)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get logs for a specific model
     */
    public function forModel($modelType, $modelId, $limit = 10)
    {
        return $this->model::where('model_type', $modelType)
            ->where('model_id', $modelId)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Delete old logs
     */
    public function deleteOlderThan($days = 90)
    {
        return $this->model::where('created_at', '<', now()->subDays($days))->delete();
    }
}


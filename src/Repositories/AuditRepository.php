<?php


namespace Marufsharia\Hyro\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Marufsharia\Hyro\Models\AuditLog;

class AuditRepository
{
    protected string $auditModel;

    public function __construct(array $config)
    {
        $this->auditModel = $config['database']['models']['audit'] ?? AuditLog::class;
    }

    /**
     * Create a new audit log entry.
     *
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model
    {
        return $this->auditModel::create($data);
    }

    /**
     * Get all audit logs
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all()
    {
        return $this->auditModel::orderBy('created_at', 'desc')->get();
    }

    /**
     * Get audit logs for a specific users
     *
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function forUser(int $userId)
    {
        return $this->auditModel::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get audit logs for a specific model type and ID
     *
     * @param string $modelType
     * @param int $modelId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function forModel(string $modelType, int $modelId)
    {
        return $this->auditModel::where('auditable_type', $modelType)
            ->where('auditable_id', $modelId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Delete an audit log entry
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $log = $this->auditModel::find($id);
        return $log ? $log->delete() : false;
    }

    /**
     * Clear all audit logs
     */
    public function clear(): int
    {
        return $this->auditModel::truncate();
    }
}

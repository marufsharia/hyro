<?php

namespace Marufsharia\Hyro\Database\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

abstract class BaseModel extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // Set the connection if configured
        if ($connection = config('hyro.database.connection')) {
            $this->setConnection($connection);
        }

        // Use UUIDs if enabled
        if (config('hyro.database.uuids.enabled', false)) {
            $this->usesUuids();
        }
    }

    protected function usesUuids(): void
    {
        if (in_array(HasUuids::class, class_uses_recursive(static::class))) {
            $this->setKeyType('string');
            $this->setIncrementing(false);
        } else {
            // Dynamically add HasUuids trait
            $this->usesUuids = true;
            $this->setKeyType('string');
            $this->setIncrementing(false);
        }
    }

    public function getTable(): string
    {
        // Use configurable table names
        $configKey = 'hyro.tables.' . $this->getTableConfigKey();
        return config($configKey, parent::getTable());
    }

    abstract protected function getTableConfigKey(): string;

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (config('hyro.database.uuids.enabled', false) && empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) \Illuminate\Support\Str::orderedUuid();
            }
        });
    }
}

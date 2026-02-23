<?php

namespace Marufsharia\Hyro\Core\Traits;

use Marufsharia\Hyro\Core\Facades\Hyro;

/**
 * Fires Hooks Trait
 * 
 * Automatically fires Hyro hooks for model events.
 */
trait FiresHooks
{
    /**
     * Boot the trait.
     */
    protected static function bootFiresHooks(): void
    {
        // Fire hooks on model events
        static::created(function ($model) {
            static::fireModelHook('created', $model);
        });

        static::updated(function ($model) {
            static::fireModelHook('updated', $model, $model->getOriginal());
        });

        static::deleted(function ($model) {
            static::fireModelHook('deleted', $model);
        });

        static::restored(function ($model) {
            static::fireModelHook('restored', $model);
        });
    }

    /**
     * Fire a model hook.
     */
    protected static function fireModelHook(string $event, $model, ?array $oldData = null): void
    {
        $modelName = class_basename($model);
        $hookName = 'hyro.' . strtolower($modelName) . '.' . $event;

        if ($oldData) {
            Hyro::doAction($hookName, $model, $oldData);
        } else {
            Hyro::doAction($hookName, $model);
        }
    }

    /**
     * Fire a custom hook for this model.
     */
    public function fireHook(string $action, mixed ...$args): void
    {
        $modelName = class_basename($this);
        $hookName = 'hyro.' . strtolower($modelName) . '.' . $action;

        Hyro::doAction($hookName, $this, ...$args);
    }

    /**
     * Apply filters to a value for this model.
     */
    public function applyFilter(string $filter, mixed $value, mixed ...$args): mixed
    {
        $modelName = class_basename($this);
        $hookName = 'hyro.' . strtolower($modelName) . '.' . $filter;

        return Hyro::applyFilters($hookName, $value, $this, ...$args);
    }
}


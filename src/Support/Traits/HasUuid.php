<?php

namespace Marufsharia\Hyro\Support\Traits;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

trait HasUuid
{
    /**
     * Boot the trait.
     */
    protected static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            if (Config::get('hyro.uuid.enabled', false)) {
                $uuidColumn = Config::get('hyro.uuid.column', 'uuid');

                if (empty($model->{$uuidColumn})) {
                    $model->{$uuidColumn} = match (Config::get('hyro.uuid.version', 4)) {
                        7 => Str::ulid()->toBase32(),
                        default => Str::uuid()->toString(),
                    };
                }
            }
        });
    }

    /**
     * Get the route key name for the model.
     */
    public function getRouteKeyName(): string
    {
        if (Config::get('hyro.uuid.enabled', false)) {
            return Config::get('hyro.uuid.column', 'uuid');
        }

        return parent::getRouteKeyName();
    }

    /**
     * Get the value of the model's route key.
     */
    public function getRouteKey()
    {
        if (Config::get('hyro.uuid.enabled', false)) {
            return $this->getAttribute(Config::get('hyro.uuid.column', 'uuid'));
        }

        return parent::getRouteKey();
    }
}

<?php

namespace Marufsharia\Hyro\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class HyroSetting extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'hyro_settings';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'value' => 'array',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Clear cache when setting is saved or deleted
        static::saved(function ($setting) {
            Cache::forget("hyro_setting_{$setting->key}");
            Cache::forget('hyro_all_settings');
        });

        static::deleted(function ($setting) {
            Cache::forget("hyro_setting_{$setting->key}");
            Cache::forget('hyro_all_settings');
        });
    }

    /**
     * Get a setting value by key.
     */
    public static function getValue(string $key, $default = null)
    {
        $cacheKey = "hyro_setting_{$key}";

        return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set a setting value by key.
     */
    public static function setValue(string $key, $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Get all settings as key-value pairs.
     */
    public static function getAllSettings(): array
    {
        return Cache::remember('hyro_all_settings', 3600, function () {
            return static::all()->pluck('value', 'key')->toArray();
        });
    }

    /**
     * Clear all settings cache.
     */
    public static function clearCache(): void
    {
        Cache::forget('hyro_all_settings');
        
        // Clear individual setting caches
        static::all()->each(function ($setting) {
            Cache::forget("hyro_setting_{$setting->key}");
        });
    }
}


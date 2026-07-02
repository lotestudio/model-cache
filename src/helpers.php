<?php

use Lotestudio\ModelCache\Contracts\ModelCacheInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

if (!function_exists('model_cache')) {
    /**
     * Get the model cache instance
     *
     * @return ModelCacheInterface
     */
    function model_cache(): ModelCacheInterface
    {
        return app(ModelCacheInterface::class);
    }
}

if (!function_exists('model_cached_all')) {
    /**
     * Get all cached models for a key as Collection
     *
     * @param string $key
     * @return Collection
     */
    function model_cached_all(string $key): Collection
    {
        return model_cache()->all($key);
    }
}

if (!function_exists('model_cached')) {
    /**
     * Get cached model by ID
     *
     * @param string $key
     * @param  int|string  $id
     * @return Model|null
     */
    function model_cached(string $key, int|string $id): ?Model
    {
        return model_cache()->find($key, $id);
    }
}

if (!function_exists('model_cached_find_by')) {
    /**
     * Find cached model by custom field
     *
     * @param string $key
     * @param string $field
     * @param mixed $value
     * @return Model|null
     */
    function model_cached_find_by(string $key, string $field, $value): ?Model
    {
        return model_cache()->findBy($key, $field, $value);
    }
}

if (!function_exists('model_cached_find_all_by')) {
    /**
     * Find all cached models matching a condition
     *
     * @param string $key
     * @param string $field
     * @param mixed $value
     * @return Collection
     */
    function model_cached_find_all_by(string $key, string $field, $value): Collection
    {
        return model_cache()->findAllBy($key, $field, $value);
    }
}

if (!function_exists('model_cached_pluck')) {
    /**
     * Pluck values from cached models
     *
     * @param string $key
     * @param string $column
     * @param string|null $keyColumn
     * @return array
     */
    function model_cached_pluck(string $key, string $column, ?string $keyColumn = null): array
    {
        return model_cache()->pluck($key, $column, $keyColumn);
    }
}

if (!function_exists('model_cached_first')) {
    /**
     * Get first cached model
     *
     * @param string $key
     * @return Model|null
     */
    function model_cached_first(string $key): ?Model
    {
        return model_cache()->first($key);
    }
}

if (!function_exists('model_cached_last')) {
    /**
     * Get last cached model
     *
     * @param string $key
     * @return Model|null
     */
    function model_cached_last(string $key): ?Model
    {
        return model_cache()->last($key);
    }
}

if (!function_exists('model_cached_count')) {
    /**
     * Count cached models
     *
     * @param string $key
     * @return int
     */
    function model_cached_count(string $key): int
    {
        return model_cache()->count($key);
    }
}

if (!function_exists('model_cached_exists')) {
    /**
     * Check if cached model exists
     *
     * @param string $key
     * @param int|string $id
     * @return bool
     */
    function model_cached_exists(string $key, $id): bool
    {
        return model_cache()->exists($key, $id);
    }
}

if (!function_exists('model_cached_is_empty')) {
    /**
     * Check if cached collection is empty
     *
     * @param string $key
     * @return bool
     */
    function model_cached_is_empty(string $key): bool
    {
        return model_cache()->isEmpty($key);
    }
}

if (!function_exists('model_cached_is_not_empty')) {
    /**
     * Check if cached collection is not empty
     *
     * @param string $key
     * @return bool
     */
    function model_cached_is_not_empty(string $key): bool
    {
        return model_cache()->isNotEmpty($key);
    }
}

if (!function_exists('model_cache_clear')) {
    /**
     * Clear cache for a specific model
     *
     * @param string $key
     * @return bool
     */
    function model_cache_clear(string $key): bool
    {
        return model_cache()->clear($key);
    }
}

if (!function_exists('model_cache_clear_all')) {
    /**
     * Clear all model caches
     *
     * @return bool
     */
    function model_cache_clear_all(): bool
    {
        return model_cache()->clearAll();
    }
}

if (!function_exists('model_cache_warmup')) {
    /**
     * Warm up cache for a specific model
     *
     * @param string $key
     * @return bool
     */
    function model_cache_warmup(string $key): bool
    {
        return model_cache()->warmup($key);
    }
}

if (!function_exists('model_cache_warmup_all')) {
    /**
     * Warm up all model caches
     *
     * @return bool
     */
    function model_cache_warmup_all(): bool
    {
        return model_cache()->warmupAll();
    }
}

if (!function_exists('model_cache_stats')) {
    /**
     * Get cache statistics
     *
     * @param string|null $key
     * @return array
     */
    function model_cache_stats(?string $key = null): array
    {
        return model_cache()->stats($key);
    }
}

if (!function_exists('model_cache_register')) {
    /**
     * Register a new model for caching
     *
     * @param string $key
     * @param array $config
     * @return ModelCacheInterface
     */
    function model_cache_register(string $key, array $config): ModelCacheInterface
    {
        return model_cache()->register($key, $config);
    }
}

if (!function_exists('model_cache_get_config')) {
    /**
     * Get model configuration
     *
     * @param string $key
     * @return array|null
     */
    function model_cache_get_config(string $key): ?array
    {
        return model_cache()->getConfig($key);
    }
}

// ============================================================================
// Алиаси за по-кратък синтаксис
// ============================================================================

if (!function_exists('mc_all')) {
    /**
     * Alias for model_cached_all
     *
     * @param string $key
     * @return Collection
     */
    function mc_all(string $key): Collection
    {
        return model_cached_all($key);
    }
}

if (!function_exists('mc_find')) {
    /**
     * Alias for model_cached
     *
     * @param string $key
     * @param int|string $id
     * @return Model|null
     */
    function mc_find(string $key, $id): ?Model
    {
        return model_cached($key, $id);
    }
}

if (!function_exists('mc_find_by')) {
    /**
     * Alias for model_cached_find_by
     *
     * @param string $key
     * @param string $field
     * @param mixed $value
     * @return Model|null
     */
    function mc_find_by(string $key, string $field, $value): ?Model
    {
        return model_cached_find_by($key, $field, $value);
    }
}

if (!function_exists('mc_find_all_by')) {
    /**
     * Alias for model_cached_find_all_by
     *
     * @param string $key
     * @param string $field
     * @param mixed $value
     * @return Collection
     */
    function mc_find_all_by(string $key, string $field, $value): Collection
    {
        return model_cached_find_all_by($key, $field, $value);
    }
}

if (!function_exists('mc_pluck')) {
    /**
     * Alias for model_cached_pluck
     *
     * @param string $key
     * @param string $column
     * @param string|null $keyColumn
     * @return array
     */
    function mc_pluck(string $key, string $column, ?string $keyColumn = null): array
    {
        return model_cached_pluck($key, $column, $keyColumn);
    }
}

if (!function_exists('mc_first')) {
    /**
     * Alias for model_cached_first
     *
     * @param string $key
     * @return Model|null
     */
    function mc_first(string $key): ?Model
    {
        return model_cached_first($key);
    }
}

if (!function_exists('mc_last')) {
    /**
     * Alias for model_cached_last
     *
     * @param string $key
     * @return Model|null
     */
    function mc_last(string $key): ?Model
    {
        return model_cached_last($key);
    }
}

if (!function_exists('mc_count')) {
    /**
     * Alias for model_cached_count
     *
     * @param string $key
     * @return int
     */
    function mc_count(string $key): int
    {
        return model_cached_count($key);
    }
}

if (!function_exists('mc_exists')) {
    /**
     * Alias for model_cached_exists
     *
     * @param string $key
     * @param int|string $id
     * @return bool
     */
    function mc_exists(string $key, $id): bool
    {
        return model_cached_exists($key, $id);
    }
}

if (!function_exists('mc_clear')) {
    /**
     * Alias for model_cache_clear
     *
     * @param string $key
     * @return bool
     */
    function mc_clear(string $key): bool
    {
        return model_cache_clear($key);
    }
}

if (!function_exists('mc_clear_all')) {
    /**
     * Alias for model_cache_clear_all
     *
     * @return bool
     */
    function mc_clear_all(): bool
    {
        return model_cache_clear_all();
    }
}

if (!function_exists('mc_warmup')) {
    /**
     * Alias for model_cache_warmup
     *
     * @param string $key
     * @return bool
     */
    function mc_warmup(string $key): bool
    {
        return model_cache_warmup($key);
    }
}

if (!function_exists('mc_warmup_all')) {
    /**
     * Alias for model_cache_warmup_all
     *
     * @return bool
     */
    function mc_warmup_all(): bool
    {
        return model_cache_warmup_all();
    }
}

if (!function_exists('mc_stats')) {
    /**
     * Alias for model_cache_stats
     *
     * @param string|null $key
     * @return array
     */
    function mc_stats(?string $key = null): array
    {
        return model_cache_stats($key);
    }
}

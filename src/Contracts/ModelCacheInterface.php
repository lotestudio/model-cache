<?php

namespace Lotestudio\ModelCache\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface ModelCacheInterface
{
    /**
     * Get all cached models for a specific key
     *
     * @param string $key
     * @return Collection
     */
    public function all(string $key): Collection;

    /**
     * Find a specific model by ID
     *
     * @param string $key
     * @param  int|string  $id
     * @return Model|null
     */
    public function find(string $key, int|string $id): ?Model;

    /**
     * Find a specific model by field
     *
     * @param string $key
     * @param string $field
     * @param mixed $value
     * @return Model|null
     */
    public function findBy(string $key, string $field, mixed $value): ?Model;

    /**
     * Find all models matching a condition
     *
     * @param string $key
     * @param string $field
     * @param mixed $value
     * @return Collection
     */
    public function findAllBy(string $key, string $field, $value): Collection;

    /**
     * Check if a model exists
     *
     * @param string $key
     * @param int|string $id
     * @return bool
     */
    public function exists(string $key, $id): bool;

    /**
     * Pluck values from cached models
     *
     * @param string $key
     * @param string $column
     * @param string|null $keyColumn
     * @return array
     */
    public function pluck(string $key, string $column, ?string $keyColumn = null): array;

    /**
     * Get first model
     *
     * @param string $key
     * @return Model|null
     */
    public function first(string $key): ?Model;

    /**
     * Get last model
     *
     * @param string $key
     * @return Model|null
     */
    public function last(string $key): ?Model;

    /**
     * Count cached models
     *
     * @param string $key
     * @return int
     */
    public function count(string $key): int;

    /**
     * Check if collection is empty
     *
     * @param string $key
     * @return bool
     */
    public function isEmpty(string $key): bool;

    /**
     * Check if collection is not empty
     *
     * @param string $key
     * @return bool
     */
    public function isNotEmpty(string $key): bool;

    /**
     * Clear cache for a specific model
     *
     * @param string $key
     * @return bool
     */
    public function clear(string $key): bool;

    /**
     * Clear all cached models
     *
     * @return bool
     */
    public function clearAll(): bool;

    /**
     * Warm up cache for a specific model
     *
     * @param string $key
     * @return bool
     */
    public function warmup(string $key): bool;

    /**
     * Warm up all models
     *
     * @return bool
     */
    public function warmupAll(): bool;

    /**
     * Get cache statistics
     *
     * @param string|null $key
     * @return array
     */
    public function stats(?string $key = null): array;

    /**
     * Get the model configuration
     *
     * @param string $key
     * @return array|null
     */
    public function getConfig(string $key): ?array;

    /**
     * Register a model for caching
     *
     * @param string $key
     * @param array $config
     * @return self
     */
    public function register(string $key, array $config): self;
}

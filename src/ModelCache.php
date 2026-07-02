<?php

namespace Lotestudio\ModelCache;

use Lotestudio\ModelCache\Contracts\ModelCacheInterface;
use Illuminate\Cache\CacheManager;
use Illuminate\Config\Repository as Config;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ModelCache implements ModelCacheInterface
{
    protected CacheManager $cache;
    protected Config $config;
    protected array $requestCache = [];
    protected array $stats = [
        'hits' => 0,
        'misses' => 0,
        'sets' => 0,
        'clears' => 0,
        'errors' => 0,
    ];
    protected array $registeredModels = [];

    public function __construct(CacheManager $cache, Config $config)
    {
        $this->cache = $cache;
        $this->config = $config;
        $this->registeredModels = $this->config->get('model-cache.models', []);

        if ($this->config->get('model-cache.warmup_on_boot', false)) {
            $this->warmupAll();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function all(string $key): Collection
    {
        // Check request cache first
        if ($this->shouldUseRequestCache() && $this->hasRequestCache($key)) {
            $this->incrementStat('hits');
            return $this->getRequestCache($key);
        }

        $config = $this->getConfig($key);
        if (!$config) {
            $this->incrementStat('errors');
            throw new \InvalidArgumentException("Model with key '{$key}' is not registered.");
        }

        $cacheKey = $this->getCacheKey($key);

        try {
            $data = $this->cache->get($cacheKey);

            if ($data !== null) {
                $this->incrementStat('hits');
                $this->log("Cache HIT for key: {$key}");

                // Възстановяваме колекцията
                $collection = $this->restoreCollection($data);

                // Store in request cache
                if ($this->shouldUseRequestCache()) {
                    $this->setRequestCache($key, $collection);
                }

                return $collection;
            }

            $this->incrementStat('misses');
            $this->log("Cache MISS for key: {$key}");

            // Load from database
            $collection = $this->loadFromDatabase($config);

            // Store in cache
            $this->cache->put($cacheKey, $this->prepareForCache($collection), $this->getTtl($config));
            $this->incrementStat('sets');

            // Store in request cache
            if ($this->shouldUseRequestCache()) {
                $this->setRequestCache($key, $collection);
            }

            $this->log("Cache SET for key: {$key}");

            return $collection;
        } catch (\Exception $e) {
            $this->incrementStat('errors');
            $this->log("ERROR for key: {$key} - {$e->getMessage()}");

            if ($this->config->get('model-cache.fallback_to_db', true)) {
                return $this->loadFromDatabase($config);
            }

            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function find(string $key, $id): ?Model
    {
        $collection = $this->all($key);
        return $collection->find($id);
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function findBy(string $key, string $field, $value): ?Model
    {
        $collection = $this->all($key);
        return $collection->firstWhere($field, $value);
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function findAllBy(string $key, string $field, $value): Collection
    {
        $collection = $this->all($key);
        return $collection->where($field, $value);
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function exists(string $key, $id): bool
    {
        return $this->find($key, $id) !== null;
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function pluck(string $key, string $column, ?string $keyColumn = null): array
    {
        $collection = $this->all($key);
        return $collection->pluck($column, $keyColumn)->toArray();
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function first(string $key): ?Model
    {
        $collection = $this->all($key);
        return $collection->first();
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function last(string $key): ?Model
    {
        $collection = $this->all($key);
        return $collection->last();
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function count(string $key): int
    {
        $collection = $this->all($key);
        return $collection->count();
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function isEmpty(string $key): bool
    {
        $collection = $this->all($key);
        return $collection->isEmpty();
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function isNotEmpty(string $key): bool
    {
        return !$this->isEmpty($key);
    }

    /**
     * {@inheritdoc}
     */
    public function clear(string $key): bool
    {
        try {
            $config = $this->getConfig($key);
            if (!$config) {
                return false;
            }

            $cacheKey = $this->getCacheKey($key);
            $this->cache->forget($cacheKey);

            if ($this->shouldUseRequestCache()) {
                $this->forgetRequestCache($key);
            }

            $this->incrementStat('clears');
            $this->log("Cache CLEAR for key: {$key}");

            return true;
        } catch (\Exception $e) {
            $this->incrementStat('errors');
            $this->log("ERROR clearing key: {$key} - {$e->getMessage()}");
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function clearAll(): bool
    {
        $success = true;
        $keys = array_keys($this->registeredModels);

        foreach ($keys as $key) {
            if (!$this->clear($key)) {
                $success = false;
            }
        }

        $this->log("Cleared ALL caches");
        return $success;
    }

    /**
     * {@inheritdoc}
     */
    public function warmup(string $key): bool
    {
        try {
            $this->all($key);
            $this->log("Cache WARMUP for key: {$key}");
            return true;
        } catch (\Exception $e) {
            $this->incrementStat('errors');
            $this->log("ERROR warming up key: {$key} - {$e->getMessage()}");
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function warmupAll(): bool
    {
        $success = true;
        $keys = array_keys($this->registeredModels);

        foreach ($keys as $key) {
            if (!$this->warmup($key)) {
                $success = false;
            }
        }

        $this->log("Warmed up ALL caches");
        return $success;
    }

    /**
     * {@inheritdoc}
     */
    public function stats(?string $key = null): array
    {

        if (!$this->config->get('model-cache.enable_stats', false)) {
            return ['message' => 'Statistics are disabled. Enable in config/model-cache.php'];
        }

        $stats = $this->stats;

        if ($key) {
            $config = $this->getConfig($key);
            if ($config) {
                $cacheKey = $this->getCacheKey($key);
                $cachedData = $this->cache->get($cacheKey);
                $stats['model'] = $config['model'] ?? null;
                $stats['cached_items'] = $cachedData ? count($cachedData) : 0;
                $stats['cache_key'] = $cacheKey;
                $stats['ttl'] = $this->getTtl($config);
            }
        } else {
            $stats['models'] = [];
            $totalItems = 0;
            $keys = array_keys($this->registeredModels);

            foreach ($keys as $modelKey) {
                $config = $this->getConfig($modelKey);
                if ($config) {
                    $cacheKey = $this->getCacheKey($modelKey);
                    $cachedData = $this->cache->get($cacheKey);
                    $count = $cachedData ? count($cachedData) : 0;
                    $totalItems += $count;
                    $stats['models'][$modelKey] = [
                        'model' => $config['model'] ?? null,
                        'item_count' => $count,
                        'ttl' => $this->getTtl($config),
                    ];
                }
            }
            $stats['total_items'] = $totalItems;
            $stats['registered_models'] = count($keys);
        }

        $stats['cache_driver'] = $this->cache->getDefaultDriver();
        $stats['memory_usage'] = $this->getMemoryUsage();

        return $stats;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig(string $key): ?array
    {
        return $this->registeredModels[$key] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function register(string $key, array $config): ModelCacheInterface
    {
        $this->registeredModels[$key] = $config;
        $this->log("Registered new model with key: {$key}");
        return $this;
    }

    /**
     * Load data from a database
     *
     * @param array $config
     * @return Collection
     */
    protected function loadFromDatabase(array $config): Collection
    {
        $modelClass = $config['model'];

        if (!class_exists($modelClass)) {
            throw new \InvalidArgumentException("Model class '{$modelClass}' does not exist.");
        }

        $query = $modelClass::query();

        // Apply conditions
        if (isset($config['conditions']) && is_array($config['conditions'])) {
            foreach ($config['conditions'] as $condition) {
                if (is_array($condition) && count($condition) >= 3) {
                    $query->where($condition[0], $condition[1], $condition[2]);
                } elseif (is_array($condition) && count($condition) === 2) {
                    $query->where($condition[0], '=', $condition[1]);
                }
            }
        }

        // Apply ordering
        if (isset($config['order_by'])) {
            $direction = $config['order_direction'] ?? 'asc';
            $query->orderBy($config['order_by'], $direction);
        }

        // Select specific fields
        if (isset($config['fields']) && is_array($config['fields'])) {
            $query->select($config['fields']);
        }

        return $query->get();
    }

    /**
     * Prepare a collection for cache storage
     */
    protected function prepareForCache(Collection $collection): array
    {
        $idField = $this->config->get('model-cache.id_field', 'id');
        $result = [];

        foreach ($collection as $model) {
            $result[$model->$idField] = [
                'class' => get_class($model),
                'attributes' => $model->getAttributes(),
                'original' => $model->getOriginal(),
                'exists' => $model->exists,
            ];
        }

        return $result;
    }

    /**
     * Restore collection from the cache
     */
    protected function restoreCollection(array $data): Collection
    {
        $collection = new Collection();
        $idField = $this->config->get('model-cache.id_field', 'id');

        foreach ($data as $id => $item) {
            // Възстановяваме модела от атрибутите
            $model = $this->restoreModel($item);
            $collection->put($id, $model);
        }

        return $collection;
    }


    /**
     * Restore a single model from the cache
     *
     * @param array $item
     * @return Model
     */
    protected function restoreModel(array $item): Model
    {
        $attributes = $item['attributes'] ?? [];
        $original = $item['original'] ?? [];
        $exists = $item['exists'] ?? true;
        $relations = $item['relations'] ?? [];
        $modelClass = $item['class'] ?? null;

        if (!$modelClass || !class_exists($modelClass)) {
            throw new \RuntimeException("Cannot restore model: class not found");
        }

        /** @var Model $model */
        $model = new $modelClass();

        $model->setRawAttributes($attributes, true);

        if (!empty($original)) {
            $model->original = $original;
        } else {
            $model->syncOriginal();
        }

        $model->exists = $exists;

        if (!empty($relations)) {
            $model->setRelations($relations);
        }

        return $model;
    }

    /**
     * Get cache key
     *
     * @param string $key
     * @return string
     */
    protected function getCacheKey(string $key): string
    {
        $prefix = $this->config->get('model-cache.prefix', 'model_cache_');
        $useTags = $this->config->get('model-cache.use_tags', false);

        if ($useTags && $this->cacheSupportsTags()) {
            return $key;
        }

        return $prefix . $key;
    }

    protected function cacheSupportsTags(): bool
    {
        return in_array($this->cache->getDefaultDriver(), ["redis", "memcached", "dynamodb"]);
    }


    /**
     * Get TTL for model
     *
     * @param array $config
     * @return int|null
     */
    protected function getTtl(array $config): ?int
    {
        if (isset($config['ttl'])) {
            return $config['ttl'];
        }

        return $this->config->get('model-cache.ttl', 86400);
    }

    /**
     * Check if request cache should be used
     *
     * @return bool
     */
    protected function shouldUseRequestCache(): bool
    {
        return $this->config->get('model-cache.request_cache', true);
    }

    /**
     * Check if request cache exists
     *
     * @param string $key
     * @return bool
     */
    protected function hasRequestCache(string $key): bool
    {
        return isset($this->requestCache[$key]);
    }

    /**
     * Get request cache
     *
     * @param string $key
     * @return mixed
     */
    protected function getRequestCache(string $key)
    {
        return $this->requestCache[$key] ?? null;
    }

    /**
     * Set request cache
     *
     * @param string $key
     * @param mixed $data
     * @return void
     */
    protected function setRequestCache(string $key, $data): void
    {
        $this->requestCache[$key] = $data;
    }

    /**
     * Forget request cache
     *
     * @param string $key
     * @return void
     */
    protected function forgetRequestCache(string $key): void
    {
        unset($this->requestCache[$key]);
    }

    /**
     * Increment statistic
     *
     * @param string $stat
     * @param int $value
     * @return void
     */
    protected function incrementStat(string $stat, int $value = 1): void
    {

        if (!$this->config->get('model-cache.enable_stats', false)) {
            return;
        }

        if (isset($this->stats[$stat])) {
            $this->stats[$stat] += $value;
        }
    }

    /**
     * Log message
     *
     * @param string $message
     * @param string $level
     * @return void
     */
    protected function log(string $message, string $level = 'debug'): void
    {
        if ($this->config->get('model-cache.logging', false)) {
            Log::channel('model_cache')->$level($message);
        }
    }

    /**
     * Get memory usage
     *
     * @return string
     */
    protected function getMemoryUsage(): string
    {
        $memory = memory_get_usage();
        return $this->formatBytes($memory);
    }

    /**
     * Format bytes to human readable
     *
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $index = 0;

        while ($bytes >= 1024 && $index < count($units) - 1) {
            $bytes /= 1024;
            $index++;
        }

        return round($bytes, $precision) . ' ' . $units[$index];
    }

    /**
     * Magic method for dynamic calls
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call(string $method, array $parameters)
    {
        if (str_starts_with($method, 'find')) {
            $key = strtolower(substr($method, 4));
            return $this->find($key, $parameters[0] ?? null);
        }

        if (str_starts_with($method, 'all')) {
            $key = strtolower(substr($method, 3));
            return $this->all($key);
        }

        if (str_starts_with($method, 'clear')) {
            $key = strtolower(substr($method, 5));
            return $this->clear($key);
        }

        if (str_starts_with($method, 'pluck')) {
            $key = strtolower(substr($method, 6));
            return $this->pluck($key, $parameters[0] ?? null, $parameters[1] ?? null);
        }

        if (str_starts_with($method, 'first')) {
            $key = strtolower(substr($method, 5));
            return $this->first($key);
        }

        if (str_starts_with($method, 'last')) {
            $key = strtolower(substr($method, 4));
            return $this->last($key);
        }

        if (str_starts_with($method, 'count')) {
            $key = strtolower(substr($method, 5));
            return $this->count($key);
        }

        throw new \BadMethodCallException("Method {$method} does not exist.");
    }
}

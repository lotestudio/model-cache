# Laravel Model Cache

A lightweight, convention-driven caching layer for Laravel Eloquent models. Automatically caches model query results with observer-based invalidation, request-level caching, and Artisan commands for management.

## Features

- **Automatic Caching** — Cache model query results with configurable TTL and cache driver
- **Observer-Based Invalidation** — Automatically clears cache when models are saved, updated, deleted, or restored
- **Request-Level Caching** — Avoids redundant cache lookups within the same request
- **Artisan Commands** — Warm up, clear, and inspect cache statistics via CLI
- **Helper Functions** — Convenient global helpers for quick access
- **Facade Support** — `ModelCache` facade for dependency injection
- **Dynamic Magic Methods** — Call `findStores($id)`, `allStores()`, etc. directly
- **Fallback to Database** — Gracefully falls back to database queries when cache is empty

## Requirements

- PHP 8.4+
- Laravel 12+

## Installation

The package is located at `app/Lote/ModelCache` and is auto-discovered via Laravel's service provider registration.

### Service Provider

Register the service provider in `bootstrap/providers.php` (Laravel 12) or `config/app.php`:

```php
App\Lote\ModelCache\ModelCacheServiceProvider::class,
```

### Facade (Optional)

Add to the `aliases` array in `config/app.php`:

```php
'ModelCache' => App\Lote\ModelCache\Facades\ModelCache::class,
```

## Configuration

[//]: # (Publish the configuration file:)

[//]: # ()
[//]: # (```bash)

[//]: # (php artisan vendor:publish --tag=model-cache-config)

[//]: # (```)

Or configure directly in `config/model-cache.php`:

```php
return [
    'driver' => env('MODEL_CACHE_DRIVER', env('CACHE_DRIVER', 'file')),
    'ttl' => env('MODEL_CACHE_TTL', 86400), // 24 hours
    'prefix' => env('MODEL_CACHE_PREFIX', 'model_cache_'),

    'models' => [
        'stores' => [
            'model' => App\Models\Store::class,
            'key' => 'stores',
            'ttl' => 86400,
            'order_by' => 'name',
            'order_direction' => 'asc',
            'fields' => ['id', 'name', 'address', 'phone', 'is_active'],
            'conditions' => [
                ['is_active', '=', true],
            ],
        ],
    ],

    'auto_clear' => true,
    'warmup_on_boot' => env('MODEL_CACHE_WARMUP', false),
    'logging' => env('MODEL_CACHE_LOGGING', false),
    'use_tags' => env('MODEL_CACHE_TAGS', false),
    'request_cache' => true,
    'fallback_to_db' => true,
    'id_field' => 'id',
];
```

### Model Configuration Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `model` | `string` | required | Full class name of the Eloquent model |
| `key` | `string` | required | Unique cache key identifier |
| `ttl` | `int` | `86400` | Cache TTL in seconds |
| `order_by` | `string` | — | Column to order results by |
| `order_direction` | `string` | `'asc'` | Sort direction (`asc` or `desc`) |
| `fields` | `array` | `null` (all) | Specific columns to select |
| `conditions` | `array` | `[]` | Where clauses `[['column', 'operator', value], ...]` |

## Usage

### Basic Usage

```php
use App\Lote\ModelCache\Facades\ModelCache;

// Get all cached stores
$stores = ModelCache::all('stores');

// Find a specific store by ID
$store = ModelCache::find('stores', 1);

// Find by custom field
$store = ModelCache::findBy('stores', 'email', 'admin@example.com');

// Find all matching a condition
$activeStores = ModelCache::findAllBy('stores', 'is_active', true);

// Check existence
if (ModelCache::exists('stores', 1)) {
    // ...
}

// Clear cache for a specific model
ModelCache::clear('stores');

// Clear all caches
ModelCache::clearAll();

// Warm up cache
ModelCache::warmup('stores');
ModelCache::warmupAll();
```

### Dynamic Magic Methods

The `__call` magic method provides convenient shortcuts:

```php
// Equivalent to ModelCache::find('stores', $id)
ModelCache::findStores($id);

// Equivalent to ModelCache::all('stores')
ModelCache::allStores();

// Equivalent to ModelCache::clear('stores')
ModelCache::clearStores();
```

### Helper Functions

```php
// Get the cache instance
$cache = model_cache();

// Find a cached model by ID
$store = model_cached('stores', 1);

// Get all cached models
$stores = model_cached_all('stores');

// Find by custom field
$store = model_cached_find_by('stores', 'slug', 'main-store');

// Find all matching
$items = model_cached_find_all_by('stores', 'is_active', true);

// Check existence
$exists = model_cached_exists('stores', 1);

// Clear cache
model_cache_clear('stores');
model_cache_clear_all();

// Warm up cache
model_cache_warmup('stores');
model_cache_warmup_all();

// Get statistics
$stats = model_cache_stats();
$storeStats = model_cache_stats('stores');

// Register a model dynamically
model_cache_register('products', [
    'model' => App\Models\Product::class,
    'key' => 'products',
    'order_by' => 'name',
]);
```

### Using the Interface (Dependency Injection)

```php
use App\Lote\ModelCache\Contracts\ModelCacheInterface;

class StoreController
{
    public function __construct(
        protected ModelCacheInterface $cache
    ) {}

    public function index()
    {
        $stores = $this->cache->all('stores');
        return view('stores.index', compact('stores'));
    }
}
```

## Artisan Commands

### Warm Up Cache

```bash
# Warm up all registered models
php artisan model-cache:warmup

# Warm up a specific model
php artisan model-cache:warmup --key=stores

# Force re-cache (clear then warm up)
php artisan model-cache:warmup --key=stores --force

# Show detailed output
php artisan model-cache:warmup --verbose
```

### Clear Cache

```bash
# Clear all model caches
php artisan model-cache:clear --all

# Clear a specific model cache
php artisan model-cache:clear --key=stores
```

### Cache Statistics

```bash
# Show overall statistics
php artisan model-cache:stats

# Show per-model statistics
php artisan model-cache:stats --key=stores
```

## Observer-Based Auto-Clear

When `auto_clear` is enabled in the config, the package automatically registers model observers that clear the relevant cache whenever a model is saved, updated, deleted, or restored.

The service provider checks for a custom observer at `App\Observers\{ModelName}Observer` before falling back to the generic `ModelCacheObserver`.

## Architecture

```
app/Lote/ModelCache/
├── Commands/
│   ├── CacheClearCommand.php      # php artisan model-cache:clear
│   ├── CacheStatsCommand.php      # php artisan model-cache:stats
│   └── CacheWarmupCommand.php     # php artisan model-cache:warmup
├── Contracts/
│   └── ModelCacheInterface.php    # Interface contract
├── Facades/
│   └── ModelCache.php             # Facade accessor
├── config/
│   └── model-cache.php            # Configuration
├── ModelCache.php                 # Core implementation
├── ModelCacheObserver.php         # Generic model observer
├── ModelCacheServiceProvider.php  # Service provider
└── helpers.php                    # Global helper functions
```

## Cache Invalidation

Cache is automatically invalidated via model observers when:

- `saved` — Model is created or updated
- `updated` — Model is updated
- `deleted` — Model is deleted
- `restored` — Model is restored from soft delete

Manual invalidation is also available via `clear()` or the Artisan command.

## License

This is internal application code. All rights reserved.

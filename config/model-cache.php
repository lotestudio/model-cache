<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cache Driver
    |--------------------------------------------------------------------------
    |
    | The cache driver to use for storing cached models.
    | Supported: "file", "redis", "memcached", "array"
    |
    */
    'driver' => env('MODEL_CACHE_DRIVER', env('CACHE_DRIVER', 'file')),

    /*
    |--------------------------------------------------------------------------
    | Cache TTL (Time To Live)
    |--------------------------------------------------------------------------
    |
    | The default number of seconds that cached items should be stored.
    | Set to null for forever caching.
    |
    */
    'ttl' => env('MODEL_CACHE_TTL', 86400), // 24 hours

    /*
    |--------------------------------------------------------------------------
    | Cache Prefix
    |--------------------------------------------------------------------------
    |
    | A prefix for all cache keys to avoid collisions with other caches.
    |
    */
    'prefix' => env('MODEL_CACHE_PREFIX', 'model_cache_'),

    /*
    |--------------------------------------------------------------------------
    | Models to Cache
    |--------------------------------------------------------------------------
    |
    | Define the models you want to cache. Each model must have:
    | - model: The full class name of the model
    | - key: The cache key to use (must be unique)
    | - fields: (optional) Specific fields to cache, null for all
    | - ttl: (optional) Custom TTL for this model
    | - order_by: (optional) Field to order by when loading
    | - order_direction: (optional) 'asc' or 'desc'
    | - conditions: (optional) Array of where conditions
    |
    */
    'models' => [
//        'stores' => [
//            'model' => App\Models\Store::class,
//            'key' => 'stores',
//            'ttl' => 86400, // 24 hours
//            'order_by' => 'name',
//            'order_direction' => 'asc',
//            'fields' => ['id', 'name', 'address', 'phone', 'is_active'],
//            'conditions' => [
//                ['is_active', '=', true]
//            ],
//        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto Clear Cache
    |--------------------------------------------------------------------------
    |
    | Automatically clear cache when models are saved, updated or deleted.
    | Uses model events/observers.
    |
    */
    'auto_clear' => true,

    /*
    |--------------------------------------------------------------------------
    | Cache Warmup
    |--------------------------------------------------------------------------
    |
    | Automatically warmup cache on application boot.
    | This will load all models into cache.
    |
    */
    'warmup_on_boot' => env('MODEL_CACHE_WARMUP', false),

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Enable logging of cache operations (hits, misses, clears, etc.)
    |
    */
    'logging' => env('MODEL_CACHE_LOGGING', false),


    /*
    |--------------------------------------------------------------------------
    | Enable Statistics
    |--------------------------------------------------------------------------
    |
    | Enable detailed cache statistics collection.
    | Disable for production to improve performance.
    |
    */
    'enable_stats' => env('MODEL_CACHE_STATS', false),

    /*
    |--------------------------------------------------------------------------
    | Tags (for Redis/Memcached)
    |--------------------------------------------------------------------------
    |
    | Use cache tags for better cache management.
    | Only works with drivers that support tags (redis, memcached)
    |
    */
    'use_tags' => env('MODEL_CACHE_TAGS', false),

    /*
    |--------------------------------------------------------------------------
    | Request Cache
    |--------------------------------------------------------------------------
    |
    | Cache the results within the same request to avoid multiple cache lookups.
    |
    */
    'request_cache' => true,

    /*
    |--------------------------------------------------------------------------
    | Fallback to Database
    |--------------------------------------------------------------------------
    |
    | If cache is empty, fallback to database query.
    | If false, will return null or throw exception.
    |
    */
    'fallback_to_db' => true,

    /*
    |--------------------------------------------------------------------------
    | The ID field name
    |--------------------------------------------------------------------------
    |
    | The primary key field name for models.
    |
    */
    'id_field' => 'id',
];

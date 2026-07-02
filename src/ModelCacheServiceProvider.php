<?php

namespace Lotestudio\ModelCache;

use Lotestudio\ModelCache\Commands\CacheClearCommand;
use Lotestudio\ModelCache\Commands\CacheStatsCommand;
use Lotestudio\ModelCache\Commands\CacheWarmupCommand;
use Lotestudio\ModelCache\Contracts\ModelCacheInterface;
use Illuminate\Support\ServiceProvider;

class ModelCacheServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../config/model-cache.php',
            'model-cache'
        );

        // Register the main service
        $this->app->singleton(ModelCacheInterface::class, function ($app) {
            return new ModelCache(
                $app['cache'],
                $app['config']
            );
        });

        // Register alias for convenience
        $this->app->alias(ModelCacheInterface::class, 'model.cache');

        // Register for easier dependency injection
        $this->app->singleton(ModelCache::class, function ($app) {
            return $app->make(ModelCacheInterface::class);
        });


        // Register commands
        $this->registerCommands();
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Publish configuration
//        $this->publishes([
//            __DIR__ . '/../config/model-cache.php' => config_path('model-cache.php'),
//        ], 'model-cache-config');

        // Publish observers (optional)
//        $this->publishes([
//            __DIR__ . '/Observers' => app_path('Observers'),
//        ], 'model-cache-observers');

        // Register model observers if auto_clear is enabled
        if (config('model-cache.auto_clear', true)) {
            $this->registerObservers();
        }

        // Register helper functions
        $this->registerHelpers();
    }

    /**
     * Регистрира observers за всички модели
     * ВИНАГИ използва ModelCacheObserver
     */
    protected function registerObservers(): void
    {
        $models = config('model-cache.models', []);

        foreach ($models as $key => $config) {
            $modelClass = $config['model'] ?? null;

            if ($modelClass && class_exists($modelClass)) {
                $modelClass::observe(ModelCacheObserver::class);
            }
        }
    }

    /**
     * Register helper functions
     *
     * @return void
     */
    protected function registerHelpers(): void
    {
        // Check if helpers are already registered
        if (!function_exists('model_cache')) {
            require_once __DIR__ . '/helpers.php';
        }
    }

    /**
     * Register console commands
     *
     * @return void
     */
    protected function registerCommands(): void
    {
        $this->commands([
            CacheWarmupCommand::class,
            CacheClearCommand::class,
            CacheStatsCommand::class,
        ]);
    }
}

<?php

namespace Lotestudio\ModelCache;

use Lotestudio\ModelCache\Contracts\ModelCacheInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class ModelCacheObserver
{
    /**
     * @var ModelCacheInterface
     */
    protected ModelCacheInterface $cache;

    /**
     * ModelCacheObserver constructor.
     * @throws BindingResolutionException
     */
    public function __construct()
    {
        $this->cache = App::make(ModelCacheInterface::class);
    }

    /**
     * Handle model saved event
     *
     * @param Model $model
     * @return void
     */
    public function saved(Model $model): void
    {
        $this->clearCacheForModel($model);
    }

    /**
     * Handle model updated event
     *
     * @param Model $model
     * @return void
     */
    public function updated(Model $model): void
    {
        $this->clearCacheForModel($model);
    }

    /**
     * Handle model deleted event
     *
     * @param Model $model
     * @return void
     */
    public function deleted(Model $model): void
    {
        $this->clearCacheForModel($model);
    }

    /**
     * Handle model restored event
     *
     * @param Model $model
     * @return void
     */
    public function restored(Model $model): void
    {
        $this->clearCacheForModel($model);
    }

    /**
     * Clear cache for the given model
     *
     * @param Model $model
     * @return void
     */
    protected function clearCacheForModel(Model $model): void
    {
        $models = config('model-cache.models', []);
        $modelClass = get_class($model);

        foreach ($models as $key => $config) {
            if (($config['model'] ?? null) === $modelClass) {
                $this->cache->clear($key);
                break;
            }
        }
    }
}

<?php

namespace Lotestudio\ModelCache\Facades;

use Lotestudio\ModelCache\Contracts\ModelCacheInterface;
use Illuminate\Support\Facades\Facade;

/**
 * @method static array all(string $key)
 * @method static object|null find(string $key, $id)
 * @method static object|null findBy(string $key, string $field, $value)
 * @method static array findAllBy(string $key, string $field, $value)
 * @method static bool exists(string $key, $id)
 * @method static bool clear(string $key)
 * @method static bool clearAll()
 * @method static bool warmup(string $key)
 * @method static bool warmupAll()
 * @method static array stats(?string $key = null)
 * @method static array|null getConfig(string $key)
 * @method static ModelCacheInterface register(string $key, array $config)
 * @method static mixed findStores($id)
 * @method static array allStores()
 * @method static mixed findPriceLists($id)
 * @method static array allPriceLists()
 * @method static mixed findCategories($id)
 * @method static array allCategories()
 *
 * @see \Lotestudio\ModelCache\ModelCache
 */
class ModelCache extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'model.cache';
    }
}

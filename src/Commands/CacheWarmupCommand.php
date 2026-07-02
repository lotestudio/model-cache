<?php

namespace Lotestudio\ModelCache\Commands;


use Lotestudio\ModelCache\Contracts\ModelCacheInterface;
use Illuminate\Console\Command;

class CacheWarmupCommand extends Command
{
    protected $signature = 'model-cache:warmup
                            {--key= : Specific model key to warmup}
                            {--force : Force warmup even if cache exists}
                            {--show-details : Show detailed output}';  // Променено от --verbose

    protected $description = 'Warm up model cache';

    protected ModelCacheInterface $cache;

    public function __construct(ModelCacheInterface $cache)
    {
        parent::__construct();
        $this->cache = $cache;
    }

    public function handle(): int
    {
        $key = $this->option('key');
        $force = $this->option('force');
        $showDetails = $this->option('show-details');  // Променено

        $startTime = microtime(true);

        $this->info('🔄 Warming up model cache...');

        if ($key) {
            if ($force) {
                $this->cache->clear($key);
                $this->line("   ↳ Cleared existing cache for: {$key}");
            }

            if ($this->cache->warmup($key)) {
                $this->info("✅ Successfully warmed up: {$key}");
            } else {
                $this->error("❌ Failed to warm up: {$key}");
                return 1;
            }
        } else {
            if ($force) {
                $this->cache->clearAll();
                $this->line("   ↳ Cleared all existing caches");
            }

            $this->cache->warmupAll();
            $this->info("✅ Successfully warmed up all models");
        }

        $elapsed = round((microtime(true) - $startTime) * 1000, 2);

        if ($showDetails) {  // Променено
            $stats = $this->cache->stats($key);
            $this->newLine();
            $this->line('📊 <fg=cyan>Cache Statistics:</>');
            $this->newLine();
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Models Cached', $stats['registered_models'] ?? 'N/A'],
                    ['Total Items', $stats['total_items'] ?? 'N/A'],
                    ['Cache Driver', $stats['cache_driver'] ?? 'N/A'],
                    ['Memory Usage', $stats['memory_usage'] ?? 'N/A'],
                    ['Execution Time', "{$elapsed}ms"],
                ]
            );
        }

        $this->newLine();
        $this->info("✨ Done in {$elapsed}ms");

        return 0;
    }
}

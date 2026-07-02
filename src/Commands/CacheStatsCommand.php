<?php

namespace Lotestudio\ModelCache\Commands;

use Illuminate\Console\Command;
use Lotestudio\ModelCache\Contracts\ModelCacheInterface;

class CacheStatsCommand extends Command
{
    protected $signature = 'model-cache:stats
                            {--key= : Specific model key to show stats for}';
    protected $description = 'Show model cache statistics';

    public function handle(ModelCacheInterface $cache): int
    {
        $key = $this->option('key');
        $stats = $cache->stats($key);

        $this->info('📊 Model Cache Statistics');
        $this->newLine();

        if ($key) {
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Model', $stats['model'] ?? 'N/A'],
                    ['Cache Key', $stats['cache_key'] ?? 'N/A'],
                    ['Cached Items', $stats['cached_items'] ?? 'N/A'],
                    ['TTL (seconds)', $stats['ttl'] ?? 'N/A'],
                    ['Cache Hits', $stats['hits'] ?? 0],
                    ['Cache Misses', $stats['misses'] ?? 0],
                    ['Cache Sets', $stats['sets'] ?? 0],
                    ['Cache Clears', $stats['clears'] ?? 0],
                    ['Errors', $stats['errors'] ?? 0],
                    ['Cache Driver', $stats['cache_driver'] ?? 'N/A'],
                    ['Memory Usage', $stats['memory_usage'] ?? 'N/A'],
                ]
            );
        } else {
            $this->info("📈 Overall Statistics:");
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Registered Models', $stats['registered_models'] ?? 0],
                    ['Total Items', $stats['total_items'] ?? 0],
                    ['Cache Hits', $stats['hits'] ?? 0],
                    ['Cache Misses', $stats['misses'] ?? 0],
                    ['Cache Sets', $stats['sets'] ?? 0],
                    ['Cache Clears', $stats['clears'] ?? 0],
                    ['Errors', $stats['errors'] ?? 0],
                    ['Cache Driver', $stats['cache_driver'] ?? 'N/A'],
                    ['Memory Usage', $stats['memory_usage'] ?? 'N/A'],
                ]
            );

            if (!empty($stats['models'])) {
                $this->newLine();
                $this->info("📦 Per-Model Statistics:");
                $rows = [];
                foreach ($stats['models'] as $modelKey => $data) {
                    $rows[] = [
                        $modelKey,
                        $data['model'] ?? 'N/A',
                        $data['item_count'] ?? 0,
                        $data['ttl'] ?? 'N/A',
                    ];
                }
                $this->table(
                    ['Model Key', 'Model Class', 'Items', 'TTL (seconds)'],
                    $rows
                );
            }
        }

        return 0;
    }
}

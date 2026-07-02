<?php

namespace Lotestudio\ModelCache\Commands;


use Illuminate\Console\Command;
use Lotestudio\ModelCache\Contracts\ModelCacheInterface;

class CacheClearCommand extends Command
{
    protected $signature = 'model-cache:clear
                            {--key= : Specific model key to clear}
                            {--all : Clear all model caches}';
    protected $description = 'Clear model cache';

    public function handle(ModelCacheInterface $cache): int
    {
        $key = $this->option('key');
        $all = $this->option('all');

        if ($all) {
            $cache->clearAll();
            $this->info('✅ All model caches cleared');
            return 0;
        }

        if ($key) {
            if ($cache->clear($key)) {
                $this->info("✅ Cache cleared for: {$key}");
                return 0;
            }
            $this->error("❌ Failed to clear cache for: {$key}");
            return 1;
        }

        $this->error('Please specify --key or --all');
        return 1;
    }
}

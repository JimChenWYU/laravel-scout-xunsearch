<?php declare(strict_types=1);

namespace JimChen\LaravelScout\XunSearch\Console;

use Illuminate\Console\Command;
use JimChen\LaravelScout\XunSearch\SchemaCache;

class IniFlushCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'scout:xs-ini-flush';

    /**
     * @var string
     */
    protected $description = "Flush ini configuration cache";

    public function handle(SchemaCache $cache)
    {
        $cache->delete('xunsearch.cache.ini');

        $this->info('All ini configuration cache have been flushed.');
    }
}

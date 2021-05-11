<?php declare(strict_types=1);

namespace JimChen\LaravelScout\XunSearch\Console;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Database\Eloquent\Model;
use JimChen\LaravelScout\XunSearch\SchemaCache;
use Laravel\Scout\Searchable;

class IniFlushCommand extends Command
{
    use ConfirmableTrait;

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

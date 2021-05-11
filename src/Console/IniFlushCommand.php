<?php declare(strict_types=1);

namespace JimChen\LaravelScout\XunSearch\Console;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use JimChen\LaravelScout\XunSearch\SchemaCache;
use Laravel\Scout\Searchable;

class IniFlushCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'scout:xs-ini-flush
            {model : Class name of model}';

    /**
     * @var string
     */
    protected $description = "Flush ini configuration cache";

    public function handle(SchemaCache $cache)
    {
        $class = $this->argument('model');

        if ($class === 'all' && $this->confirmToProceed()) {
            $cache->clear();

            $this->info('All ini configuration cache have been flushed.');
            return ;
        }

        /** @var Model|Searchable $model */
        $model = new $class();

        $cache->delete($model->searchableAs());

        $this->info('All [' . $class . '] ini configuration cache have been flushed.');
    }
}

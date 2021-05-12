<?php declare(strict_types=1);

namespace JimChen\LaravelScout\XunSearch\Console;

/**
 * Trait Interacts
 *
 * @mixin \Illuminate\Console\Command
 */
trait Interacts
{
    /**
     * @return array
     */
    protected function getFreshConfiguration()
    {
        return $this->laravel['config']->get('scout.xunsearch.storage.schema');
    }
}

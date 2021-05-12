<?php

namespace JimChen\LaravelScout\XunSearch\Console;

use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;

/**
 * Trait HasConfiguration
 *
 * @mixin \Illuminate\Console\Command
 */
trait HasConfiguration
{
    /**
     * Boot a fresh copy of the application configuration.
     *
     * @return array
     */
    protected function getFreshConfiguration()
    {
        return $this->laravel['config']->get('scout.xunsearch.storage.schema');
    }
}

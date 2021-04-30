<?php declare(strict_types=1);

namespace Tests;

use JimChen\LaravelScout\XunSearch\XunSearchScoutServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            XunSearchScoutServiceProvider::class
        ];
    }
}

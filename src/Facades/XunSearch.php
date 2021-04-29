<?php declare(strict_types=1);

namespace JimChen\LaravelScout\XunSearch\Facades;

use Illuminate\Support\Facades\Facade;
use JimChen\LaravelScout\XunSearch\XunSearchClient;

class XunSearch extends Facade
{
    protected static function getFacadeAccessor()
    {
        return static::$app->get(XunSearchClient::class);
    }
}

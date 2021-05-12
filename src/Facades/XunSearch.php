<?php declare(strict_types=1);

namespace JimChen\LaravelScout\XunSearch\Facades;

use Illuminate\Support\Facades\Facade;
use JimChen\LaravelScout\XunSearch\XunSearchClient;

/**
 * Class XunSearch
 *
 * @method static array getHotQuery(string $indexName, int $limit = 10, string $type = 'total')
 * @method static array getRelatedQuery(string $indexName, ?string $query = null, int $limit = 10)
 */
class XunSearch extends Facade
{
    protected static function getFacadeAccessor()
    {
        return XunSearchClient::class;
    }
}

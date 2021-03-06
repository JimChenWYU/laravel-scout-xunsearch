<?php declare(strict_types=1);

namespace JimChen\LaravelScout\XunSearch;

use JimChen\LaravelScout\XunSearch\Facades\XunSearch;

/**
 * Trait XunSearchable
 *
 * @mixin \Laravel\Scout\Searchable
 */
trait XunSearchable
{
    /**
     * @param int    $limit
     * @param string $type
     * @return array
     */
    public function getHotQuery(int $limit = 10, string $type = 'total')
    {
        return XunSearch::getHotQuery($this->searchableAs(), $limit, $type);
    }

    /**
     * @param string|null $query
     * @param int         $limit
     * @return array
     */
    public function getRelatedQuery(?string $query = null, int $limit = 10)
    {
        return XunSearch::getRelatedQuery($this->searchableAs(), $query, $limit);
    }
}

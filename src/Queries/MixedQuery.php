<?php declare(strict_types=1);

namespace JimChen\LaravelScout\XunSearch\Queries;

class MixedQuery implements Query
{
    /** @var string */
    protected $query;

    /**
     * @param string $query
     */
    public function __construct(string $query)
    {
        $this->query = $query;
    }

    public function __toString()
    {
        return $this->query;
    }
}

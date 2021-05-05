<?php

namespace JimChen\LaravelScout\XunSearch\Queries;

class UnContainQuery extends MixedQuery
{
    /** @var string[] */
    protected $unContains;

    public function __construct(string $query, array $unContains)
    {
        $this->unContains = $unContains;
        parent::__construct($query);
    }

    public function __toString()
    {
        return vsprintf('%s NOT (%s)', [
            $this->query,
            implode(' ', $this->unContains),
        ]);
    }
}

<?php declare(strict_types=1);

namespace JimChen\LaravelScout\XunSearch\Queries;

class ExactMatchQuery extends MixedQuery
{
    public function __toString()
    {
        return sprintf('"%s"', $this->query);
    }
}

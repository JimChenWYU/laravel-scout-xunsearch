<?php declare(strict_types=1);

namespace JimChen\LaravelScout\XunSearch\Queries;

class NearQuery extends DistanceQuery
{
    public function kinds(): string
    {
        return 'NEAR';
    }
}

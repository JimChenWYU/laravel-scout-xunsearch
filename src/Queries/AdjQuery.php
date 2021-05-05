<?php declare(strict_types=1);

namespace JimChen\LaravelScout\XunSearch\Queries;

class AdjQuery extends DistanceQuery
{
    public function kinds(): string
    {
        return 'ADJ';
    }
}

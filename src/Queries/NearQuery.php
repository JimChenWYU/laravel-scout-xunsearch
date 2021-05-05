<?php

namespace JimChen\LaravelScout\XunSearch\Queries;

class NearQuery extends DistanceQuery
{
    public function kinds(): string
    {
        return 'NEAR';
    }
}

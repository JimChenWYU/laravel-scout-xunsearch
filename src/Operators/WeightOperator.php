<?php declare(strict_types=1);

namespace JimChen\LaravelScout\XunSearch\Operators;

class WeightOperator extends Operator
{
    private $weight;

    public function __construct(string $weight = '')
    {
        $this->weight = $weight;
    }

    public function __toString()
    {
        return $this->weight;
    }
}

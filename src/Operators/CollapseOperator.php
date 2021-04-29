<?php declare(strict_types=1);

namespace JimChen\LaravelScout\XunSearch\Operators;

class CollapseOperator extends Operator
{
    private $number;

    public function __construct(int $number = 1)
    {
        $this->number = $number;
    }

    public function __toString()
    {
        return (string) $this->number;
    }
}

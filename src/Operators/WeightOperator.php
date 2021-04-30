<?php declare(strict_types=1);

namespace JimChen\LaravelScout\XunSearch\Operators;

class WeightOperator extends Operator
{
    private $weight;

    private $term;

    public function __construct(string $term, string $weight = '')
    {
        $this->term = $term;
        $this->weight = $weight;
    }

    public function __toString()
    {
        return $this->weight;
    }

    /**
     * @return string
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @return mixed
     */
    public function getTerm()
    {
        return $this->term;
    }
}

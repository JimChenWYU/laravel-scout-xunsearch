<?php declare(strict_types=1);

namespace JimChen\LaravelScout\XunSearch\Operators;

class FuzzyOperator extends Operator
{
    private $fuzzy;

    public function __construct(bool $fuzzy)
    {
        $this->fuzzy = $fuzzy;
    }

    public function __toString()
    {
        return (string) $this->fuzzy;
    }

    /**
     * @return bool
     */
    public function isFuzzy()
    {
        return $this->fuzzy;
    }
}

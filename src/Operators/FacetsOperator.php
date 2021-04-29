<?php declare(strict_types=1);

namespace JimChen\LaravelScout\XunSearch\Operators;

class FacetsOperator extends Operator
{
    private $fields;

    private $exact;

    public function __construct(array $fields, bool $exact = false)
    {
        $this->fields = $fields;
        $this->exact = $exact;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function getExact()
    {
        return $this->exact;
    }

    public function __toString()
    {
        return '';
    }
}

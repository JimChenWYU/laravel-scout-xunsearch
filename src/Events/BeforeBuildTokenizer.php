<?php declare(strict_types=1);

namespace JimChen\LaravelScout\XunSearch\Events;

class BeforeBuildTokenizer
{
    public $builder;

    public function __construct($builder)
    {
        $this->builder = $builder;
    }
}

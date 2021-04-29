<?php

namespace JimChen\LaravelScout\XunSearch\Events;

class AfterBuildTokenizer
{
    public $builder;

    public $tokenizer;

    /**
     * AfterBuildTokenizer constructor.
     * @param $builder
     * @param $tokenizer
     */
    public function __construct($builder, $tokenizer)
    {
        $this->builder = $builder;
        $this->tokenizer = $tokenizer;
    }
}

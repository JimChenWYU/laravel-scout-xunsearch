<?php declare(strict_types=1);

namespace JimChen\LaravelScout\XunSearch\Builders;

use JimChen\LaravelScout\XunSearch\Tokenizers\Contracts\AbstractTokenizer;
use JimChen\LaravelScout\XunSearch\Tokenizers\Contracts\TokenizerContract;
use ReflectionClass;
use RuntimeException;
use XS;

class TokenizerBuilder
{
    public $xs;

    public $mode;

    private $class;

    /**
     * TokenizerBuilder constructor.
     * @param $class
     */
    public function __construct($class)
    {
        if (is_subclass_of($class, TokenizerContract::class)) {
            $this->class = $class;
        }

        throw new RuntimeException("Class '{$class}' not implement '" . TokenizerContract::class . "'");
    }

    /**
     * @param XS $xs
     * @return $this
     */
    public function withXs(XS $xs)
    {
        $this->xs = $xs;

        return $this;
    }

    /**
     * @param mixed $mode
     * @return $this
     */
    public function withMode($mode)
    {
        $this->mode = $mode;

        return $this;
    }

    /**
     * @return TokenizerContract|AbstractTokenizer|object
     * @throws \ReflectionException
     */
    public function build()
    {
        return (new ReflectionClass($this->class))->newInstance($this);
    }
}

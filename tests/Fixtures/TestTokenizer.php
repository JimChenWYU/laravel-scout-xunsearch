<?php declare(strict_types=1);

namespace Tests\Fixtures;

use JimChen\LaravelScout\XunSearch\Tokenizers\Contracts\AbstractTokenizer;
use SplFixedArray;
use XSDocument;

class TestTokenizer extends AbstractTokenizer
{
    private $foo = 'foo';

    public function getTops($text, $limit = 10, $xattr = ''): SplFixedArray
    {
        return SplFixedArray::fromArray([]);
    }

    public function getTokens($value, XSDocument $doc = null): SplFixedArray
    {
        return SplFixedArray::fromArray([]);
    }

    /**
     * @return string
     */
    public function getFoo(): string
    {
        return $this->foo;
    }

    /**
     * @param string $foo
     */
    public function setFoo(string $foo): void
    {
        $this->foo = $foo;
    }
}

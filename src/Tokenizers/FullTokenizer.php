<?php declare(strict_types=1);

namespace JimChen\LaravelScout\XunSearch\Tokenizers;

use JimChen\LaravelScout\XunSearch\Tokenizers\Contracts\AbstractTokenizer;
use SplFixedArray;
use XSDocument;

class FullTokenizer extends AbstractTokenizer
{
    public function getTokens($value, XSDocument $doc = null): SplFixedArray
    {
        return SplFixedArray::fromArray([$value]);
    }
}

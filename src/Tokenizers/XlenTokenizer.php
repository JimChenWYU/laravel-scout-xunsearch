<?php declare(strict_types=1);

namespace JimChen\LaravelScout\XunSearch\Tokenizers;

use JimChen\LaravelScout\XunSearch\Tokenizers\Contracts\AbstractTokenizer;
use SplFixedArray;
use XSDocument;

class XlenTokenizer extends AbstractTokenizer
{
    public function getTokens($value, XSDocument $doc = null): SplFixedArray
    {
        $terms = [];
        for ($i = 0, $iMax = strlen($value); $i < $iMax; $i += $this->mode) {
            $terms[] = substr($value, $i, $this->mode);
        }
        return SplFixedArray::fromArray($terms);
    }
}

<?php declare(strict_types=1);

namespace JimChen\LaravelScout\XunSearch\Tokenizers;

use JimChen\LaravelScout\XunSearch\Tokenizers\Contracts\AbstractTokenizer;
use SplFixedArray;
use XSDocument;

class XstepTokenizer extends AbstractTokenizer
{
    public function getTokens($value, XSDocument $doc = null): SplFixedArray
    {
        $terms = [];
        $i = $this->mode;
        while (true) {
            $terms[] = substr($value, 0, $i);
            if ($i >= strlen($value)) {
                break;
            }
            $i += $this->mode;
        }
        return SplFixedArray::fromArray($terms);
    }
}

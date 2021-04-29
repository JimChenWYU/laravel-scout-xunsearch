<?php declare(strict_types=1);

namespace JimChen\LaravelScout\XunSearch\Tokenizers;

use JimChen\LaravelScout\XunSearch\Tokenizers\Contracts\AbstractTokenizer;
use SplFixedArray;
use XSDocument;

class SplitTokenizer extends AbstractTokenizer
{
    protected $mode = ' ';

    public function getTokens($value, XSDocument $doc = null): SplFixedArray
    {
        if (strlen($this->mode) > 2 && strpos($this->mode, '/') === 0 && $this->mode[strlen($this->mode) - 1] === '/') {
            $tokens = preg_split($this->mode, $value);
        } else {
            $tokens = explode($this->mode, $value);
        }

        return SplFixedArray::fromArray($tokens);
    }
}

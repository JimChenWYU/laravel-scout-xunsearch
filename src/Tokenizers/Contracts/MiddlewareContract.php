<?php declare(strict_types=1);

namespace JimChen\LaravelScout\XunSearch\Tokenizers\Contracts;

interface MiddlewareContract
{
    /**
     * @param TokenizerContract $tokenizer
     * @param callable $next
     * @return TokenizerContract
     */
    public function handle($tokenizer, $next);
}

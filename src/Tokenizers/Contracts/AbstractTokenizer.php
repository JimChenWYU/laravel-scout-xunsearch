<?php declare(strict_types=1);

namespace JimChen\LaravelScout\XunSearch\Tokenizers\Contracts;

use JimChen\LaravelScout\XunSearch\Builders\TokenizerBuilder;
use JimChen\LaravelScout\XunSearch\Pipeline;
use JimChen\LaravelScout\XunSearch\Tokenizers\Results\Top;
use SplFixedArray;

abstract class AbstractTokenizer implements TokenizerContract
{
    /** @var \XS */
    protected $xs;

    /** @var mixed */
    protected $mode;

    public function __construct(TokenizerBuilder $builder)
    {
        $this->xs = $builder->xs;
        $this->mode = $builder->mode;
        $this->init();
    }

    protected function init()
    {
        //
    }

    public static function builder()
    {
        return new TokenizerBuilder(static::class);
    }

    /**
     * @param array $middlewares
     * @return self
     */
    public function throughMiddleware(array $middlewares = [])
    {
        return (new Pipeline())
            ->send($this)
            ->through($middlewares)
            ->then(function (TokenizerContract $tokenizer) {
                return $tokenizer;
            });
    }

    public function getTops($text, $limit = 10, $xattr = ''): SplFixedArray
    {
        /** @var SplFixedArray $fixdArray */
        $fixdArray = tap($this->getTokens($text), function (SplFixedArray $array) use ($limit) {
            $array->setSize($limit);
        });

        $newFixedArray = new SplFixedArray($fixdArray->getSize());
        foreach ($fixdArray as $k => $token) {
            $newFixedArray->offsetSet($k, new Top(1, '', $token));
        }
        return $newFixedArray;
    }
}

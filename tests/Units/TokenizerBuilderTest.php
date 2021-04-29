<?php declare(strict_types=1);

namespace Tests\Units;

use Illuminate\Support\Facades\Event;
use JimChen\LaravelScout\XunSearch\Builders\TokenizerBuilder;
use JimChen\LaravelScout\XunSearch\Events\AfterBuildTokenizer;
use JimChen\LaravelScout\XunSearch\Events\BeforeBuildTokenizer;
use JimChen\LaravelScout\XunSearch\Tokenizers\Contracts\AbstractTokenizer;
use JimChen\LaravelScout\XunSearch\Tokenizers\Contracts\TokenizerContract;
use SplFixedArray;
use Tests\TestCase;
use XSDocument;

class TokenizerBuilderTest extends TestCase
{
    public function test_build()
    {
        Event::fake([
            BeforeBuildTokenizer::class,
            AfterBuildTokenizer::class,
        ]);

        self::assertInstanceOf(
            NoneTokenizer::class,
            (new TokenizerBuilder(NoneTokenizer::class))->build()
        );

        Event::assertDispatched(BeforeBuildTokenizer::class, 1);
        Event::assertDispatched(AfterBuildTokenizer::class, 1);

        Event::assertDispatched(BeforeBuildTokenizer::class, function ($event) {
            self::assertInstanceOf(TokenizerBuilder::class, $event->builder);

            return true;
        });

        Event::assertDispatched(AfterBuildTokenizer::class, function ($event) {
            self::assertInstanceOf(TokenizerBuilder::class, $event->builder);
            self::assertInstanceOf(NoneTokenizer::class, $event->tokenizer);

            return true;
        });
    }

    public function test_throw_exception()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Class '" . NotTokenizer::class . "' not implement '" . TokenizerContract::class . "'");
        (new TokenizerBuilder(NotTokenizer::class))->build();
    }
}

class NotTokenizer
{
}

class NoneTokenizer extends AbstractTokenizer
{
    public function getTops($text, $limit = 10, $xattr = ''): SplFixedArray
    {
        return SplFixedArray::fromArray([]);
    }

    public function getTokens($value, XSDocument $doc = null): SplFixedArray
    {
        return SplFixedArray::fromArray([]);
    }
}

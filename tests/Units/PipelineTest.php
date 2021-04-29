<?php

namespace Tests\Units;

use JimChen\LaravelScout\XunSearch\Pipeline;
use Tests\TestCase;

class TokenizerPipelineTest extends TestCase
{
    public function testPipelineBasicUsage()
    {
        unset($_SERVER['__test.pipe.one'], $_SERVER['__test.pipe.two']);

        $pipeTwo = function ($piped, $next) {
            $_SERVER['__test.pipe.two'] = $piped;

            return $next($piped);
        };

        $result = (new Pipeline)
            ->send('foo')
            ->through([ PipelineTestPipeOne::class, $pipeTwo ])
            ->then(function ($piped) {
                return $piped;
            });

        self::assertSame('foo', $result);
        self::assertSame('foo', $_SERVER['__test.pipe.one']);
        self::assertSame('foo', $_SERVER['__test.pipe.two']);

        unset($_SERVER['__test.pipe.one'], $_SERVER['__test.pipe.two']);
    }

    public function testPipelineUsageWithObjects()
    {
        unset($_SERVER['__test.pipe.one']);

        $result = (new Pipeline)
            ->send('foo')
            ->through([new PipelineTestPipeOne])
            ->then(function ($piped) {
                return $piped;
            });

        self::assertSame('foo', $result);
        self::assertSame('foo', $_SERVER['__test.pipe.one']);

        unset($_SERVER['__test.pipe.one']);
    }

    public function testPipelineUsageWithInvokableObjects()
    {
        unset($_SERVER['__test.pipe.one']);

        $result = (new Pipeline)
            ->send('foo')
            ->through([new PipelineTestPipeTwo])
            ->then(
                function ($piped) {
                    return $piped;
                }
            );

        self::assertSame('foo', $result);
        self::assertSame('foo', $_SERVER['__test.pipe.one']);

        unset($_SERVER['__test.pipe.one']);
    }

    public function testPipelineUsageWithCallable()
    {
        unset($_SERVER['__test.pipe.one']);

        $function = function ($piped, $next) {
            $_SERVER['__test.pipe.one'] = 'foo';

            return $next($piped);
        };

        $result = (new Pipeline)
            ->send('foo')
            ->through([$function])
            ->then(
                function ($piped) {
                    return $piped;
                }
            );

        self::assertSame('foo', $result);
        self::assertSame('foo', $_SERVER['__test.pipe.one']);

        unset($_SERVER['__test.pipe.one']);

        $result = (new Pipeline)
            ->send('bar')
            ->through($function)
            ->then(function ($piped) {
                return $piped;
            });

        self::assertSame('bar', $result);
        self::assertSame('foo', $_SERVER['__test.pipe.one']);

        unset($_SERVER['__test.pipe.one']);
    }

    public function testPipelineUsageWithInvokableClass()
    {
        unset($_SERVER['__test.pipe.one']);

        $result = (new Pipeline)
            ->send('foo')
            ->through([PipelineTestPipeTwo::class])
            ->then(
                function ($piped) {
                    return $piped;
                }
            );

        self::assertSame('foo', $result);
        self::assertSame('foo', $_SERVER['__test.pipe.one']);

        unset($_SERVER['__test.pipe.one']);
    }


    public function testPipelineUsageWithParameters()
    {
        unset($_SERVER['__test.pipe.parameters']);

        $parameters = ['one', 'two'];

        $result = (new Pipeline)
            ->send('foo')
            ->through(PipelineTestParameterPipe::class.':'.implode(',', $parameters))
            ->then(function ($piped) {
                return $piped;
            });

        self::assertSame('foo', $result);
        self::assertEquals($parameters, $_SERVER['__test.pipe.parameters']);

        unset($_SERVER['__test.pipe.parameters']);
    }
}

class PipelineTestPipeOne
{
    public function handle($tokenizer, $next)
    {
        $_SERVER['__test.pipe.one'] = $tokenizer;
        return $next($tokenizer);
    }
}

class PipelineTestPipeTwo
{
    public function __invoke($piped, $next)
    {
        $_SERVER['__test.pipe.one'] = $piped;

        return $next($piped);
    }
}

class PipelineTestParameterPipe
{
    public function handle($piped, $next, $parameter1 = null, $parameter2 = null)
    {
        $_SERVER['__test.pipe.parameters'] = [$parameter1, $parameter2];

        return $next($piped);
    }
}

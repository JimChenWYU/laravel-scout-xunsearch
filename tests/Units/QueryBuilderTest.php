<?php declare(strict_types=1);

namespace Tests\Units;

use JimChen\LaravelScout\XunSearch\QueryBuilder;
use Tests\TestCase;

class QueryBuilderTest extends TestCase
{
    public function testBasicQuery()
    {
        $builder = new QueryBuilder();
        self::assertEquals('foo', $builder->logic('foo')->build());

        $builder = new QueryBuilder();
        self::assertEquals('foo AND bar', $builder->logic('foo')->andLogic('bar')->build());

        $builder = new QueryBuilder();
        self::assertEquals('foo OR bar', $builder->logic('foo')->orLogic('bar')->build());

        $builder = new QueryBuilder();
        self::assertEquals(
            'foo AND bar OR zoo AND foobar',
            $builder
                ->logic('foo')
                ->andLogic('bar')
                ->orLogic('zoo')
                ->andLogic('foobar')
                ->build()
        );
    }

    public function testNotQuery()
    {
        $builder = new QueryBuilder();
        self::assertEquals(
            'NOT foo',
            $builder
                ->notLogic('foo')
                ->build()
        );

        $builder = new QueryBuilder();
        self::assertEquals(
            'foo NOT abc',
            $builder
                ->logic('foo')
                ->notLogic('abc')
                ->build()
        );

        $builder = new QueryBuilder();
        self::assertEquals(
            'foo AND bar OR zoo NOT abc',
            $builder
                ->logic('foo')
                ->andLogic('bar')
                ->orLogic('zoo')
                ->notLogic('abc')
                ->build()
        );

        $builder = new QueryBuilder();
        self::assertEquals(
            'foo AND bar OR zoo NOT (abc cbd)',
            $builder
                ->logic('foo')
                ->andLogic('bar')
                ->orLogic('zoo')
                ->notLogic('abc')
                ->notLogic('cbd')
                ->build()
        );
    }

    public function testRepeat()
    {
        $builder = new QueryBuilder();
        self::assertEquals(
            'foo AND foo OR foo',
            $builder
                ->logic('foo')
                ->andLogic('foo')
                ->orLogic('foo')
                ->build()
        );

        $builder = new QueryBuilder();
        self::assertEquals(
            'foo NOT (foo foo)',
            $builder
                ->logic('foo')
                ->notLogic('foo')
                ->notLogic('foo')
                ->build()
        );

        $builder = new QueryBuilder();
        self::assertEquals(
            'foo AND foo OR foo NOT (foo foo)',
            $builder
                ->logic('foo')
                ->andLogic('foo')
                ->orLogic('foo')
                ->notLogic('foo')
                ->notLogic('foo')
                ->build()
        );
    }

    public function testArrayQuery()
    {
        $builder = new QueryBuilder();
        self::assertEquals(
            '(foo AND bar)',
            $builder->logic(['foo', 'bar'])->build()
        );

        $builder = new QueryBuilder();
        self::assertEquals(
            '(bar OR zoo)',
            $builder
                ->logic([['bar', 'and'], ['zoo', 'or']])
                ->build()
        );

        $builder = new QueryBuilder();
        self::assertEquals(
            'NOT (foo bar)',
            $builder
                ->notLogic(['foo', 'bar'])
                ->build()
        );

        $builder = new QueryBuilder();
        self::assertEquals(
            'NOT (foo bar)',
            $builder
                ->notLogic([['foo', 'and'], ['bar', 'and']])
                ->build()
        );

        $builder = new QueryBuilder();
        self::assertEquals(
            '((foo OR bar) OR zoo) AND bar OR zoo NOT abc',
            $builder
                ->logic([[[['foo', 'and'], ['bar', 'or']], 'and'], ['zoo', 'or']])
                ->andLogic('bar')
                ->orLogic('zoo')
                ->notLogic('abc')
                ->build()
        );
    }
}

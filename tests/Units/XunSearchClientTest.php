<?php declare(strict_types=1);

namespace Tests\Units;

use JimChen\LaravelScout\XunSearch\Queries\FieldQuery;
use JimChen\LaravelScout\XunSearch\Queries\MixedQuery;
use JimChen\LaravelScout\XunSearch\Queries\Query;
use JimChen\LaravelScout\XunSearch\XunSearchClient;
use Tests\TestCase;

class XunSearchClientTest extends TestCase
{
    public function test_loadconfig()
    {
        $client = new XunSearchClient(
            'localhost:8383',
            'localhost:8384',
            'gbk',
            [
                'schemas' => [
                    'user' => [
                        'id' => [
                            'type' => 'id',
                        ],
                    ],
                ],
            ]
        );

        self::assertEquals([
            'server.search' => 'localhost:8384',
            'server.index' => 'localhost:8383',
            'project.default_charset' => 'gbk',
            'project.name' => 'user',
            'id' => [
                'type' => 'id'
            ],
        ], $client->loadConfig('user'));
    }

    public function test_single_instance_xunsearch_init()
    {
        $client = new TestXunSearchClient(
            'localhost:8383',
            'localhost:8384',
            'gbk',
            [
                'schemas' => [
                    'user' => [
                        'id' => [
                            'type' => 'id',
                        ],
                    ],
                    'post' => [
                        'id' => [
                            'type' => 'id',
                        ],
                    ],
                ],
            ]
        );

        $o1 = $client->testInitXunSearch('user');
        $o2 = $client->testInitXunSearch('user');
        $o3 = $client->testInitXunSearch('post');

        self::assertTrue($o1 === $o2);
        self::assertFalse($o2 === $o3);
    }

    public function test_build_query_base_usage()
    {
        $client = new TestXunSearchClient(
            'localhost:8383',
            'localhost:8384',
            'gbk',
            [
                'schemas' => [
                    'user' => [
                        'id' => [
                            'type' => 'id',
                        ],
                    ],
                    'post' => [
                        'id' => [
                            'type' => 'id',
                        ],
                    ],
                ],
            ]
        );

        self::assertEquals('foobar', $client->buildQuery('foobar'));
        self::assertEquals('foobar', $client->buildQuery(function () {
            return 'foobar';
        }));
        self::assertEquals('foobar', $client->buildQuery(new XunSearchQuery()));
        self::assertEquals('foobar', $client->buildQuery(new ObjectImplementInvoke()));
    }

    public function test_build_mixedQuery()
    {
        $client = new TestXunSearchClient(
            'localhost:8383',
            'localhost:8384',
            'gbk',
            [
                'schemas' => [
                    'user' => [
                        'id' => [
                            'type' => 'id',
                        ],
                    ],
                    'post' => [
                        'id' => [
                            'type' => 'id',
                        ],
                    ],
                ],
            ]
        );

        self::assertEquals('foobar', $client->buildQuery(new MixedQuery('foobar')));
    }

    public function test_build_fieldQuery()
    {
        $client = new TestXunSearchClient(
            'localhost:8383',
            'localhost:8384',
            'gbk',
            [
                'schemas' => [
                    'user' => [
                        'id' => [
                            'type' => 'id',
                        ],
                    ],
                    'post' => [
                        'id' => [
                            'type' => 'id',
                        ],
                    ],
                ],
            ]
        );

        self::assertEquals('foo:bar', $client->buildQuery(new FieldQuery('bar', 'foo')));
        self::assertEquals('bar', $client->buildQuery(new FieldQuery('bar', '')));
    }
}

class XunSearchQuery implements Query
{
    public function __toString()
    {
        return 'foobar';
    }
}

class ObjectImplementInvoke
{
    public function __invoke()
    {
        return 'foobar';
    }
}

class TestXunSearchClient extends XunSearchClient
{
    public function testInitXunSearch(string $indexName)
    {
        return $this->initXunSearch($indexName);
    }
}

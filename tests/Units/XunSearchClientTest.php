<?php declare(strict_types=1);

namespace Tests\Units;

use donatj\Ini\Builder as IniBuilder;
use JimChen\LaravelScout\XunSearch\Queries\FieldQuery;
use JimChen\LaravelScout\XunSearch\Queries\MixedQuery;
use JimChen\LaravelScout\XunSearch\Queries\Query;
use JimChen\LaravelScout\XunSearch\XunSearchClient;
use Mockery as m;
use Psr\SimpleCache\CacheInterface;
use Tests\TestCase;

class XunSearchClientTest extends TestCase
{
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();

        $cache = m::mock(CacheInterface::class);
        $cache->shouldReceive('get')->withAnyArgs()->andReturn(false);
        $cache->shouldReceive('set')->withAnyArgs()->andReturn(false);

        $this->client = new TestXunSearchClient(
            'localhost:8383',
            'localhost:8384',
            $cache,
            'gbk', [
                'schemas' => [
                    'user' => [
                        'id' => [
                            'type' => 'id',
                        ],
                    ],
                ],
            ]
        );
    }

    public function test_loadconfig()
    {
        $client = $this->client;

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
        $client = $this->client;

        $o1 = $client->testInitXunSearch('user');
        $o2 = $client->testInitXunSearch('user');
        $o3 = $client->testInitXunSearch('post');

        self::assertTrue($o1 === $o2);
        self::assertFalse($o2 === $o3);
    }

    public function test_build_query_base_usage()
    {
        $client = $this->client;

        self::assertEquals('foobar', $client->buildQuery('foobar'));
        self::assertEquals('foobar', $client->buildQuery(function () {
            return 'foobar';
        }));
        self::assertEquals('foobar', $client->buildQuery(new XunSearchQuery()));
        self::assertEquals('foobar', $client->buildQuery(new ObjectImplementInvoke()));
    }

    public function test_build_mixedQuery()
    {
        $client = $this->client;


        self::assertEquals('foobar', $client->buildQuery(new MixedQuery('foobar')));
    }

    public function test_build_fieldQuery()
    {
        $client = $this->client;

        self::assertEquals('foo:bar', $client->buildQuery(new FieldQuery('bar', 'foo')));
        self::assertEquals('bar', $client->buildQuery(new FieldQuery('bar', '')));
    }

    public function test_build_xunsearch_from_cache_ini()
    {
        $cache = m::mock(CacheInterface::class);
        $ini = <<<INI
project.name = demo

[id]
type = id

[nickname]
index = mixed
INI;
        $cache->shouldReceive('get')->with('demo')->andReturn($ini);
        $cache->shouldNotReceive('set');

        $client = new TestXunSearchClient(
            'localhost:8383',
            'localhost:8384',
            $cache,
            'gbk', [
                'schemas' => [
                    'demo' => [
                        'id' => [
                            'type' => 'id',
                        ],
                        'nickname' => [
                            'type' => 'string',
                            'index' => 'self',
                        ],
                    ],
                ],
            ]
        );

        self::assertEquals($ini, $client->loadIni('demo'));
    }

    public function test_build_xunsearch_missing_cache_ini()
    {
        $cache = m::mock(CacheInterface::class);
        $cache->shouldReceive('get')->with('demo')->andReturn(false);
        $cache->shouldReceive('set')->once()->with('demo', m::any())->andReturn(true);

        $client = new TestXunSearchClient(
            'localhost:8383',
            'localhost:8384',
            $cache,
            'gbk', [
                'schemas' => [
                    'demo' => [
                        'id' => [
                            'type' => 'id',
                        ],
                        'nickname' => [
                            'type' => 'string',
                            'index' => 'self',
                        ],
                    ],
                ],
            ]
        );

        self::assertEquals((new IniBuilder)->generate($client->loadConfig('demo')), $client->loadIni('demo'));
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

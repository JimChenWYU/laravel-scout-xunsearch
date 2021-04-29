<?php declare(strict_types=1);

namespace Tests\Units;

use JimChen\LaravelScout\XunSearch\Builders\TokenizerBuilder;
use JimChen\LaravelScout\XunSearch\Tokenizers\Results\Top;
use JimChen\LaravelScout\XunSearch\XunSearchClient;
use Mockery as m;
use SplFixedArray;
use Tests\TestCase;

class XunSearchClientTest extends TestCase
{
    public function test_participle()
    {
        $builder = m::mock(TokenizerBuilder::class);
        $builder->shouldReceive('withXs')->withAnyArgs()->andReturnSelf();
        $builder->shouldReceive('build')->withAnyArgs()->andReturnSelf();
        $builder->shouldReceive('throughMiddleware')->withAnyArgs()->andReturnSelf();
        $builder->shouldReceive('getTops')->with(
            'foobar',
            5,
            'n,nr,ns,nz,v,vn'
        )->andReturn(SplFixedArray::fromArray([
            new Top(1, '', 'foo'),
            new Top(1, '', 'bar'),
        ]));

        $client = new XunSearchClient(
            'localhost:8383',
            'localhost:8384',
            $builder,
            'uft8',
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
            'foo', 'bar'
        ], $client->participle('user', 'foobar'));
    }

    public function test_loadconfig()
    {
        $builder = m::mock(TokenizerBuilder::class);

        $client = new XunSearchClient(
            'localhost:8383',
            'localhost:8384',
            $builder,
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
        $builder = m::mock(TokenizerBuilder::class);
        $client = new TestXunSearchClient(
            'localhost:8383',
            'localhost:8384',
            $builder,
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
}

class TestXunSearchClient extends XunSearchClient
{
    public function testInitXunSearch(string $indexName)
    {
        return $this->initXunSearch($indexName);
    }
}

<?php declare(strict_types=1);

namespace Tests\Units;

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
}

class TestXunSearchClient extends XunSearchClient
{
    public function testInitXunSearch(string $indexName)
    {
        return $this->initXunSearch($indexName);
    }
}

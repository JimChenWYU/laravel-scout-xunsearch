<?php declare(strict_types=1);

namespace Tests\Units;

use Tests\TestCase;

class CommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app['config']->set('scout.xunsearch.storage', [
            'cache'   => [
                'path'    => test_cache_path(),
            ],
            'schema' => [
                'foo' => [
                    'id' => [
                        'type' => 'id'
                    ],
                    'name' => [
                        'type' => 'string',
                        'index' => 'self',
                    ],
                ],
                'bar' => [
                    'id' => [
                        'type' => 'id'
                    ],
                ],
            ],
        ]);
    }

    /** @test */
    public function it_config_cache()
    {
        self::assertFileNotExists(test_cache_path('foo.ini'));
        self::assertFileNotExists(test_cache_path('bar.ini'));

        $this->artisan('xunsearch:config:cache')->assertExitCode(0);

        self::assertFileExists(test_cache_path('foo.ini'));
        self::assertFileExists(test_cache_path('bar.ini'));
    }

    /** @test */
    public function it_config_clear()
    {
        self::assertFileExists(test_cache_path('foo.ini'));
        self::assertFileExists(test_cache_path('bar.ini'));

        $this->artisan('xunsearch:config:clear')->assertExitCode(0);

        self::assertFileNotExists(test_cache_path('foo.ini'));
        self::assertFileNotExists(test_cache_path('bar.ini'));
    }
}

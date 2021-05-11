<?php declare(strict_types=1);

namespace Tests\Units;

use Illuminate\Database\Eloquent\Model;
use JimChen\LaravelScout\XunSearch\SchemaCache;
use Laravel\Scout\Searchable;
use Tests\TestCase;

class IniFlushCommandTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->app['config']->set('scout.xunsearch.storage.cache.enabled', true);
        $this->app['config']->set('scout.xunsearch.storage.cache.store', 'array');
        $this->app['config']->set('scout.xunsearch.storage.cache.prefix', 'test');
    }

    /** @test */
    public function it_can_flush()
    {
        /** @var SchemaCache $cache */
        $cache = $this->app->make(SchemaCache::class);

        $cache->clear();

        $cache->set('xunsearch.cache.ini', 'first');
        $cache->set('foobar', 'second');

        $this->artisan('scout:xs-ini-flush')->assertExitCode(0);

        self::assertFalse($cache->has('xunsearch.cache.ini'));
        self::assertTrue($cache->has('foobar'));

        $cache->clear();
    }
}

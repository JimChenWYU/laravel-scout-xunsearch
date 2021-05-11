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

        $cache->set('foo', 'first');
        $cache->set('bar', 'second');

        $this->artisan('scout:xs-ini-flush', [
            'model' => Foo::class,
        ])->assertExitCode(0);

        self::assertFalse($cache->has('foo'));
        self::assertTrue($cache->has('bar'));

        $cache->clear();
    }
}

class Foo extends Model
{
    use Searchable;

    public function searchableAs()
    {
        return 'foo';
    }
}

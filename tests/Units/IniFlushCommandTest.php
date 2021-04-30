<?php

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

        $this->app['config']->set('scout.xunsearch.storage.cache.enable', true);
        $this->app['config']->set('scout.xunsearch.storage.cache.store', 'array');
        $this->app['config']->set('scout.xunsearch.storage.cache.prefix', 'test');
    }

    /** @test */
    public function it_can_flush()
    {
        /** @var SchemaCache $cache */
        $cache = $this->app->make(SchemaCache::class);

        $cache->clear();

        $model1 = new User;
        $model2 = new Post;

        $cache->set($model1->searchableAs(), 'first');
        $cache->set($model2->searchableAs(), 'second');

        $this->artisan('scout:xs-ini-flush', [
            'model' => User::class
        ])->assertExitCode(0);

        self::assertFalse($cache->has($model1->searchableAs()));
        self::assertTrue($cache->has($model2->searchableAs()));

        $this->artisan('scout:xs-ini-flush', [
            'model' => 'all'
        ])->assertExitCode(0);

        self::assertFalse($cache->has($model2->searchableAs()));

        $cache->clear();
    }
}

class User extends Model
{
    use Searchable;

    public function searchableAs()
    {
        return 'user';
    }
}

class Post extends Model
{
    use Searchable;

    public function searchableAs()
    {
        return 'post';
    }
}


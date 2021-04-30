<?php declare(strict_types=1);

namespace Tests\Units;

use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use JimChen\LaravelScout\XunSearch\SchemaCache;
use Tests\TestCase;

class SchemaCachetTest extends TestCase
{
    protected $cache;

    protected function setUp(): void
    {
        Cache::shouldReceive('store')
            ->with('array')
            ->andReturn(new Repository(new ArrayStore()));

        parent::setUp();
    }

    public function test_clear()
    {
        $cache = new SchemaCache(true, 'array', 'test');

        $cache->set('foo', 'bar');
        $cache->clear();
        self::assertFalse($cache->has('foo'));

        $cache->clear();
    }

    public function test_enable_cache()
    {
        $cache = new SchemaCache(true, 'array', 'test');
        self::assertTrue($cache->set('foo', 'bar'));
        self::assertTrue($cache->has('foo'));
        self::assertFalse($cache->has('foo_bar'));
        self::assertEquals('bar', $cache->get('foo'));
        self::assertNull($cache->get('foo_bar'));

        $cache->clear();

        self::assertTrue($cache->setMultiple(['foo' => 'bar', 'foo_bar' => 'foo']));
        self::assertEquals(['foo' => 'bar', 'foo_bar' => 'foo'], $cache->getMultiple(['foo', 'foo_bar']));
        self::assertTrue($cache->deleteMultiple(['foo', 'foo_bar']));
        self::assertEquals(['foo' => null, 'foo_bar' => null], $cache->getMultiple(['foo', 'foo_bar']));

        $cache->clear();

        self::assertTrue($cache->set('foo', 'bar'));
        self::assertTrue($cache->has('foo'));
        self::assertTrue($cache->delete('foo'));
        self::assertFalse($cache->has('foo'));

        $cache->clear();
    }

    public function test_disable_cache()
    {
        $cache = new SchemaCache(false, 'array', 'test');

        // get
        self::assertTrue($cache->set('foo', 'bar'));
        self::assertNull($cache->get('foo'));
        self::assertEquals('zoo', $cache->get('foo', 'zoo'));

        $cache->clear();

        // has
        self::assertTrue($cache->set('foo', 'bar'));
        self::assertFalse($cache->has('foo'));

        $cache->clear();

        // delete
        self::assertTrue($cache->set('foo', 'bar'));
        self::assertTrue($cache->delete('foo'));

        $cache->clear();

        // getMultiple
        self::assertTrue($cache->setMultiple(['foo' => 'bar', 'foo_bar' => 'foo']));
        self::assertEquals(['foo' => null, 'foo_bar' => null], $cache->getMultiple(['foo', 'foo_bar']));
        self::assertEquals(['foo' => 'zoo', 'foo_bar' => 'zoo'], $cache->getMultiple(['foo', 'foo_bar'], 'zoo'));

        $cache->clear();
    }
}

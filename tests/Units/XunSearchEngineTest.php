<?php declare(strict_types=1);

namespace Tests\Units;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Config;
use JimChen\LaravelScout\XunSearch\Engines\XunSearchEngine;
use JimChen\LaravelScout\XunSearch\XunSearchClient as SearchClient;
use Laravel\Scout\Builder;
use Mockery as m;
use stdClass;
use Tests\Fixtures\EmptySearchableModel;
use Tests\Fixtures\SearchableModel;
use Tests\Fixtures\SoftDeletedEmptySearchableModel;
use Tests\TestCase;
use XSDocument;

class XunSearchEngineTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Config::shouldReceive('get')->with('scout.after_commit', m::any())->andReturn(false);
    }

    protected function tearDown(): void
    {
        m::close();
    }

    public function test_update_adds_objects_to_index()
    {
        $client = m::mock(SearchClient::class);
        $client->shouldReceive('initIndex')->with('table')->andReturn($index = m::mock(stdClass::class));
        $index->shouldReceive('openBuffer', 'closeBuffer', 'flushIndex')->withNoArgs();
        $index->shouldReceive('update')->with([
            new XSDocument([
                'id' => 1,
            ])
        ]);

        $engine = new XunSearchEngine($client);
        $engine->update(Collection::make([new SearchableModel()]));

        self::assertTrue(true);
    }

    public function test_delete_removes_objects_to_index()
    {
        $client = m::mock(SearchClient::class);
        $client->shouldReceive('initIndex')->with('table')->andReturn($index = m::mock(stdClass::class));
        $index->shouldReceive('del')->with([1]);
        $index->shouldReceive('flushIndex')->withNoArgs();
        $engine = new XunSearchEngine($client);
        $engine->delete(Collection::make([new SearchableModel(['id' => 1])]));

        self::assertTrue(true);
    }

    public function test_search_sends_correct_parameters_to_xunsearch()
    {
        $client = m::mock(SearchClient::class);
        $client->shouldReceive('initSearch')->with('table')->andReturn($search = m::mock(stdClass::class));
        $client->shouldReceive('participle')->with('table', 'zonda')->andReturn(['zonda']);
        $client->shouldReceive('buildQuery')->with('zonda')->andReturn('zonda');
        $search->shouldReceive('search')->with(null, false)->andReturn([1,2,3]);
        $search->shouldReceive('getLastCount')->withNoArgs()->andReturn(3);
        $search->shouldReceive('setQuery')->with('zonda')->andReturnSelf();
        $search->shouldReceive('setLimit')->withAnyArgs()->andReturnSelf();
        $search->shouldReceive('addRange')->with('foo', 1, 1)->andReturnSelf();

        $engine = new XunSearchEngine($client);
        $builder = new Builder(new SearchableModel(), 'zonda');
        $builder->where('foo', 1);
        self::assertEquals([
            'hits' => [1, 2, 3],
            'nbHits' => 3,
            'facets' => collect([])
        ], $engine->search($builder));
    }

    public function test_map_correctly_maps_results_to_models()
    {
        $client = m::mock(SearchClient::class);
        $engine = new XunSearchEngine($client);

        $model = m::mock(stdClass::class);
        $model->shouldReceive('getKeyName')->andReturn('id');
        $model->shouldReceive('getScoutModelsByIds')->andReturn($models = Collection::make([
            new SearchableModel(['id' => 1]),
        ]));

        $builder = m::mock(Builder::class);

        $results = $engine->map($builder, ['nbHits' => 1, 'hits' => [
            ['id' => 1],
        ]], $model);

        self::assertCount(1, $results);
    }

    public function test_map_method_respects_order()
    {
        $client = m::mock(SearchClient::class);
        $engine = new XunSearchEngine($client);

        $model = m::mock(stdClass::class);
        $model->shouldReceive('getKeyName')->andReturn('id');
        $model->shouldReceive('getScoutModelsByIds')->andReturn($models = Collection::make([
            new SearchableModel(['id' => 1]),
            new SearchableModel(['id' => 2]),
            new SearchableModel(['id' => 3]),
            new SearchableModel(['id' => 4]),
        ]));

        $builder = m::mock(Builder::class);

        $results = $engine->map($builder, ['nbHits' => 4, 'hits' => [
            ['id' => 1],
            ['id' => 2],
            ['id' => 4],
            ['id' => 3],
        ]], $model);

        self::assertCount(4, $results);

        // It's important we assert with array keys to ensure
        // they have been reset after sorting.
        self::assertEquals([
            0 => ['id' => 1],
            1 => ['id' => 2],
            2 => ['id' => 4],
            3 => ['id' => 3],
        ], $results->toArray());
    }

    public function test_update_empty_searchable_array_does_not_add_objects_to_index()
    {
        $client = m::mock(SearchClient::class);
        $client->shouldReceive('initIndex')->with('table')->andReturn($index = m::mock(stdClass::class));
        $index->shouldReceive('openBuffer', 'closeBuffer', 'flushIndex')->withNoArgs();
        $index->shouldNotReceive('update');

        $engine = new XunSearchEngine($client);
        $engine->update(Collection::make([new EmptySearchableModel()]));

        self::assertTrue(true);
    }

    public function test_update_empty_searchable_array_from_soft_deleted_model_does_not_add_objects_to_index()
    {
        $client = m::mock(SearchClient::class);
        $client->shouldReceive('initIndex')->with('table')->andReturn($index = m::mock('StdClass'));
        $index->shouldReceive('openBuffer', 'closeBuffer', 'flushIndex')->withNoArgs();
        $index->shouldNotReceive('update');

        $engine = new XunSearchEngine($client, true);
        $engine->update(Collection::make([new SoftDeletedEmptySearchableModel()]));

        self::assertTrue(true);
    }
}

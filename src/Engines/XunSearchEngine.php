<?php declare(strict_types=1);

namespace JimChen\LaravelScout\XunSearch\Engines;

use XSDocument;
use Laravel\Scout\Builder;
use Laravel\Scout\Engines\Engine;
use JimChen\LaravelScout\XunSearch\XunSearchClient;
use JimChen\LaravelScout\XunSearch\Operators\RangeOperator;
use JimChen\LaravelScout\XunSearch\Operators\FuzzyOperator;
use JimChen\LaravelScout\XunSearch\Operators\WeightOperator;
use JimChen\LaravelScout\XunSearch\Operators\FacetsOperator;
use JimChen\LaravelScout\XunSearch\Operators\CollapseOperator;

class XunSearchEngine extends Engine
{
    /**
     * @var \JimChen\LaravelScout\XunSearch\XunSearchClient
     */
    protected $xunsearch;

    /**
     * Determines if soft deletes for Scout are enabled or not.
     *
     * @var bool
     */
    protected $softDelete;

    /**
     * @var int
     */
    private $defaultPerPage = 15;

    /**
     * @param XunSearchClient $xunsearch
     * @param bool            $softDelete
     */
    public function __construct(XunSearchClient $xunsearch, bool $softDelete = false)
    {
        $this->xunsearch = $xunsearch;
        $this->softDelete = $softDelete;
    }

    /**
     * Update the given model in the index.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     *
     * @return void
     */
    public function update($models)
    {
        $index = $this->xunsearch->initIndex($this->getIndexName($models->first()));

        $index->openBuffer();

        if ($this->usesSoftDelete($models->first()) && $this->softDelete) {
            $models->each->pushSoftDeleteMetadata();
        }

        $models->each(function ($model) use ($index) {
            $array = $model->toSearchableArray();

            if (empty($array)) {
                return;
            }


            $array = array_merge([$model->getKeyName() => $model->getKey()], $array);

            $index->update(new XSDocument($array));
        });

        $index->closeBuffer();

        $index->flushIndex();
    }

    /**
     * Remove the given model from the index.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     *
     * @return void
     */
    public function delete($models)
    {
        $model = $models->first();

        $index = $this->xunsearch->initIndex($this->getIndexName($model));

        $index->del($models->pluck($model->getKeyName())->all());

        $index->flushIndex();
    }

    /**
     * Perform the given search on the engine.
     *
     * @param  \Laravel\Scout\Builder  $builder
     *
     * @return mixed
     */
    public function search(Builder $builder)
    {
        return $this->performSearch($builder, array_filter([
            'numericFilters' => $this->filters($builder),
            'hitsPerPage'    => $builder->limit,
        ]));
    }

    /**
     * Perform the given search on the engine.
     *
     * @param  \Laravel\Scout\Builder  $builder
     * @param  int  $perPage
     * @param  int  $page
     *
     * @return mixed
     */
    public function paginate(Builder $builder, $perPage, $page)
    {
        return $this->performSearch($builder, [
            'numericFilters' => $this->filters($builder),
            'hitsPerPage'    => $perPage,
            'page'           => $page - 1,
        ]);
    }

    /**
     * Pluck and return the primary keys of the given results.
     *
     * @param  mixed  $results
     * @return \Illuminate\Support\Collection
     */
    public function mapIds($results, string $keyName = 'id')
    {
        return collect($results['hits'])->pluck($keyName)->values();
    }

    /**
     * Map the given results to instances of the given model.
     *
     * @param Builder                             $builder
     * @param mixed                               $results
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function map(Builder $builder, $results, $model)
    {
        if (count($results['hits']) === 0) {
            return $model->newCollection();
        }

        $keys = $this->mapIds($results, $model->getKeyName())->all();
        $keyPositions = array_flip($keys);

        return $model->getScoutModelsByIds(
            $builder,
            $keys
        )->filter(function ($model) use ($keys) {
            return in_array($model->getScoutKey(), $keys);
        })->sortBy(function ($model) use ($keyPositions) {
            return $keyPositions[$model->getScoutKey()];
        })->values();
    }

    /**
     * Get the total count from a raw result returned by the engine.
     *
     * @param mixed $results
     * @return int
     */
    public function getTotalCount($results)
    {
        return $results['nbHits'];
    }

    /**
     * Flush all of the model's records from the engine.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function flush($model)
    {
        $index = $this->xunsearch->initIndex($this->getIndexName($model));
        $index->clean();
        $index->flushIndex();
    }

    /**
     * Perform the given search on the engine.
     *
     * @param  \Laravel\Scout\Builder  $builder
     *
     * @return array
     */
    protected function performSearch(Builder $builder, array $options = [], bool $or = false)
    {
        $indexName = $builder->index ?: $this->getIndexName($builder->model);

        $search = $this->xunsearch->initSearch($indexName);

        if ($builder->callback) {
            return call_user_func($builder->callback, $search, $builder->query, $options);
        }

        $words = $this->xunsearch->participle($this->xunsearch->initXunSearch($indexName), $builder->query);

        $search->setQuery($this->buildSearchQuery($words, $or));

        collect($builder->wheres)->map(function ($value, $key) use ($search) {
            if ($value instanceof RangeOperator) {
                $search->addRange($key, $value->getFrom(), $value->getTo());
            } elseif ($value instanceof WeightOperator) {
                $search->addWeight($key, $value);
            } elseif ($value instanceof CollapseOperator) {
                $search->setCollapse($key, (int) sprintf('%s', $value));
            } elseif ($value instanceof FuzzyOperator) {
                $search->setFuzzy($value());
            } elseif ($value instanceof FacetsOperator) {
                $search->setFacets($value->getFields(), $value->getExact());
            } else {
                $search->addRange($key, $value, $value);
            }
        });

        /*
        collect($builder->orders)->map(function ($value, $key) use ($search) {
            $search->setSort($key, $value == 'desc');
        });
        */
        if (!empty($builder->orders)) {
            foreach ($builder->orders as $val) {
                $search->setSort($val['column'], !($val['direction'] === 'desc'));
            }
        }

        $offset = 0;
        $perPage = $options['hitsPerPage'] ?? $this->defaultPerPage;
        if (!empty($options['page'])) {
            $offset = $perPage * $options['page'];
        }
        $hits = $search->setLimit($perPage, $offset)->search();

        if (count($hits) < 3 && $or == false) {
            $this->performSearch($builder, $options, true);
        }

        $facets = collect($builder->wheres)->map(function ($value, $key) use ($search) {
            if ($value instanceof FacetsOperator) {
                return collect($value->getFields())->mapWithKeys(function ($field) use ($search) {
                    return [$field =>$search->getFacets($field)];
                });
            }
        })->collapse();

        return [ 'hits' => $hits, 'nbHits' => $search->getLastCount(), 'facets' => $facets ];
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model|\Laravel\Scout\Searchable $model
     */
    protected function getIndexName($model)
    {
        return $model->searchableAs();
    }

    /**
     * Get the filter array for the query.
     *
     * @param  \Laravel\Scout\Builder  $builder
     *
     * @return array
     */
    protected function filters(Builder $builder)
    {
        return collect($builder->wheres)->map(function ($value, $key) {
            return $key . '=' . $value;
        })->values()->all();
    }

    /**
     * @param array $words
     * @param bool  $or
     * @return string
     */
    protected function buildSearchQuery(array $words, bool $or)
    {
        if (count($words) < 2 || $or) {
            return implode(' OR ', $words);
        }

        $keyword_ = $words[0] . ' AND (';
        unset($words[0]);
        $keyword = implode(' OR ', $words);
        $keyword_ .= $keyword . ')';

        return $keyword_;
    }

    /**
     * Determine if the given model uses soft deletes.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return bool
     */
    protected function usesSoftDelete($model)
    {
        return in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($model));
    }

    /**
     * Dynamically call the Algolia client instance.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->xunsearch->$method(...$parameters);
    }
}

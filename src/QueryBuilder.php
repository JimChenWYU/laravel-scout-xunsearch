<?php declare(strict_types=1);

namespace JimChen\LaravelScout\XunSearch;

use Closure;
use JimChen\LaravelScout\XunSearch\Queries\AdjQuery;
use JimChen\LaravelScout\XunSearch\Queries\ExactMatchQuery;
use JimChen\LaravelScout\XunSearch\Queries\FieldQuery;
use JimChen\LaravelScout\XunSearch\Queries\MixedQuery;
use JimChen\LaravelScout\XunSearch\Queries\NearQuery;
use JimChen\LaravelScout\XunSearch\Queries\UnContainQuery;
use Stringable;

class QueryBuilder implements Stringable
{
    protected $terms = [];

    protected $notTerms = [];

    public function field(string $query, string $field)
    {
        return new FieldQuery($query, $field);
    }

    public function mixed(string $query)
    {
        return new MixedQuery($query);
    }

    public function unContain(string $query, array $unContains)
    {
        return new UnContainQuery($query, $unContains);
    }

    public function exactMatch(string $query)
    {
        return new ExactMatchQuery($query);
    }

    public function near(string $first, string $second, int $distance = 10)
    {
        return new NearQuery($first, $second, $distance);
    }

    public function adj(string $first, string $second, int $distance = 10)
    {
        return new AdjQuery($first, $second, $distance);
    }

    public function logic($value, string $boolean = 'AND')
    {
        if (is_array($value)) {
            if (strtoupper($boolean) === 'NOT') {
                foreach ($value as $key => $item) {
                    if (is_numeric($key) && is_array($item)) {
                        $this->notLogic(...array_values($item));
                    } else {
                        $this->notLogic($item);
                    }
                }

                return $this;
            }

            return $this->termNested(function ($query) use ($value, $boolean) {
                /** @var QueryBuilder $query */
                foreach ($value as $key => $item) {
                    if (is_numeric($key) && is_array($item)) {
                        $query->logic(...array_values($item));
                    } else {
                        $query->logic($item, $boolean);
                    }
                }
            }, $boolean);
        }

        $type = 'Basic';
        if (strtoupper($boolean) === 'NOT') {
            $this->notTerms[] = compact('type', 'value', 'boolean');
        } else {
            $this->terms[] = compact('type', 'value', 'boolean');
        }

        return $this;
    }

    public function andLogic($value)
    {
        return $this->logic($value, 'AND');
    }

    public function orLogic($value)
    {
        return $this->logic($value, 'OR');
    }

    public function notLogic($value)
    {
        return $this->logic($value, 'NOT');
    }

    public function addNestedTerm(QueryBuilder $query, $boolean = 'AND')
    {
        if (count($query->terms)) {
            $type = 'Nested';
            $this->terms[] = compact('type', 'query', 'boolean');
        }

        return $this;
    }

    public function build()
    {
        return trim($this->compileTerms($this) . ' ' . $this->compileNotTerms($this));
    }

    public function __toString()
    {
        return $this->build();
    }

    protected function termNested(Closure $callback, $boolean = 'AND')
    {
        call_user_func($callback, $query = new static());

        return $this->addNestedTerm($query, $boolean);
    }

    protected function buildTermBasic(QueryBuilder $query, $term)
    {
        return $term['value'];
    }

    protected function buildTermNested(QueryBuilder $query, $term)
    {
        return '(' . $this->compileTerms($term['query']) . ')';
    }

    protected function compileTerms(QueryBuilder $query)
    {
        if (is_null($query->terms)) {
            return '';
        }

        if (count($sql = $this->compileTermsToArray($query)) > 0) {
            return $this->concatenateTermClauses($query, $sql);
        }

        return '';
    }

    protected function compileNotTerms(QueryBuilder $query)
    {
        if (is_null($query->notTerms)) {
            return '';
        }

        $collect = collect($query->notTerms)->pluck('value');
        if ($collect->isNotEmpty()) {
            return 'NOT ' . ($collect->count() > 1 ? '(' . $collect->join(' ') . ')' : $collect->first());
        }

        return '';
    }

    protected function compileTermsToArray(QueryBuilder $query)
    {
        return collect($query->terms)
            ->map(function ($term) use ($query) {
                return strtoupper($term['boolean']) . ' ' . $this->{"buildTerm{$term['type']}"}($query, $term);
            })
            ->all();
    }

    /**
     * @param $query
     * @param $terms
     * @return string
     */
    protected function concatenateTermClauses($query, $terms)
    {
        return preg_replace('/AND |OR /i', '', implode(' ', $terms), 1);
    }
}

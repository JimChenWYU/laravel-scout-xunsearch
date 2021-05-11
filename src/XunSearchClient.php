<?php declare(strict_types=1);

namespace JimChen\LaravelScout\XunSearch;

use donatj\Ini\Builder as IniBuilder;
use Illuminate\Support\Str;
use JimChen\LaravelScout\XunSearch\Queries\Query;
use Psr\SimpleCache\CacheInterface;
use XS;

class XunSearchClient
{
    /**
     * indexHost.
     *
     * @var string
     */
    protected $indexHost;

    /**
     * searchHost.
     *
     * @var string
     */
    protected $searchHost;

    /**
     * charset
     *
     * @var string
     */
    protected $charset;

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * options.
     *
     * @var mixed
     */
    protected $options = [];

    /**
     * @var XS[]
     */
    protected $instances = [];

    /**
     * XunSearchClient constructor.
     * @param string         $indexHost
     * @param string         $searchHost
     * @param CacheInterface $cache
     * @param string         $charset
     * @param array          $options
     */
    public function __construct(
        $indexHost,
        $searchHost,
        CacheInterface $cache,
        $charset = null,
        $options = null
    ) {
        $this->indexHost = $indexHost;
        $this->searchHost = $searchHost;
        $this->cache = $cache;
        $this->charset = $charset ?? 'UTF-8';
        $this->options = $options ?? [];
        $this->cache = $cache;
    }

    /**
     * Get the XSSearch object initialized.
     *
     * @param  string $indexName
     * @return \XSSearch
     */
    public function initSearch(string $indexName)
    {
        return $this->initXunsearch($indexName)->getSearch();
    }

    /**
     * Get the XSIndex object initialized.
     *
     * @param string $indexName
     *
     * @return \XSIndex
     */
    public function initIndex(string $indexName)
    {
        return $this->initXunSearch($indexName)->getIndex();
    }

    /**
     * @param string $indexName
     * @return XS
     */
    public function initXunSearch(string $indexName)
    {
        return $this->instances[$indexName] ?? $this->instances[$indexName] = $this->buildXunSearch($this->loadIni($indexName));
    }

    /**
     * Build search engine
     *
     * @param string $ini
     * @return XS
     */
    public function buildXunSearch(string $ini)
    {
        return new XS($ini);
    }

    /**
     * @param callable|string|Query $query
     * @return string
     */
    public function buildQuery($query)
    {
        if (is_string($query)) {
            return $query;
        }

        if ($query instanceof Query) {
            return $query->__toString();
        }

        if (is_callable($query)) {
            return (string)call_user_func($query);
        }

        return (string)$query();
    }

    /**
     * @param string $schema 索引名称
     *
     * @return array
     */
    public function loadConfig(string $schema)
    {
        $config = [];
        foreach ($this->options['schemas'][$this->getSchemaName($schema)] as $field => $value) {
            $config[$field] = $value;
        }
        $config['server.search'] = $this->searchHost;
        $config['server.index'] = $this->indexHost;
        $config['project.default_charset'] = $this->charset;
        $config['project.name'] = $schema;

        return $config;
    }

    /**
     * @param string $indexName
     * @return string
     */
    public function loadIni(string $indexName)
    {
        $ini = $this->cache->get('xunsearch.cache.ini');
        if (!is_string($ini) || !$ini) {
            $ini = (new IniBuilder())->generate($this->loadConfig($indexName));
            $this->cache->set('xunsearch.cache.ini', $ini);
        }

        return $ini;
    }

    /**
     * @param string $indexName
     * @param int    $limit
     * @param string $type
     * @return \XSDocument[]
     * @throws \XSException
     */
    public function getHotQuery(string $indexName, int $limit = 10, string $type = 'total')
    {
        return $this->initSearch($indexName)->getHotQuery($limit, $type);
    }

    /**
     * @param string      $indexName
     * @param string|null $query
     * @param int         $limit
     * @return \XSDocument[]
     * @throws \XSException
     */
    public function getRelatedQuery(string $indexName, ?string $query = null, int $limit = 10)
    {
        return $this->initSearch($indexName)->getRelatedQuery($query, $limit);
    }

    /**
     * @param string $name
     * @return string
     */
    protected function getSchemaName(string $name)
    {
        $prefix = $this->options['schema_prefix'] ?? '';
        if ($prefix) {
            return Str::after($name, $prefix);
        }

        return $name;
    }
}

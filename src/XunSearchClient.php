<?php declare(strict_types=1);

namespace JimChen\LaravelScout\XunSearch;

use donatj\Ini\Builder as IniBuilder;
use Illuminate\Support\Str;
use JimChen\LaravelScout\XunSearch\Queries\Query;
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
     * options.
     *
     * @var mixed
     */
    protected $options = [];

    /**
     * @var XS[]
     */
    private $instances = [];

    /**
     * XunSearchClient constructor.
     * @param string $indexHost
     * @param string $searchHost
     * @param string $charset
     * @param array  $options
     */
    public function __construct($indexHost, $searchHost, $charset = 'uft-8', $options = [])
    {
        $this->indexHost = $indexHost;
        $this->searchHost = $searchHost;
        $this->charset = $charset;
        $this->options = $options;
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
        return $this->instances[$indexName] ?? $this->instances[$indexName] = $this->buildXunSearch($this->loadConfig($indexName));
    }

    /**
     * Build search engine
     *
     * @param array $config
     * @return XS
     */
    public function buildXunSearch(array $config)
    {
        return new XS((new IniBuilder())->generate($config));
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

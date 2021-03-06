<?php declare(strict_types=1);

namespace JimChen\LaravelScout\XunSearch;

use donatj\Ini\Builder as IniBuilder;
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
     * @var IniConfiguration
     */
    protected $cacheConfig;

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
     * @param IniConfiguration $cacheConfig
     * @param string         $charset
     * @param array          $options
     */
    public function __construct(
        $indexHost,
        $searchHost,
        IniConfiguration $cacheConfig,
        $charset = null,
        $options = null
    ) {
        $this->indexHost = $indexHost;
        $this->searchHost = $searchHost;
        $this->cacheConfig = $cacheConfig;
        $this->charset = $charset ?? 'UTF-8';
        $this->options = $options ?? [];
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
     * @param string $schema ????????????
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
        $iniFilename = $indexName . '.ini';
        if ($this->cacheConfig->configurationIsCached($iniFilename)) {
            return $this->cacheConfig->getCachedConfigPath($iniFilename);
        }

        return $this->generateIniConfig($indexName);
    }

    /**
     * @param string $indexName
     * @return string
     */
    public function generateIniConfig(string $indexName)
    {
        return (new IniBuilder())->generate($this->loadConfig($indexName));
    }

    /**
     * @param string $indexName
     * @param int    $limit
     * @param string $type
     * @return array
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
     * @return array
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
        return $name;
    }
}

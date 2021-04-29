<?php declare(strict_types=1);

namespace JimChen\LaravelScout\XunSearch;

use donatj\Ini\Builder as IniBuilder;
use Illuminate\Support\Str;
use JimChen\LaravelScout\XunSearch\Builders\TokenizerBuilder;
use JimChen\LaravelScout\XunSearch\Tokenizers\Results\Top;
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
     * @var TokenizerBuilder
     */
    protected $tokenizerBuilder;

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
    public function __construct($indexHost, $searchHost, TokenizerBuilder $tokenizerBuilder, $charset = 'uft-8', $options = [])
    {
        $this->indexHost = $indexHost;
        $this->searchHost = $searchHost;
        $this->tokenizerBuilder = $tokenizerBuilder;
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
     * @param string $string
     * @return string[]
     */
    public function participle(string $indexName, string $string)
    {
        if (empty($string)) {
            return [];
        }

        $topWordsCollection = $this->tokenizerBuilder
            ->withXs($this->initXunSearch($indexName))
            ->build()
            ->throughMiddleware($this->options['tokenizer']['middlewares'] ?? [])
            ->getTops($string, 5, 'n,nr,ns,nz,v,vn');

        $topWords = [];
        /** @var Top $item */
        foreach ($topWordsCollection as $item) {
            $topWords[] = $item->getWord();
        }

        return $topWords;
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
        if (isset($this->options['schema_prefix']) && $this->options['schema_prefix']) {
            return Str::after($name, $this->options['schema_prefix']);
        }

        return $name;
    }
}

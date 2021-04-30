<?php

namespace JimChen\LaravelScout\XunSearch;

use Illuminate\Cache\NullStore;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Psr\SimpleCache\CacheInterface;

class SchemaCache implements CacheInterface
{
    /** @var bool */
    private $enabled;

    /** @var string|null */
    private $store;

    /** @var string|null */
    private $prefix;

    public function __construct(
        bool $enabled,
        ?string $store,
        ?string $prefix
    ) {
        $this->enabled = $enabled;
        $this->store = $store;
        $this->prefix = $prefix;
    }

    public function get($key, $default = null)
    {
        return $this->resolveStore()->get($this->resolveCacheKey($key), $default);
    }

    public function getMultiple($keys, $default = null)
    {
        $_keys = [];
        foreach ($keys as $key) {
            $_keys[] = $this->resolveCacheKey($key);
        }

        return collect($this->resolveStore()->getMultiple($_keys, $default))
            ->keyBy(function ($value, $key) {
                return $this->removePrefixCacheKey($key);
            })
            ->all();
    }

    public function has($key)
    {
        return $this->resolveStore()->has($this->resolveCacheKey($key));
    }

    public function set($key, $value, $ttl = null)
    {
        if (!$this->enabled) {
            return true;
        }

        return $this->resolveStore()->set($this->resolveCacheKey($key), $value, $ttl);
    }

    public function delete($key)
    {
        return $this->resolveStore()->delete($this->resolveCacheKey($key));
    }

    public function clear()
    {
        return $this->resolveStore()->clear();
    }

    public function setMultiple($values, $ttl = null)
    {
        if (!$this->enabled) {
            return true;
        }

        $_values = [];
        foreach ($values as $key => $value) {
            $_values[$this->resolveCacheKey($key)] = $value;
        }

        return $this->resolveStore()->setMultiple($_values, $ttl);
    }

    public function deleteMultiple($keys)
    {
        $_keys = [];
        foreach ($keys as $key) {
            $_keys[] = $this->resolveCacheKey($key);
        }

        return $this->resolveStore()->deleteMultiple($_keys);
    }

    private function resolveStore()
    {
        if ($this->enabled) {
            return Cache::store($this->store);
        }

        return Cache::repository(new NullStore());
    }

    private function resolveCacheKey(string $key): string
    {
        $prefix = $this->prefix ? "{$this->prefix}." : '';

        return "{$prefix}{$key}";
    }

    private function removePrefixCacheKey(string $key): string
    {
        $prefix = $this->prefix ? "{$this->prefix}." : '';

        if (!empty($prefix)) {
            return Str::after($key, $prefix);
        }

        return $key;
    }
}

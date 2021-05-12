<?php declare(strict_types=1);

namespace JimChen\LaravelScout\XunSearch;

class IniConfiguration
{
    protected $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
    }

    public function configurationIsCached($path = '')
    {
        return file_exists($this->getCachedConfigPath($path));
    }

    public function getCachedConfigPath($path = '')
    {
        return $this->basePath . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

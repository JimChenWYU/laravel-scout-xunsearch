<?php declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

define('TEST_PATH', __DIR__);

function test_ini_path($path = '')
{
    return TEST_PATH . '/_ini' . ($path ? DIRECTORY_SEPARATOR . $path : $path);
}

function test_cache_path($path = '')
{
    return TEST_PATH . '/_cache' . ($path ? DIRECTORY_SEPARATOR . $path : $path);
}

<?php declare(strict_types=1);

namespace JimChen\LaravelScout\XunSearch;

use Illuminate\Support\ServiceProvider;
use JimChen\LaravelScout\XunSearch\Console\ConfigCacheCommand;
use JimChen\LaravelScout\XunSearch\Console\ConfigClearCommand;
use JimChen\LaravelScout\XunSearch\Engines\XunSearchEngine;
use Laravel\Scout\EngineManager;

class XunSearchScoutServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/scout.php', 'scout');

        $this->app->bind(IniConfiguration::class, function ($app) {
            return new IniConfiguration($app['config']->get('scout.xunsearch.storage.cache.path'));
        });

        $this->app->singleton(XunSearchClient::class, function ($app) {
            return new XunSearchClient(
                $app['config']->get('scout.xunsearch.index'),
                $app['config']->get('scout.xunsearch.search'),
                $app[IniConfiguration::class],
                $app['config']->get('scout.xunsearch.charset'),
                [
                    'schemas' => $app['config']->get('scout.xunsearch.storage.schema')
                ]
            );
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->make(EngineManager::class)->extend('xunsearch', function ($app) {
            return new XunSearchEngine($app->make(XunSearchClient::class), $app['config']->get('scout.soft_delete'));
        });

        $this->commands([
            ConfigCacheCommand::class,
            ConfigClearCommand::class,
        ]);
    }
}

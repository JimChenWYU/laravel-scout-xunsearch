<?php declare(strict_types=1);

namespace JimChen\LaravelScout\XunSearch;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use JimChen\LaravelScout\XunSearch\Engines\XunSearchEngine;
use Laravel\Scout\EngineManager;

class XunSearchScoutServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/scout.php', 'scout');
        $this->app->bind(XunSearchClient::class, function ($app) {
            return new XunSearchClient(
                $app['config']->get('scout.xunsearch.index'),
                $app['config']->get('scout.xunsearch.search'),
                $app['config']->get('scout.xunsearch.charset'),
                array_merge($app['config']->get('scout.xunsearch.options'), [
                    'schema_prefix' => $app['config']->get(
                        'scout.xunsearch.options.schema_prefix',
                        $app['config']->get('scout.prefix')
                    )
                ])
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
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            EngineManager::class,
        ];
    }
}

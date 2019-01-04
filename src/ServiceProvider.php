<?php

namespace Tomato\PDFMerger;

use Illuminate\Support\Facades\Config;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(PDFMerger::class, function () {
            return new PDFMerger($this->app);
        });

        $this->app->alias(PDFMerger::class, 'TomatoPDFMerger');

        $this->mergeConfigFrom(
            __DIR__.'/config/pdfmerger.php', 'pdfmerger'
        );
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/pdfmerger.php' => config_path('pdfmerger.php'),
        ]);

    }

    /**
     * Get the active router.
     *
     * @return Router
     */
    protected function getRouter()
    {
        return $this->app['router'];
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['TomatoPDFMerger', PDFMerger::class];
    }
}
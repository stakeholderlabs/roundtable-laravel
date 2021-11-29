<?php

namespace Shl\RoundTable\Providers;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function boot()
    {
        $this->registerTranslations();

        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/roundtable.php', 'roundtable');
    }


    protected function bootForConsole()
    {
        $this->publishes([
            __DIR__ . '/../../config/roundtable.php' => config_path('roundtable.php'),
        ], 'config');
    }

    public function registerTranslations(): self
    {
        $this->loadJsonTranslationsFrom(realpath(__DIR__ . '/../../resources/lang/'));

        return $this;
    }
}

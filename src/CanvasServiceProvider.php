<?php

namespace Canvas;

use Canvas\Traits\ServiceBindings;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class CanvasServiceProvider extends ServiceProvider
{
    use ServiceBindings;

    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerRoutes();
        $this->registerMigrations();
        $this->registerPublishing();
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'canvas');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang/', 'canvas');
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/canvas.php', 'canvas');
        $this->commands([
            Console\InstallCommand::class,
        ]);

        $this->registerServices();
    }

    /**
     * Register the routes.
     *
     * @return void
     */
    protected function registerRoutes()
    {
        Route::group(['namespace' => 'Canvas\Http\Controllers'], function () {
            Route::group(['middleware' => 'web', 'prefix' => config('canvas.public_path')], function () {
                $this->loadRoutesFrom(__DIR__ . '/../routes/blog.php');
            });

            Route::group(['middleware' => config('canvas.middleware'), 'prefix' => 'canvas'], function () {
                $this->loadRoutesFrom(__DIR__ . '/../routes/canvas.php');
            });
        });
    }

    /**
     * Register the resources.
     *
     * @return void
     */
    protected function registerResources()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'canvas');
    }

    /**
     * Register services in the container.
     *
     * @return void
     */
    protected function registerServices()
    {
        foreach ($this->serviceBindings as $key => $value) {
            is_numeric($key)
                ? $this->app->singleton($value)
                : $this->app->singleton($key, $value);
        }
    }

    /**
     * Register the package's migrations.
     *
     * @return void
     */
    private function registerMigrations()
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    private function registerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../public' => public_path('vendor/canvas'),
            ], 'assets');
            $this->publishes([
                __DIR__ . '/../config/canvas.php' => config_path('canvas.php'),
            ], 'config');
            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/canvas'),
            ], 'views');
        }
    }
}

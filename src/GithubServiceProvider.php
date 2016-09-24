<?php

namespace Nahid\GithubClient;

use Illuminate\Container\Container;
use Illuminate\Foundation\Application as LaravelApplication;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application as LumenApplication;
class GithubServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->setupConfig();
    }
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerGithub();

    }
    /**
     * Setup the config.
     */
    protected function setupConfig()
    {
        $source = realpath(__DIR__ . '/../config/github.php');
        // Check if the application is a Laravel OR Lumen instance to properly merge the configuration file.
        if ($this->app instanceof LaravelApplication && $this->app->runningInConsole()) {
            $this->publishes([$source => config_path('github.php')]);
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('github');
        }
        $this->mergeConfigFrom($source, 'github');
    }

    /**
     * Register Talk class.
     *
     * @return void
     */
    protected function registerGithub()
    {
        $this->app->singleton('Github', function (Container $app) {
            return new StackApi($app['config']->get('github'));
        });
    }
    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return [
            Github::class
        ];
    }
}

<?php


namespace Friendsmore\LaravelWeAppSubscribeNotification;


use Friendsmore\LaravelWeAppSubscribeNotification\Commands\DropSubscribeCommand;
use Friendsmore\LaravelWeAppSubscribeNotification\Commands\SubscribeTableCommand;
use Friendsmore\laravelWeAppSubscribeNotification\Commands\UpdateSubscribeCommand;
use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Application as LaravelApplication;

class SubscribeServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->setupConfig();
        if (version_compare($this->app->version(), '5.1', ">=") or starts_with($this->app->version(), "Lumen")) {

            if ($this->app->runningInConsole()) {
                $this->commands([
                    DropSubscribeCommand::class,
                    SubscribeTableCommand::class,
                    UpdateSubscribeCommand::class,
                ]);
            }
        }
    }

    public function boot()
    {
        //
    }

    /**
     * Setup the config.
     */
    protected function setupConfig()
    {
        $source = realpath(__DIR__.'/config/wechat_subscribe_template.php');

        if ($this->app instanceof LaravelApplication && $this->app->runningInConsole()) {
            $this->publishes([$source => config_path('wechat_subscribe_template.php')], 'wechat_subscribe_template');
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('wechat_subscribe_template');
        }

        $this->mergeConfigFrom($source, 'wechat_subscribe_template');
    }
}
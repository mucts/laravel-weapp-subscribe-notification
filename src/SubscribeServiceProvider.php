<?php


namespace Friendsmore\LaravelWeAppSubscribeNotification;


use Friendsmore\LaravelWeAppSubscribeNotification\Commands\DropSubscribeCommand;
use Friendsmore\LaravelWeAppSubscribeNotification\Commands\SubscribeTableCommand;
use Friendsmore\LaravelWeAppSubscribeNotification\Commands\UpdateSubscribeCommand;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class SubscribeServiceProvider extends ServiceProvider
{
    public function register()
    {
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
        $this->mergeConfigFrom(
            dirname(__FILE__) . '/config/wechat_subscribe_template.php', 'wechat_subscribe_template'
        );

        $this->publishes([
            dirname(__FILE__) . '/config/' => config_path(),
        ], "wechat_subscribe_template.config");

        // Auto configuration with lumen framework.

        if (Str::contains($this->app->version(), 'Lumen')) {
            $this->app->configure("wechat_subscribe_template");
        }
    }
}
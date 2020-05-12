<?php


namespace MuCTS\Laravel\WeAppSubscribeNotification;


use Illuminate\Contracts\Support\DeferrableProvider;
use MuCTS\Laravel\WeAppSubscribeNotification\Commands\DropSubscribeCommand;
use MuCTS\Laravel\WeAppSubscribeNotification\Commands\UpdateSubscribeCommand;
use Illuminate\Support\ServiceProvider;

class SubscribeServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/config/wechat_subscribe_template.php', 'wechat_subscribe_template'
        );
        $this->publishes([
            dirname(__DIR__) . '/migrations/' => database_path('migrations'),
        ], 'migrations');
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(dirname(__DIR__) . '/migrations/');
            $this->commands([
                DropSubscribeCommand::class,
                UpdateSubscribeCommand::class,
            ]);
        }
    }

    public function boot()
    {
        if (!file_exists(config_path('wechat_subscribe_template.php'))) {
            $this->publishes([
                dirname(__DIR__) . '/config/wechat_subscribe_template.php' => config_path('wechat_subscribe_template.php'),
            ], 'config');
        }
    }
}
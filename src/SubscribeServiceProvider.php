<?php


namespace Friendsmore\LaravelWeAppSubscribeNotification;


use Friendsmore\LaravelCursorPagination\ServiceProvider;
use Friendsmore\LaravelWeAppSubscribeNotification\Commands\DropSubscribeCommand;
use Friendsmore\LaravelWeAppSubscribeNotification\Commands\SubscribeTableCommand;

class SubscribeServiceProvider extends ServiceProvider
{
    public function register()
    {
        if (version_compare($this->app->version(), '5.1', ">=") or starts_with($this->app->version(), "Lumen")) {

            if ($this->app->runningInConsole()) {
                $this->commands([
                    DropSubscribeCommand::class,
                    SubscribeTableCommand::class,
                ]);
            }
        }
    }
}
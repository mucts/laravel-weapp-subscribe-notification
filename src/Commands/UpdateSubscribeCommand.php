<?php


namespace Friendsmore\LaravelWeAppSubscribeNotification\Commands;


use Carbon\Carbon;
use Friendsmore\LaravelWeAppSubscribeNotification\SubscribeTemple;
use Illuminate\Console\Command;

class UpdateSubscribeCommand extends Command
{
    protected $signature = 'weapp:subscribe:update';

    protected $description = 'Update wechat app subscribe template information';

    public function handle()
    {
        info('Update wechat app subscribe template information At ' . Carbon::now()->toDateTimeString());
        collect(config('wechat.mini_program'))->each(function ($value, $key) {
            $appId = $value['app_id'];
            info('Start app id:' . $appId);
            $configs = config('wechat_subscribe_template.' . $key, []);
            $bar = $this->output->createProgressBar(count($configs));
            collect($configs)->each(function ($item) use ($appId, $bar) {
                $subscribeTmpl = (new SubscribeTemple())
                    ->setAppId($appId)
                    ->setKeywords($item['keywords'])
                    ->setTid($item['tid'])
                    ->setScenes($item['scenes']);
                if (isset($item['type']) && !is_null($item['type'])) {
                    $subscribeTmpl->setType($item['type']);
                }
                if (isset($item['name']) && !is_null($item['name'])) {
                    $subscribeTmpl->setName($item['name']);
                }
                $subscribeTmpl->updateOrCreate();
                $bar->advance();
            });
            $this->info('');
            $bar->finish();
        });
        info('Update wechat app subscribe template information successfully At ' . Carbon::now()->toDateTimeString());
    }
}
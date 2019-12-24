<?php


namespace Friendsmore\LaravelWeAppSubscribeNotification\Commands;


use Carbon\Carbon;
use Friendsmore\LaravelWeAppSubscribeNotification\Models\WeAppSubscribeNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Overtrue\LaravelWeChat\Facade as EasyWeChat;

class DropSubscribeCommand extends Command
{
    protected $signature = 'weapp:subscribe:drop';
    protected $description = 'Destroy all WeApp subscribe information ';

    public function handle()
    {
        $this->info('Destroy all WeApp subscribe information At ' . Carbon::now()->toDateTimeString());
        if (Schema::hasTable('weapp_subscribe_notifications')) {
            DB::transaction(function () {
                collect(config('wechat.mini_program'))->each(function ($value, $key) {
                    $miniProgram = EasyWeChat::miniProgram($key);
                    $this->info('app id:' . $value['app_id']);
                    $res = $miniProgram->subscribe_message->getTemplates();
                    if (isset($res['errcode']) && $res['errcode'] != 0 || count($res['data']) == 0) {
                        return;
                    }
                    $bar = $this->output->createProgressBar(count($res['data']));
                    collect($res['data'])->each(function ($item) use ($miniProgram, $bar) {
                        $res = $miniProgram->subscribe_message->deleteTemplate($item['priTmplId']);
                        if (isset($res['errcode']) && $res['errcode'] != 0) {
                            $this->error($res['errmsg']);
                        }
                        $bar->advance();
                    });
                    $this->info('');
                    $bar->finish();
                });
                // 重构表结构并删除相应标签缓存
                WeAppSubscribeNotification::truncate();
                Cache::tags(WeAppSubscribeNotification::CACHE_FOR_TAGS)->flush();
            });
        }
        $this->info('Destroy all WeApp subscribe information successfully At ' . Carbon::now()->toDateTimeString());
    }
}
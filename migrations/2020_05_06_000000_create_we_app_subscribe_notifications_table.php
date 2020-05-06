<?php
/**
 * This file is part of the mucts.com.
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 *
 * @version 1.0
 * @author herry<yuandeng@aliyun.com>
 * @copyright © 2020 MuCTS.com All Rights Reserved.
 */

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWeAppSubscribeNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('weapp_subscribe_notifications', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('微信小程序订阅消息模版 ID');
            $table->timestamps();
            $table->string('app_id', 18)->comment('小程序appid');
            $table->string('tid', 12)->comment('模板库标题ID');
            $table->string('title', 32)->nullable()->comment('模版标题');
            $table->unsignedTinyInteger('type')->comment('模版类型，2 one_time 为一次性订阅|3 long_term 为长期订阅');
            $table->string('pri_tmpl_id', 64)->index('idx_pri_tmpl_id')->comment('订阅模版ID');
            $table->string('hash')->index('idx_hash')->comment('模板标识(模板标题ID与模板关键词列表MD5产生)');
            $table->json('content')->comment('模版内容，格式:[{"kid":"2","name":"会议时间","rule":"date"}]');
            $table->json('scenes')->comment('场景，格式：["order","refund","group_order"]');
            $table->unique(['app_id', 'hash'],'idx_unique_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('weapp_subscribe_notifications');
    }
}
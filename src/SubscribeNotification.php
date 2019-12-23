<?php


namespace Friendsmore\LaravelWeAppSubscribeNotification;


use Friendsmore\laravelWeAppSubscribeNotification\PriTmpl\PriTmpl;

interface SubscribeNotification
{
    /**
     * 获取微信App id
     *
     * @param $notifiable
     * @return string|null
     */
    public function getWeappId($notifiable): ?string;

    /**
     * 订阅消息参数设置
     *
     * @param $notifiable
     * @return SubscribeMessage|null
     */
    public function toWeAppSubscribeMessage($notifiable): ?SubscribeMessage;

    /**
     * 获取授权编号
     *
     * @param $notifiable
     * @return string
     */
    public function getSceneId($notifiable): ?string;

    /**
     * 获取场景
     *
     * @param $notifiable
     * @return string
     */
    public function getScene($notifiable): string;

    /**
     * 获取模版信息
     *
     * @param $notifiable
     * @return PriTmpl
     */
    public function getPriTmpl($notifiable): PriTmpl;
}
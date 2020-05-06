<?php
/**
 * 订阅消息模版格式如下
 *
 *'default' => [
 *  [
 *      "tid" => '',
 *      'keywords' => [],
 *      'scenes' => [],
 *      'type' => MuCTS\LaravelWeAppSubscribeNotification\Models\WeAppSubscribeNotifications::TYPES[2],
 *      'name' => ''
 *  ]
 *]
 */
return [
    'default' => [
        [
            "tid" => '',
            'keywords' => [],
            'scenes' => [],
            'type' => MuCTS\LaravelWeAppSubscribeNotification\Models\WeAppSubscribeNotification::TYPES[2],
            'name' => ''
        ]
    ]
];
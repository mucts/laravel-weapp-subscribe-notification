# 微信模版订阅消息插件

## 环境需求

用于 Lumen 框架的微信小程序订阅消息服务通知

1. PHP >= 7.1.3
2. OpenSSL PHP Extension
3. PDO PHP Extension
4. Mbstring PHP Extension
5. Redis >= 3.2.0
6. MySQL >= 5.7.5
7. laravel/framework  ^7.0
9. overtrue/laravel-wechat  ~5.0
11. predis/predis ^1.1

## 安装

```shell
composer require MuCTS/laravel-weapp-subscribe-notification
```

### Laravel 配置方法

由于设置了 Laravel providers 自动加载，所以不需要额外操作。

### Lumen 配置方法

在 `bootstrap/app.php` 中增加：
```php
$app->register(MuCTS\LaravelWeAppSubscribeNotification\SubscribeServiceProvider::class);
```

### 模版配置文件

文件名称：`config/wechat_subscribe_template.php`

文件格式：
```php
<?php
return [
    'default' => [
        [
            "tid" => '',// 订阅消息模版库标题ID
            'keywords' => [],// 模版关键词
            'scenes' => [],// 场景
            'type' => MuCTS\LaravelWeAppSubscribeNotification\Models\WeAppSubscribeNotification::TYPES[2],// 消息类型
            'name' => ''// 模版标题
        ]
    ]
]
?>
```

### 清空模版

```shell
php artisan weapp:subscribe:drop
```

### 更新模版

```shell
php artisan weapp:subscribe:update
```

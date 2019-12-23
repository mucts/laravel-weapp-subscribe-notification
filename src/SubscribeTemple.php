<?php


namespace Friendsmore\laravelWeAppSubscribeNotification;


use Friendsmore\LaravelWeAppSubscribeNotification\Models\WeAppSubscribeNotifications;
use Illuminate\Support\Str;

class SubscribeTemple
{
    /** @var string|null */
    private $tid;
    /** @var array|null */
    private $keywords;
    /** @var string|null */
    private $appId;
    /** @var array|null */
    private $scenes;

    public function setTid(string $tid): SubscribeTemple
    {
        $this->tid = $tid;
        return $this;
    }

    public function getTid(): ?string
    {
        return $this->tid;
    }

    public function setKeywords(array $keywords): SubscribeTemple
    {
        $this->keywords = $keywords;
        return $this;
    }

    public function getKeywords(): ?array
    {
        return $this->keywords;
    }

    public function setAppId(string $appId): SubscribeTemple
    {
        $this->appId = $appId;
        return $this;
    }

    public function getAppId(): ?string
    {
        return $this->appId;
    }

    public function setScenes(?array $scenes): SubscribeTemple
    {
        $this->scenes = collect($scenes)->map(function ($scenes) {
            return Str::snake(basename($scenes));
        });
        return $this;
    }

    public function getScenes(): ?array
    {
        return $this->scenes;
    }


    public function setMessage(SubscribeMessage $subscribeMessage): SubscribeTemple
    {
        $this->setTid($subscribeMessage->getTid())
            ->setKeywords($subscribeMessage->getKeywords())
            ->setScenes($subscribeMessage->getScenes());
        return $this;
    }

    public function getHash()
    {
        return json_encode(json_encode([$this->getAppId(), $this->getTid(), $this->getKeywords()], JSON_UNESCAPED_UNICODE));
    }

    public function updateOrCreate()
    {
        return WeAppSubscribeNotifications::updateOrCreatePriTmpl($this);
    }

    public function getPriTemp()
    {
        return WeAppSubscribeNotifications::getPriTmpl($this->getHash());
    }
}
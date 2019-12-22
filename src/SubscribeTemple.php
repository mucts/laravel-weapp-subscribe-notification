<?php


namespace Friendsmore\laravelWeAppSubscribeNotification;


use Friendsmore\LaravelWeAppSubscribeNotification\Models\WeAppSubscribeNotifications;

class SubscribeTemple
{
    /** @var string|null */
    private $tid;
    /** @var array|null */
    private $keywords;
    /** @var string|null */
    private $appId;
    /** @var string */
    private $sceneDesc;

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

    public function setSceneDesc(string $sceneDesc): SubscribeTemple
    {
        $this->sceneDesc = $sceneDesc;
        return $this;
    }

    public function getSceneDesc(): ?string
    {
        return $this->sceneDesc;
    }


    public function setMessage(SubscribeMessage $subscribeMessage): SubscribeTemple
    {
        $this->setTid($subscribeMessage->getTid())
            ->setKeywords($subscribeMessage->getKeywords())
            ->setSceneDesc($subscribeMessage->getSceneDesc());
        return $this;
    }

    public function getPriTemp()
    {
        return WeAppSubscribeNotifications::getPriTmpl($this->getAppId(), $this->getTid(), $this->getKeywords(), $this->getSceneDesc());
    }
}
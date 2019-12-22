<?php


namespace Friendsmore\LaravelWeAppSubscribeNotification;


use Friendsmore\laravelWeAppSubscribeNotification\PriTmpl\PriKidInfo;
use Friendsmore\laravelWeAppSubscribeNotification\PriTmpl\PriTmpl;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

class SubscribeMessage implements Arrayable
{
    /** @var string */
    private $tId;
    /** @var PriTmpl|null */
    private $priTmpl;
    /** @var array */
    private $datum;
    /** @var string|null */
    private $page;
    /** @var string */
    private $toUser;
    /** @var array|null */
    private $rateLimit;
    /** @var string */
    private $driver;
    /** @var string */
    private $sceneDesc;
    /** @var string */

    const DRIVER_WE_APP = 'weapp';
    const DRIVER_WE_APPS = 'weapps';

    public function __construct()
    {
        $this->setRouteNotificationFor(self::DRIVER_WE_APP);
    }


    /**
     * set the notification routing information for the given driver.
     * @param string $driver
     * @return SubscribeMessage
     */
    public function setRouteNotificationFor(string $driver): SubscribeMessage
    {
        $this->driver = $driver;
        return $this;
    }

    /**
     * Get the notification routing information for the given driver.
     *
     * @return string
     */
    public function getRouteNotificationFor(): string
    {
        return $this->driver;
    }

    /**
     * Set subscription template library Title ID
     *
     * @param string $tid
     * @return SubscribeMessage
     */
    public function setTid(string $tid): SubscribeMessage
    {
        $this->tId = $tid;
        return $this;
    }

    /**
     * Set subscription template library Title ID
     *
     * @return string|null
     */
    public function getTid(): ?string
    {
        return $this->tId;
    }


    /**
     * Get subscribe template keywords
     *
     * @return array|null
     */
    public function getKeywords(): ?array
    {
        return array_keys($this->datum);
    }

    /**
     * Set subscribe message keywords data
     *
     * @param array $datum
     * @return SubscribeMessage
     */
    public function setDatum(array $datum): SubscribeMessage
    {
        $this->datum = $datum;
        return $this;
    }

    /**
     * Get subscribe message keywords data
     *
     * @return array|null
     */
    public function getDatum(): ?array
    {
        if (is_null($this->priTmpl)) {
            return null;
        }
        return $this->priTmpl
            ->getPriTmplKeywords()
            ->getContent()
            ->map(function (PriKidInfo $info) {
                return [
                    'key' => $info->getKey(),
                    'data' => ['value' => $this->getData($info->getName())]
                ];
            })
            ->pluck('data', 'key')
            ->all();
    }

    /**
     * get subscribe message keyword data
     *
     * @param string $key
     * @return string|null
     */
    private function getData(string $key): ?string
    {
        return isset($this->datum[$key]) ? $this->datum[$key] : null;
    }

    /**
     * Set Subscribe Message template info
     *
     * @param PriTmpl $tmpl
     * @return SubscribeMessage
     */
    public function setPriTmpl(PriTmpl $tmpl): SubscribeMessage
    {
        $this->priTmpl = $tmpl;
        return $this;
    }

    /**
     * Get Subscribe template id
     *
     * @return string|null
     */
    public function getPriTmplId(): ?string
    {
        return $this->priTmpl ? $this->priTmpl->getPriTmplId() : null;
    }

    /**
     * Set subscribe scene desc
     *
     * @param string $sceneDesc
     * @return SubscribeMessage
     */
    public function setSceneDesc(string $sceneDesc): SubscribeMessage
    {
        $this->sceneDesc = $sceneDesc;
        return $this;
    }

    /**
     * Get subscribe scene desc
     *
     * @return string|null
     */
    public function getSceneDesc(): ?string
    {
        return $this->sceneDesc;
    }

    /**
     * Set receiver Open Id
     *
     * @param string $openId
     * @return SubscribeMessage
     */
    public function setToUser(string $openId): SubscribeMessage
    {
        $this->toUser = $openId;
        return $this;
    }

    /**
     * Get receiver Open Id
     *
     * @return string|null
     */
    public function getToUser(): ?string
    {
        return $this->toUser;
    }

    /**
     * Set we app page url
     *
     * @param string|null $page
     * @return SubscribeMessage
     */
    public function setPage(?string $page): SubscribeMessage
    {
        $this->page = $page;
        return $this;
    }

    /**
     * Get we app page url
     *
     * @return string|null
     */
    public function getPage(): ?string
    {
        return $this->page;
    }

    /**
     * （可选）设置发送频率 (一个接收方对于同一个 key 发送时间间隔不能少于的秒数)
     *
     * @param int $tts
     * @param string $key
     * @return $this
     */
    public function setRateLimit(int $tts, ?string $key = null)
    {
        $this->rateLimit = [
            'tts' => $tts,
            'key' => $key
        ];
        return $this;
    }

    /**
     * 获取 发送频率
     * @param string|null $key
     * @return mixed
     */
    public function getRateLimit(?string $key = null)
    {
        $rateLimit = $this->rateLimit;
        $rateLimit = is_null($rateLimit) ? null : collect($rateLimit)->map(function ($value, $key) {
            return $key == 'key' ? sprintf('WX_RATE_LIMIT:%s', md5(json_encode($this->toArray() + ['key' => $value]))) : $value;
        })->toArray();
        return is_null($key) ? $rateLimit : (is_null($rateLimit) ? null : Arr::get($rateLimit, $key));
    }


    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return [
            'touser' => $this->getToUser(),
            'template_id' => $this->getPriTmplId(),
            'page' => $this->getPage(),
            'data' => $this->getDatum()
        ];
    }
}
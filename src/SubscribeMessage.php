<?php


namespace MuCTS\LaravelWeAppSubscribeNotification;


use MuCTS\LaravelWeAppSubscribeNotification\PriTmpl\PriKidInfo;
use MuCTS\LaravelWeAppSubscribeNotification\PriTmpl\PriTmpl;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

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
                    'data' => ['value' => $this->getData($info->getName(), $info->getRule())]
                ];
            })
            ->pluck('data', 'key')
            ->all();
    }


    /**
     * get subscribe message keyword data
     *
     * @param string $key
     * @param string $rule
     * @return string|null
     */
    private function getData(string $key, string $rule): ?string
    {
        $value = isset($this->datum[$key]) ? $this->datum[$key] : null;
        if (is_null($value)) {
            return null;
        }
        switch ($rule) {
            // 20个以内字符 	可汉字、数字、字母或符号组合
            case 'thing':
                if (mb_strlen($value, 'UTF-8') <= 20) {
                    return $value;
                }
                return mb_substr($value, 0, 17, 'UTF-8') . '...';
            // 32位以内字母 	只能字母
            case 'letter':
                return Str::limit($value, 32, '');
            // 32位以内数字、字母或符号 	可数字、字母或符号组合
            case 'character_string':
                if (strlen($value) <= 32) {
                    return $value;
                }
                return Str::limit($value, 29);
            // 5位以内符号 	只能符号
            case 'phrase':
                if (mb_strlen($value, 'UTF-8') <= 5) {
                    return $value;
                }
                return mb_substr($value, 0, 5, 'UTF-8');
            case 'symbol':
                return Str::limit($value, 5, '');
            // 17位以内，数字、符号 	电话号码，例：+86-0766-66888866
            case 'phone_number':
                return Str::limit($value, 17, '');
            // 8位以内，第一位与最后一位可为汉字，其余为字母或数字 	车牌号码：粤A8Z888挂
            case 'car_number':
                return mb_substr($value, 0, 8, 'UTF-8');
            // 10个以内纯汉字或20个以内纯字母或符号 	中文名10个汉字内；纯英文名20个字母内；中文和字母混合按中文名算，10个字内
            case 'name':
                if (preg_match('/^[a-zA-Z]$/', $value)) {
                    if (strlen($value) <= 20) {
                        return $value;
                    }
                    return Str::limit($value, 17);
                }
                if (mb_strlen($value, 'UTF-8') <= 10) {
                    return $value;
                }
                return mb_substr($value, 0, 10, 'UTF-8');
            // 5个以内汉字 	5个以内纯汉字，例如：配送中
            default:
                return $value;
        }
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
<?php


namespace Friendsmore\laravelWeAppSubscribeNotification\PriTmpl;


use Illuminate\Contracts\Support\Arrayable;

class PriKidInfo implements Arrayable
{
    /** @var string */
    private $kid;
    /** @var string */
    private $name;
    /** @var string */
    private $rule;

    /**
     * Set Kid
     *
     * @param string $kid
     * @return PriKidInfo
     */
    public function setKid(string $kid): PriKidInfo
    {
        $this->kid = $kid;
        return $this;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return PriKidInfo
     */
    public function setName(string $name): PriKidInfo
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    public function setRule(string $rule): PriKidInfo
    {
        $this->rule = $rule;
        return $this;
    }

    /**
     * get rule
     *
     * @return string|null
     */
    public function getRule(): ?string
    {
        return $this->rule;
    }

    /**
     * Get Kid
     *
     * @return string|null
     */
    public function getKid(): ?string
    {
        return $this->kid;
    }

    /**
     * get key
     *
     * @return string|null
     */
    public function getKey(): ?string
    {
        if (is_null($this->getKid()) || is_null($this->getRule())) {
            return null;
        }
        return $this->getRule() . $this->getKid();
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return [
            'kid' => $this->getKid(),
            'name' => $this->getName(),
            'rule' => $this->getRule()
        ];
    }
}
<?php


namespace Friendsmore\laravelWeAppSubscribeNotification\PriTmpl;


class PriTmpl
{
    /** @var string */
    private $priTmplId;
    /** @var PriTmplKeywords */
    private $priTmplKeywords;

    public function __construct(?string $priTmplId = null, ?PriTmplKeywords $priTmplKeywords = null)
    {
        $this->priTmplKeywords = $priTmplKeywords;
        $this->priTmplId = $priTmplId;
    }

    public function setPriTmplId(string $priTmlId): PriTmpl
    {
        $this->priTmplId = $priTmlId;
        return $this;
    }

    public function setPriTmplKeywords(PriTmplKeywords $priTmplKeywords): PriTmpl
    {
        $this->priTmplKeywords = $priTmplKeywords;
        return $this;
    }

    public function getPriTmplKeywords(): ?PriTmplKeywords
    {
        return $this->priTmplKeywords;
    }

    public function getPriTmplId(): ?string
    {
        return $this->priTmplId;
    }
}
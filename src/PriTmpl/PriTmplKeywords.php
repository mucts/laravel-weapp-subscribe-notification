<?php


namespace MuCTS\Laravel\WeAppSubscribeNotification\PriTmpl;


use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

class PriTmplKeywords implements Arrayable
{
    /** @var string */
    private $tid;
    /** @var int */
    private $type;
    /** @var Collection */
    private $content;
    /** @var array */
    private $keywords;
    /** @var string */
    private $name;

    public function __construct(string $tid, string $type, ?string $name, array $content)
    {
        $this->tid = $tid;
        $this->type = $type;
        $this->name = $name;
        $this->content = collect($content)->map(function ($item) {
            return (new PriKidInfo())
                ->setKid($item['kid'])
                ->setName($item['name'])
                ->setRule($item['rule']);
        });
        $this->setKeywords(collect($content)->pluck('name')->all());
    }

    /**
     * Set keywords
     *
     * @param array $keywords
     * @return $this
     */
    public function setKeywords(array $keywords)
    {
        $this->keywords = $keywords;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }

    /**
     * Get kid
     *
     * @return array
     */
    public function getKids(): array
    {
        return $this->content->filter(function (PriKidInfo $kidInfo) {
            return in_array($kidInfo->getName(), $this->keywords);
        })->sortBy(function (PriKidInfo $kidInfo) {
            return array_search($kidInfo->getName(), $this->keywords);
        })->map(function (PriKidInfo $kidInfo) {
            return $kidInfo->getKid();
        })->toArray();
    }

    /**
     * Get Content
     * @return Collection
     */
    public function getContent(): Collection
    {
        return $this->content->filter(function (PriKidInfo $kidInfo) {
            return in_array($kidInfo->getName(), $this->keywords);
        })->sortBy(function (PriKidInfo $kidInfo) {
            return array_search($kidInfo->getName(), $this->keywords);
        });
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return [
            'tid' => $this->tid,
            'type' => $this->type,
            'content' => $this->content->toArray(),
        ];
    }
}
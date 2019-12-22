<?php


namespace Friendsmore\laravelWeAppSubscribeNotification;


use Carbon\Carbon;
use Friendsmore\LaravelBase\JsonResource;
use Illuminate\Notifications\RoutesNotifications;
use Illuminate\Support\Collection;

class SubscribeResource extends JsonResource
{
    private $subscribe_notifications_auth = [];
    /** @var RoutesNotifications */
    private $notifiable;
    private $expiryTime;

    public function setSubscribeNotifications(array $notifications): SubscribeResource
    {
        $this->subscribe_notifications_auth = $notifications;
        return $this;
    }

    public function setSubscribeNotification(string $notification): SubscribeResource
    {
        array_push($this->subscribe_notifications_auth, $notification);
        return $this;
    }

    public function setSubscribeNotifiable(RoutesNotifications $notifiable): SubscribeResource
    {
        $this->notifiable = $notifiable;
        return $this;
    }

    public function getSubscribeNotifiable(): ?RoutesNotifications
    {
        return $this->notifiable;
    }

    public function getSubscribeNotifications(): Collection
    {
        return collect($this->subscribe_notifications_auth)->map(function ($class) {
            $class = new $class($this->resource);
            if ($class instanceof SubscribeNotification) {
                return $class;
            }
            return null;
        })->filter();
    }

    public function setSubscribeExpiryTime(?Carbon $expiryTime): ?SubscribeResource
    {
        $this->expiryTime = $expiryTime;
        return $this;
    }

    private function getSubscribeExpiryTime(): ?Carbon
    {
        return $this->expiryTime;
    }

    /**
     * @param $data
     * @param $request
     * @return mixed
     */
    public function subscribeResolveExpands($data, $request)
    {
        $priTemplates = $this->getSubscribeNotifications();
        if ($priTemplates->isNotEmpty()) {
            $priTemplates = SubscribeAuthorize::getSubscribeTmpl($this->getSubscribeNotifiable(), $priTemplates, $this->getSubscribeExpiryTime());
            $data += $priTemplates ? ['subscribe_templates' => $priTemplates] : [];
        }
        return $data;
    }

    public function toArray($request)
    {
        return $this->subscribeResolveExpands(parent::toArray($request), $request);
    }
}
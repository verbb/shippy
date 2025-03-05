<?php
namespace verbb\shippy\events;

use verbb\shippy\carriers\CarrierInterface;
use verbb\shippy\models\Request;

class TrackingEvent extends ModelEvent
{
    // Properties
    // =========================================================================

    protected ?CarrierInterface $carrier = null;
    protected ?Request $request = null;
    protected array $data = [];


    // Public Methods
    // =========================================================================

    public function getCarrier(): ?CarrierInterface
    {
        return $this->carrier;
    }

    public function setCarrier(?CarrierInterface $carrier): TrackingEvent
    {
        $this->carrier = $carrier;
        return $this;
    }

    public function getRequest(): ?Request
    {
        return $this->request;
    }

    public function setRequest(?Request $request): TrackingEvent
    {
        $this->request = $request;
        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): TrackingEvent
    {
        $this->data = $data;
        return $this;
    }
}
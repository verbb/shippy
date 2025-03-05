<?php
namespace verbb\shippy\events;

use verbb\shippy\carriers\CarrierInterface;
use verbb\shippy\models\Request;

class RateEvent extends ModelEvent
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

    public function setCarrier(?CarrierInterface $carrier): RateEvent
    {
        $this->carrier = $carrier;
        return $this;
    }

    public function getRequest(): ?Request
    {
        return $this->request;
    }

    public function setRequest(?Request $request): RateEvent
    {
        $this->request = $request;
        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): RateEvent
    {
        $this->data = $data;
        return $this;
    }
}
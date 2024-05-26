<?php
namespace verbb\shippy\events;

use verbb\shippy\models\Request;

class RateEvent extends ModelEvent
{
    // Properties
    // =========================================================================

    protected ?Request $request = null;
    protected array $data = [];


    // Public Methods
    // =========================================================================

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
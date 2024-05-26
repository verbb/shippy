<?php
namespace verbb\shippy\models;

class TrackingResponse extends ResourceResponse
{
    // Properties
    // =========================================================================

    protected array $tracking = [];


    // Public Methods
    // =========================================================================

    public function getTracking(): array
    {
        return $this->tracking;
    }

    public function setTracking(array $tracking): TrackingResponse
    {
        $this->tracking = $tracking;
        return $this;
    }
}

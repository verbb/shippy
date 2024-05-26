<?php
namespace verbb\shippy\models;

class RateResponse extends ResourceResponse
{
    // Properties
    // =========================================================================

    protected array $rates = [];


    // Public Methods
    // =========================================================================

    public function getRates(): array
    {
        return $this->rates;
    }

    public function setRates(array $rates): RateResponse
    {
        $this->rates = $rates;
        return $this;
    }
}

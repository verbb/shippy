<?php
namespace verbb\shippy\models;

use DateTime;
use verbb\shippy\carriers\CarrierInterface;
use verbb\shippy\helpers\DateTimeHelper;

class Rate extends Model
{
    // Properties
    // =========================================================================

    protected CarrierInterface $carrier;
    protected ?string $serviceName = null;
    protected ?string $serviceCode = null;
    protected ?string $rate = null;
    protected ?string $currency = null;
    protected ?int $deliveryDays = null;
    protected ?DateTime $deliveryDate = null;
    protected ?bool $deliveryDateGuaranteed = null;
    protected array $response = [];


    // Public Methods
    // =========================================================================

    public function getCarrier(): CarrierInterface
    {
        return $this->carrier;
    }

    public function setCarrier(CarrierInterface $carrier): Rate
    {
        $this->carrier = $carrier;
        return $this;
    }

    public function getServiceName(): ?string
    {
        return $this->serviceName;
    }

    public function setServiceName(?string $serviceName): Rate
    {
        $this->serviceName = $serviceName;
        return $this;
    }

    public function getServiceCode(): ?string
    {
        return $this->serviceCode;
    }

    public function setServiceCode(?string $serviceCode): Rate
    {
        $this->serviceCode = $serviceCode;
        return $this;
    }

    public function getRate(): ?string
    {
        return $this->rate;
    }

    public function setRate(?string $rate): Rate
    {
        $this->rate = $rate;
        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(?string $currency): Rate
    {
        $this->currency = $currency;
        return $this;
    }

    public function getDeliveryDays(): ?int
    {
        return $this->deliveryDays;
    }

    public function setDeliveryDays(?int $deliveryDays): Rate
    {
        $this->deliveryDays = $deliveryDays;
        return $this;
    }

    public function getDeliveryDate(): ?DateTime
    {
        return $this->deliveryDate;
    }

    public function setDeliveryDate(DateTime|string|null $deliveryDate): Rate
    {
        $this->deliveryDate = DateTimeHelper::toDateTime($deliveryDate);
        return $this;
    }

    public function getDeliveryDateGuaranteed(): ?bool
    {
        return $this->deliveryDateGuaranteed;
    }

    public function setDeliveryDateGuaranteed(?bool $deliveryDateGuaranteed): Rate
    {
        $this->deliveryDateGuaranteed = $deliveryDateGuaranteed;
        return $this;
    }

    public function getResponse(): array
    {
        return $this->response;
    }

    public function setResponse(array $response): Rate
    {
        $this->response = $response;
        return $this;
    }

    public function toArray(): array
    {
        // Remove debug/info attributes
        $vars = parent::toArray();
        unset($vars['carrier'], $vars['response']);

        return $vars;
    }
}

<?php
namespace verbb\shippy\models;

use DateTime;
use verbb\shippy\carriers\CarrierInterface;
use verbb\shippy\helpers\DateTimeHelper;

class Tracking extends Model
{
    // Constants
    // =========================================================================

    public const STATUS_PENDING = 'pending';
    public const STATUS_PRE_TRANSIT = 'pre_transit';
    public const STATUS_IN_TRANSIT = 'in_transit';
    public const STATUS_OUT_FOR_DELIVERY = 'out_for_delivery';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_AVAILABLE_FOR_PICKUP = 'available_for_pickup';
    public const STATUS_RETURN_TO_SENDER = 'return_to_sender';
    public const STATUS_FAILURE = 'failure';
    public const STATUS_UNKNOWN = 'unknown';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_ERROR = 'error';
    public const STATUS_NOT_FOUND = 'not_found';


    // Properties
    // =========================================================================

    protected CarrierInterface $carrier;
    protected ?string $trackingNumber = null;
    protected ?string $status = null;
    protected ?string $statusDetail = null;
    protected ?DateTime $estimatedDelivery = null;
    protected ?string $signedBy = null;
    protected ?string $weight = null;
    protected ?string $weightUnit = null;
    protected array $details = [];
    protected array $errors = [];
    protected array $response = [];


    // Public Methods
    // =========================================================================

    public function getCarrier(): CarrierInterface
    {
        return $this->carrier;
    }

    public function setCarrier(CarrierInterface $carrier): Tracking
    {
        $this->carrier = $carrier;
        return $this;
    }

    public function getTrackingNumber(): ?string
    {
        return $this->trackingNumber;
    }

    public function setTrackingNumber(?string $trackingNumber): Tracking
    {
        $this->trackingNumber = $trackingNumber;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): Tracking
    {
        $this->status = $status;
        return $this;
    }

    public function getStatusDetail(): ?string
    {
        return $this->statusDetail;
    }

    public function setStatusDetail(?string $statusDetail): Tracking
    {
        $this->statusDetail = $statusDetail;
        return $this;
    }

    public function getEstimatedDelivery(): ?DateTime
    {
        return $this->estimatedDelivery;
    }

    public function setEstimatedDelivery(DateTime|string|null $estimatedDelivery): Tracking
    {
        $this->estimatedDelivery = DateTimeHelper::toDateTime($estimatedDelivery);
        return $this;
    }

    public function getSignedBy(): ?string
    {
        return $this->signedBy;
    }

    public function setSignedBy(?string $signedBy): Tracking
    {
        $this->signedBy = $signedBy;
        return $this;
    }

    public function getWeight(): ?string
    {
        return $this->weight;
    }

    public function setWeight(?string $weight): Tracking
    {
        $this->weight = $weight;
        return $this;
    }

    public function getWeightUnit(): ?string
    {
        return $this->weightUnit;
    }

    public function setWeightUnit(?string $weightUnit): Tracking
    {
        $this->weightUnit = $weightUnit;
        return $this;
    }

    public function getDetails(): array
    {
        return $this->details;
    }

    public function setDetails(array $details): Tracking
    {
        $this->details = $details;
        return $this;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function setErrors(array $errors): Tracking
    {
        $this->errors = $errors;
        return $this;
    }

    public function getResponse(): array
    {
        return $this->response;
    }

    public function setResponse(array $response): Tracking
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
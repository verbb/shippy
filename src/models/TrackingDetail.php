<?php
namespace verbb\shippy\models;

use DateTime;
use verbb\shippy\helpers\DateTimeHelper;

class TrackingDetail extends Model
{
    // Properties
    // =========================================================================

    protected ?string $description = null;
    protected ?DateTime $date = null;
    protected ?string $location = null;
    protected ?string $status = null;
    protected ?string $statusDetail = null;


    // Public Methods
    // =========================================================================

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): TrackingDetail
    {
        $this->description = $description;
        return $this;
    }

    public function getDate(): ?DateTime
    {
        return $this->date;
    }

    public function setDate(DateTime|string|null $date): TrackingDetail
    {
        $this->date = DateTimeHelper::toDateTime($date);
        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): TrackingDetail
    {
        $this->location = $location;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): TrackingDetail
    {
        $this->status = $status;
        return $this;
    }

    public function getStatusDetail(): ?string
    {
        return $this->statusDetail;
    }

    public function setStatusDetail(?string $statusDetail): TrackingDetail
    {
        $this->statusDetail = $statusDetail;
        return $this;
    }

}
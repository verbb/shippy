<?php
namespace verbb\shippy\models;

use verbb\shippy\carriers\CarrierInterface;

class Label extends Model
{
    // Constants
    // =========================================================================

    public const FORMAT_EPL2 = 'epl2';
    public const FORMAT_GIF = 'gif';
    public const FORMAT_JPG = 'jpg';
    public const FORMAT_LP2 = 'lp2';
    public const FORMAT_PDF = 'pdf';
    public const FORMAT_PNG = 'png';
    public const FORMAT_SPL = 'spl';
    public const FORMAT_SVG = 'svg';
    public const FORMAT_TIFF = 'tiff';
    public const FORMAT_ZPL = 'zpl';


    // Properties
    // =========================================================================

    protected CarrierInterface $carrier;
    protected Rate $rate;
    protected ?string $trackingNumber = null;
    protected ?string $labelId = null;
    protected ?string $labelData = null;
    protected ?string $labelMime = null;
    protected array $response = [];


    // Public Methods
    // =========================================================================

    public function getCarrier(): CarrierInterface
    {
        return $this->carrier;
    }

    public function setCarrier(CarrierInterface $carrier): Label
    {
        $this->carrier = $carrier;
        return $this;
    }

    public function getRate(): Rate
    {
        return $this->rate;
    }

    public function setRate(Rate $rate): Label
    {
        $this->rate = $rate;
        return $this;
    }

    public function getTrackingNumber(): string
    {
        return (string)$this->trackingNumber;
    }

    public function setTrackingNumber(string $trackingNumber): Label
    {
        $this->trackingNumber = $trackingNumber;
        return $this;
    }

    public function getLabelId(): string
    {
        return (string)$this->labelId;
    }

    public function setLabelId(string $labelId): Label
    {
        $this->labelId = $labelId;
        return $this;
    }

    public function getLabelData(): string
    {
        return (string)$this->labelData;
    }

    public function setLabelData(string $labelData): Label
    {
        $this->labelData = $labelData;
        return $this;
    }

    public function getLabelMime(): string
    {
        return (string)$this->labelMime;
    }

    public function setLabelMime(string $labelMime): Label
    {
        $this->labelMime = $labelMime;
        return $this;
    }

    public function getResponse(): array
    {
        return $this->response;
    }

    public function setResponse(array $response): Label
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

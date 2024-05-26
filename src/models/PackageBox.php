<?php
namespace verbb\shippy\models;

use DVDoug\BoxPacker\Box;

class PackageBox extends Model implements Box
{
    // Properties
    // =========================================================================

    protected ?string $reference = null;
    protected ?int $outerWidth = null;
    protected ?int $outerLength = null;
    protected ?int $outerDepth = null;
    protected ?int $emptyWeight = null;
    protected ?int $innerWidth = null;
    protected ?int $innerLength = null;
    protected ?int $innerDepth = null;
    protected ?int $maxWeight = null;
    protected ?string $type = null;
    protected ?float $price = null;


    // Public Methods
    // =========================================================================

    public function setDimensions($reference, $width, $length, $depth, $weight): void
    {
        $this->reference = $reference;
        $this->outerWidth = $width;
        $this->outerLength = $length;
        $this->outerDepth = $depth;
        $this->emptyWeight = 0;
        $this->innerWidth = $width;
        $this->innerLength = $length;
        $this->innerDepth = $depth;
        $this->maxWeight = $weight;
    }

    public function getReference(): string
    {
        return (string)$this->reference;
    }

    public function setReference($value): void
    {
        $this->reference = $value;
    }

    public function getOuterWidth(): int
    {
        return (int)$this->outerWidth;
    }

    public function setOuterWidth($value): void
    {
        $this->outerWidth = $value;
    }

    public function getOuterLength(): int
    {
        return (int)$this->outerLength;
    }

    public function setOuterLength($value): void
    {
        $this->outerLength = $value;
    }

    public function getOuterDepth(): int
    {
        return (int)$this->outerDepth;
    }

    public function setOuterDepth($value): void
    {
        $this->outerDepth = $value;
    }

    public function getEmptyWeight(): int
    {
        return (int)$this->emptyWeight;
    }

    public function setEmptyWeight($value): void
    {
        $this->emptyWeight = $value;
    }

    public function getInnerWidth(): int
    {
        return (int)$this->innerWidth;
    }

    public function setInnerWidth($value): void
    {
        $this->innerWidth = $value;
    }

    public function getInnerLength(): int
    {
        return (int)$this->innerLength;
    }

    public function setInnerLength($value): void
    {
        $this->innerLength = $value;
    }

    public function getInnerDepth(): int
    {
        return (int)$this->innerDepth;
    }

    public function setInnerDepth($value): void
    {
        $this->innerDepth = $value;
    }

    public function getMaxWeight(): int
    {
        return (int)$this->maxWeight;
    }

    public function setMaxWeight($value): void
    {
        $this->maxWeight = $value;
    }

    public function getType(): string
    {
        return (string)$this->type;
    }

    public function setType($value): void
    {
        $this->type = $value;
    }

    public function getPrice(): float
    {
        return (float)$this->price;
    }

    public function setPrice($value): void
    {
        $this->price = $value;
    }
}

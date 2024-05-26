<?php
namespace verbb\shippy\models;

use DVDoug\BoxPacker\Item;

class PackageItem extends Model implements Item
{
    // Properties
    // =========================================================================

    protected ?string $description = null;
    protected ?int $width = null;
    protected ?int $length = null;
    protected ?int $depth = null;
    protected ?int $weight = null;
    protected ?float $itemValue = null;
    protected bool $keepFlat = false;


    // Public Methods
    // =========================================================================

    public function setDimensions($description, $width, $length, $depth, $weight): void
    {
        $this->description = $description;
        $this->width = $width;
        $this->length = $length;
        $this->depth = $depth;
        $this->weight = $weight;
        $this->keepFlat = false;
    }

    public function getDescription(): string
    {
        return (string)$this->description;
    }

    public function setDescription($value): void
    {
        $this->description = $value;
    }

    public function getWidth(): int
    {
        return (int)$this->width;
    }

    public function setWidth($value): void
    {
        $this->width = $value;
    }

    public function getLength(): int
    {
        return (int)$this->length;
    }

    public function setLength($value): void
    {
        $this->length = $value;
    }

    public function getDepth(): int
    {
        return (int)$this->depth;
    }

    public function setDepth($value): void
    {
        $this->depth = $value;
    }

    public function getWeight(): int
    {
        return (int)$this->weight;
    }

    public function setWeight($value): void
    {
        $this->weight = $value;
    }

    public function getKeepFlat(): bool
    {
        return $this->keepFlat;
    }

    public function setKeepFlat($value): void
    {
        $this->keepFlat = $value;
    }

    public function getItemValue(): float
    {
        return (float)$this->itemValue;
    }

    public function setItemValue($value): void
    {
        $this->itemValue = $value;
    }
}

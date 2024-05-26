<?php
namespace verbb\shippy\models;

use PhpUnitsOfMeasure\PhysicalQuantity\Length;
use PhpUnitsOfMeasure\PhysicalQuantity\Mass;
use PhpUnitsOfMeasure\Exception\NonNumericValue;
use PhpUnitsOfMeasure\Exception\NonStringUnitName;

class Package extends Model
{
    // Properties
    // =========================================================================

    protected ?string $weight = null;
    protected ?string $width = null;
    protected ?string $length = null;
    protected ?string $height = null;
    protected ?string $price = null;
    protected string $weightUnit = 'g';
    protected string $dimensionUnit = 'mm';
    protected ?string $packageType = null;
    protected ?string $packagePreset = null;
    protected ?string $reference = null;
    protected ?string $description = null;
    protected ?string $content = null;
    protected bool $isDocument = false;


    // Public Methods
    // =========================================================================

    /**
     * @throws NonNumericValue
     * @throws NonStringUnitName
     */
    public function convertTo(string $weightUnit, string $dimensionUnit): Package
    {
        $weight = new Mass($this->weight, $this->weightUnit);
        $width = new Length($this->width, $this->dimensionUnit);
        $length = new Length($this->length, $this->dimensionUnit);
        $height = new Length($this->height, $this->dimensionUnit);

        return new Package([
            'weight' => $weight->toUnit($weightUnit),
            'width' => $width->toUnit($dimensionUnit),
            'length' => $length->toUnit($dimensionUnit),
            'height' => $height->toUnit($dimensionUnit),
            'price' => $this->price,
        ]);
    }

    public function getWeight(int $decimals = 2): string
    {
        return (string)round($this->weight, $decimals);
    }

    public function setWeight(string $weight): Package
    {
        $this->weight = $weight;
        return $this;
    }

    public function getWidth(int $decimals = 2): string
    {
        return (string)round($this->width, $decimals);
    }

    public function setWidth(string $width): Package
    {
        $this->width = $width;
        return $this;
    }

    public function getLength(int $decimals = 2): string
    {
        return (string)round($this->length, $decimals);
    }

    public function setLength(string $length): Package
    {
        $this->length = $length;
        return $this;
    }

    public function getHeight(int $decimals = 2): string
    {
        return (string)round($this->height, $decimals);
    }

    public function setHeight(string $height): Package
    {
        $this->height = $height;
        return $this;
    }

    public function getPrice(int $decimals = 2): string
    {
        return (string)round($this->price, $decimals);
    }

    public function setPrice(string $price): Package
    {
        $this->price = $price;
        return $this;
    }

    public function getWeightUnit(): string
    {
        return $this->weightUnit;
    }

    public function setWeightUnit(string $weightUnit): Package
    {
        $this->weightUnit = $weightUnit;
        return $this;
    }

    public function getDimensionUnit(): string
    {
        return $this->dimensionUnit;
    }

    public function setDimensionUnit(string $dimensionUnit): Package
    {
        $this->dimensionUnit = $dimensionUnit;
        return $this;
    }

    public function getPackageType(): string
    {
        return (string)$this->packageType;
    }

    public function setPackageType(string $packageType): Package
    {
        $this->packageType = $packageType;
        return $this;
    }

    public function getPackagePreset(): string
    {
        return (string)$this->packagePreset;
    }

    public function setPackagePreset(string $packagePreset): Package
    {
        $this->packagePreset = $packagePreset;
        return $this;
    }

    public function getReference(): string
    {
        return (string)$this->reference;
    }

    public function setReference(string $reference): Package
    {
        $this->reference = $reference;
        return $this;
    }

    public function getDescription(): string
    {
        return (string)$this->description;
    }

    public function setDescription(string $description): Package
    {
        $this->description = $description;
        return $this;
    }

    public function getContent(): string
    {
        return (string)$this->content;
    }

    public function setContent(string $content): Package
    {
        $this->content = $content;
        return $this;
    }

    public function isDocument(): bool
    {
        return $this->isDocument;
    }

    public function setIsDocument(bool $isDocument): Package
    {
        $this->isDocument = $isDocument;
        return $this;
    }

}
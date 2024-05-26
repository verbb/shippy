<?php
namespace verbb\shippy\models;

use CommerceGuys\Addressing\Country\CountryRepository;
use CommerceGuys\Addressing\Subdivision\SubdivisionRepository;

class Address extends Model
{
    // Properties
    // =========================================================================

    protected ?string $firstName = null;
    protected ?string $lastName = null;
    protected ?string $companyName = null;
    protected ?string $email = null;
    protected ?string $phone = null;
    protected ?string $street1 = null;
    protected ?string $street2 = null;
    protected ?string $street3 = null;
    protected ?string $city = null;
    protected ?string $postalCode = null;
    protected ?string $countryCode = null;
    protected ?string $stateProvince = null;
    protected bool $isResidential = false;


    // Public Methods
    // =========================================================================

    public function getFirstName(): string
    {
        return (string)$this->firstName;
    }

    public function setFirstName(string $firstName): Address
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): string
    {
        return (string)$this->lastName;
    }

    public function setLastName(string $lastName): Address
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getFullName(): string
    {
        return implode(' ', [$this->getFirstName(), $this->getLastName()]);
    }

    public function getCompanyName(): string
    {
        return (string)$this->companyName;
    }

    public function setCompanyName(string $companyName): Address
    {
        $this->companyName = $companyName;
        return $this;
    }

    public function getEmail(): string
    {
        return (string)$this->email;
    }

    public function setEmail(string $email): Address
    {
        $this->email = $email;
        return $this;
    }

    public function getPhone(): string
    {
        return (string)$this->phone;
    }

    public function setPhone(string $phone): Address
    {
        $this->phone = $phone;
        return $this;
    }

    public function getStreet1(): string
    {
        return (string)$this->street1;
    }

    public function setStreet1(string $street1): Address
    {
        $this->street1 = $street1;
        return $this;
    }

    public function getStreet2(): string
    {
        return (string)$this->street2;
    }

    public function setStreet2(string $street2): Address
    {
        $this->street2 = $street2;
        return $this;
    }

    public function getStreet3(): string
    {
        return (string)$this->street3;
    }

    public function setStreet3(string $street3): Address
    {
        $this->street3 = $street3;
        return $this;
    }

    public function getCity(): string
    {
        return (string)$this->city;
    }

    public function setCity(string $city): Address
    {
        $this->city = $city;
        return $this;
    }

    public function getPostalCode(): string
    {
        return (string)$this->postalCode;
    }

    public function setPostalCode(string $postalCode): Address
    {
        // Remove spaces in postal codes
        $this->postalCode = str_replace(' ', '', $postalCode);
        return $this;
    }

    public function getCountryCode(): string
    {
        return (string)$this->countryCode;
    }

    public function getCountryName(): string
    {
        $countryRepository = new CountryRepository();

        return $countryRepository->get($this->countryCode)->getName();
    }

    public function setCountryCode(string $countryCode): Address
    {
        $this->countryCode = $countryCode;
        return $this;
    }

    public function getStateProvince(): string
    {
        return (string)$this->stateProvince;
    }

    public function getStateProvinceName(): string
    {
        $subdivisionRepository = new SubdivisionRepository();
        $stateProvince = $subdivisionRepository->get($this->countryCode, [$this->stateProvince]);

        return $stateProvince->getName();
    }

    public function setStateProvince(string $stateProvince): Address
    {
        $this->stateProvince = $stateProvince;
        return $this;
    }

    public function isResidential(): bool
    {
        return $this->isResidential;
    }

    public function setIsResidential(bool $isResidential): Address
    {
        $this->isResidential = $isResidential;
        return $this;
    }

}
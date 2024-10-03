<?php
namespace verbb\shippy\models;

use verbb\shippy\carriers\CarrierInterface;
use verbb\shippy\Shippy;

class Shipment extends Model
{
    // Properties
    // =========================================================================

    protected Address $from;
    protected Address $to;
    protected ?string $currency = null;
    protected array $packages = [];
    protected array $carriers = [];


    // Public Methods
    // =========================================================================

    public function getFrom(): Address
    {
        return $this->from;
    }

    public function setFrom(Address $from): Shipment
    {
        $this->from = $from;
        return $this;
    }

    public function getTo(): Address
    {
        return $this->to;
    }

    public function setTo(Address $to): Shipment
    {
        $this->to = $to;
        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(?string $currency): Shipment
    {
        $this->currency = $currency;
        return $this;
    }

    public function getPackages(): array
    {
        return $this->packages;
    }

    public function setPackages(array $packages): Shipment
    {
        $this->packages = $packages;
        return $this;
    }

    public function addPackage(Package $package): Shipment
    {
        $this->packages[] = $package;
        return $this;
    }

    public function getCarriers(): array
    {
        return $this->carriers;
    }

    public function setCarriers(array $carriers): Shipment
    {
        $this->carriers = $carriers;
        return $this;
    }

    public function addCarrier(CarrierInterface $carrier): Shipment
    {
        $this->carriers[] = $carrier;
        return $this;
    }

    public function getRates(): RateResponse
    {
        $response = [];
        $errors = [];
        $rates = [];

        foreach ($this->getCarriers() as $carrier) {
            // Clone the original shipment, as it may be modified by each carrier
            $shipment = clone $this;

            // Convert each package to the units defined by the carrier
            $shipment->getPackagesForCarrier($carrier);

            // Fetch the rates according to the carrier
            $rateResponse = $carrier->getRates($shipment);

            if (!$rateResponse) {
                continue;
            }

            if (!$rateResponse->getRates()) {
                Shippy::debug('{name} Rates: No rates matched.', [
                    'name' => $carrier::getName(),
                ]);
            }

            // Filter any rates that are 0 value, or are disallowed services
            $rateResponse->rates = array_filter($rateResponse->getRates(), function($rate) use ($carrier) {
                if ($rate->getRate() <= 0) {
                    return false;
                }

                if (!empty($carrier->getAllowedServiceCodes()) && !in_array($rate->getServiceCode(), $carrier->getAllowedServiceCodes())) {
                    return false;
                }

                return true;
            });

            $rates[$carrier->displayName()] = $rateResponse->getRates();

            if ($responseErrors = $rateResponse->getErrors()) {
                $errors[$carrier->displayName()] = $responseErrors;
            } else {
                $response[$carrier->displayName()] = $rateResponse->getResponse();
            }
        }

        // Merge here for performance
        $rates = array_merge(...array_values($rates));

        // Sort by cost
        usort($rates, function(Rate $a, Rate $b) {
            return $a->getRate() <=> $b->getRate();
        });

        return new RateResponse([
            'response' => $response,
            'errors' => $errors,
            'rates' => $rates,
        ]);
    }

    public function getLabels(Rate $rate, array $options = []): LabelResponse
    {
        // Clone the original shipment, as it may be modified by each carrier
        $shipment = clone $this;

        $carrier = $rate->getCarrier();

        // Convert each package to the units defined by the carrier
        $shipment->getPackagesForCarrier($carrier);

        // Fetch the labels according to the carrier
        $labelResponse = $carrier->getLabels($shipment, $rate, $options);

        if (!$labelResponse->getLabels()) {
            Shippy::debug('{name} Labels: No labels available.', [
                'name' => $carrier::getName(),
            ]);
        }

        return $labelResponse;
    }

    public function getPackagesForCarrier(CarrierInterface $carrier): array
    {
        $packages = $this->getPackages();

        // Convert each package to the units defined by the carrier
        foreach ($packages as &$package) {
            $package = $package->convertTo($carrier::getWeightUnit($this), $carrier::getDimensionUnit($this));
        }

        // Update the original packages
        $this->setPackages($packages);

        return $packages;
    }

    public function getTotalWeight(CarrierInterface $carrier, int $decimals = 2): int|float
    {
        $total = 0;

        foreach ($this->getPackagesForCarrier($carrier) as $package) {
            $total += $package->getWeight($decimals);
        }

        return $total;
    }

    public function getTotalWidth(CarrierInterface $carrier, int $decimals = 2, bool $stacked = true): int|float
    {
        $total = 0;

        foreach ($this->getPackagesForCarrier($carrier) as $package) {
            // Even when dealing with totals, we assume a "stacked" approach for multiple boxes
            if ($stacked) {
                $total = max($total, $package->getWidth($decimals));
            } else {
                $total += $package->getWidth($decimals);
            }
        }

        return $total;
    }

    public function getTotalHeight(CarrierInterface $carrier, int $decimals = 2, bool $stacked = false): int|float
    {
        $total = 0;

        foreach ($this->getPackagesForCarrier($carrier) as $package) {
            // Even when dealing with totals, we assume a "stacked" approach for multiple boxes
            if ($stacked) {
                $total = max($total, $package->getHeight($decimals));
            } else {
                $total += $package->getHeight($decimals);
            }
        }

        return $total;
    }

    public function getTotalLength(CarrierInterface $carrier, int $decimals = 2, bool $stacked = true): int|float
    {
        $total = 0;

        foreach ($this->getPackagesForCarrier($carrier) as $package) {
            // Even when dealing with totals, we assume a "stacked" approach for multiple boxes
            if ($stacked) {
                $total = max($total, $package->getLength($decimals));
            } else {
                $total += $package->getLength($decimals);
            }
        }

        return $total;
    }
}
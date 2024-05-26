<?php
namespace verbb\shippy\carriers;

use verbb\shippy\models\Address;
use verbb\shippy\models\Package;
use verbb\shippy\models\Rate;
use verbb\shippy\models\RateResponse;
use verbb\shippy\models\Request;
use verbb\shippy\models\Shipment;

class FedExFreight extends FedEx
{
    // Static Methods
    // =========================================================================

    public static function getName(): string
    {
        return 'FedEx Freight';
    }


    // Properties
    // =========================================================================

    protected Address $billing;
    protected Address $shipper;
    protected ?string $freightAccountNumber = null;


    // Public Methods
    // =========================================================================

    public function getBilling(): Address
    {
        return $this->billing;
    }

    public function setBilling(Address $billing): FedExFreight
    {
        $this->billing = $billing;
        return $this;
    }

    public function getShipper(): Address
    {
        return $this->shipper;
    }

    public function setShipper(Address $shipper): FedExFreight
    {
        $this->shipper = $shipper;
        return $this;
    }

    public function getFreightAccountNumber(): ?string
    {
        return $this->freightAccountNumber;
    }

    public function setFreightAccountNumber(?string $freightAccountNumber): FedExFreight
    {
        $this->freightAccountNumber = $freightAccountNumber;
        return $this;
    }

    public function getRates(Shipment $shipment): ?RateResponse
    {
        // Only include for shipments over 150lb
        $totalWeight = 0;

        foreach ($shipment->getPackages() as $package) {
            $totalWeight += $package->getWeight();
        }

        if ($totalWeight < 150) {
            return null;
        }

        return parent::getRates($shipment);
    }


    // Protected Methods
    // =========================================================================

    protected function getRequest(Shipment $shipment): Request
    {
        $payload = [
            'accountNumber' => [
                'value' => $this->accountNumber,
            ],
            'rateRequestControlParameters' => [
                'returnTransitTimes' => true,
                'servicesNeededOnRateFailure' => true,
            ],
            'freightRequestedShipment' => [
                'shipper' => [
                    'address' => $this->getAddress($shipment->getFrom()),
                ],
                'recipient' => [
                    'address' => $this->getAddress($shipment->getTo()),
                ],
                'shippingChargesPayment' => [
                    'payor' => [
                        'responsibleParty' => [
                            'accountNumber' => [
                                'value' => $this->freightAccountNumber,
                            ],
                        ],
                    ],
                    'paymentType' => 'SENDER',
                ],
                'requestedPackageLineItems' => array_map(function($key, Package $package) use ($shipment) {
                    return [
                        'subPackagingType' => 'BAG',
                        'groupPackageCount' => 1,
                        'weight' => [
                            'units' => strtoupper(self::getWeightUnit($shipment)),
                            'value' => $package->getWeight(),
                        ],
                        'dimensions' => [
                            'length' => $package->getLength(0),
                            'width' => $package->getWidth(0),
                            'height' => $package->getHeight(0),
                            'units' => strtoupper(self::getDimensionUnit($shipment)),
                        ],
                        'associatedFreightLineItems' => [
                            [
                                'id' => 'id_' . $key,
                            ],
                        ],
                    ];
                }, array_keys($shipment->getPackages()), $shipment->getPackages()),
                'freightShipmentDetail' => [
                    'role' => 'SHIPPER',
                    'accountNumber' => [
                        'value' => $this->freightAccountNumber,
                    ],
                    'fedExFreightBillingContactAndAddress' => [
                        'address' => $this->getAddress($this->billing),
                    ],
                    'totalHandlingUnits' => 0,
                    'lineItem' => array_map(function($key, Package $package) use ($shipment) {
                        return [
                            'id' => 'id_' . $key,
                            'freightClass' => 'CLASS_050',
                            'subPackagingType' => 'BAG',
                            'pieces' => 0,
                            'handlingUnits' => 0,
                            'weight' => [
                                'units' => strtoupper(self::getWeightUnit($shipment)),
                                'value' => $package->getWeight(),
                            ],
                            'dimensions' => [
                                'length' => $package->getLength(0),
                                'width' => $package->getWidth(0),
                                'height' => $package->getHeight(0),
                                'units' => strtoupper(self::getDimensionUnit($shipment)),
                            ],
                        ];
                    }, array_keys($shipment->getPackages()), $shipment->getPackages()),
                ],
            ],
        ];

        return new Request([
            'endpoint' => 'rate/v1/freight/rates/quotes',
            'payload' => [
                'json' => $payload,
            ],
        ]);
    }

    protected function getLabelRequest(Shipment $shipment, Rate $rate, array $options = []): Request
    {
        $labelRequest = parent::getLabelRequest($shipment, $rate, $options);
        $labelRequest['freightRequestedShipment'] = $labelRequest['requestedShipment'];
        unset($labelRequest['requestedShipment']);

        return new Request([
            'method' => 'POST',
            'endpoint' => 'ship/v1/freight/shipments',
            'payload' => [
                'json' => $labelRequest,
            ],
        ]);
    }
}

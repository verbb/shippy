<?php
namespace verbb\shippy\carriers;


use Illuminate\Support\Arr;
use verbb\shippy\exceptions\InvalidRequestException;
use verbb\shippy\helpers\Json;
use verbb\shippy\models\Address;
use verbb\shippy\models\HttpClient;
use verbb\shippy\models\Label;
use verbb\shippy\models\LabelResponse;
use verbb\shippy\models\Package;
use verbb\shippy\models\Rate;
use verbb\shippy\models\RateResponse;
use verbb\shippy\models\Request;
use verbb\shippy\models\Response;
use verbb\shippy\models\Shipment;
use verbb\shippy\models\Tracking;
use verbb\shippy\models\TrackingDetail;
use verbb\shippy\models\TrackingResponse;

class UPSFreight extends UPS
{
    // Static Methods
    // =========================================================================

    public static function getName(): string
    {
        return 'UPS Freight';
    }


    // Properties
    // =========================================================================

    protected ?string $freightClass = '50';
    protected ?string $freightService = '308';
    protected ?string $freightPackingType = 'BAG';
    protected ?string $pickupDate = null;
    protected ?string $earliestTimeReady = '0900';
    protected ?string $latestTimeReady = '1700';
    protected ?string $shipmentBillingOption = '10';
    protected ?string $alternateRateOptions = '3';


    // Public Methods
    // =========================================================================

    public function init(): void
    {
        if (!$this->pickupDate) {
            $this->pickupDate = date('Ymd');
        }

        parent::init();
    }

    public function getFreightClass(): ?string
    {
        return $this->freightClass;
    }

    public function setFreightClass(?string $freightClass): UPS
    {
        $this->freightClass = $freightClass;
        return $this;
    }

    public function getFreightService(): ?string
    {
        return $this->freightService;
    }

    public function setFreightService(?string $freightService): UPS
    {
        $this->freightService = $freightService;
        return $this;
    }

    public function getFreightPackingType(): ?string
    {
        return $this->freightPackingType;
    }

    public function setFreightPackingType(?string $freightPackingType): UPS
    {
        $this->freightPackingType = $freightPackingType;
        return $this;
    }

    /**
     * @throws InvalidRequestException
     */
    public function getRates(Shipment $shipment): ?RateResponse
    {
        $this->validate('clientId', 'clientSecret', 'accountNumber', 'freightClass', 'freightService', 'freightPackingType');

        $payload = [
            'FreightRateRequest' => [
                'ShipperNumber' => $this->accountNumber,
                'ShipFrom' => $this->getContact($shipment->getFrom()),
                'ShipTo' => $this->getContact($shipment->getTo()),
                'PaymentInformation' => [
                    'Payer' => $this->getContact($shipment->getFrom()),
                    'ShipmentBillingOption' => [
                        'Code' => $this->shipmentBillingOption,
                    ],
                ],
                'Service' => [
                    'Code' => $this->freightService,
                ],
                'Commodity' => $this->getPackages($shipment),
                'AlternateRateOptions' => [
                    'Code' => $this->alternateRateOptions,
                ],
                'PickupRequest' => [
                    'PickupDate' => $this->pickupDate,
                ],
                'GFPOptions' => [
                    'GPFAccesorialRateIndicator' => '',
                ],
                'TimeInTransitIndicator' => '',
            ],
        ];

        $payload['FreightRateRequest']['Shipment']['Shipper']['ShipperNumber'] = $this->accountNumber;
        $payload['FreightRateRequest']['PaymentInformation']['Payer']['ShipperNumber'] = $this->accountNumber;

        $request = new Request([
            'endpoint' => 'api/freight/v1/rating/ground',
            'payload' => [
                'json' => $payload,
            ],
        ]);

        $data = $this->fetchRates($request, function(Response $response) {
            return $response->json();
        });

        $rates = [];
        $rate = Arr::get($data, 'FreightRateResponse.TotalShipmentCharge.MonetaryValue', 0);

        if ($rate) {
            $rates[] =  new Rate([
                'carrier' => $this,
                'response' => $data,
                'serviceName' => Arr::get($data, 'FreightRateResponse.Commodity.Description'),
                'serviceCode' => 'freight-' . $this->freightClass,
                'rate' => $rate,
                'currency' => Arr::get($data, 'FreightRateResponse.TotalShipmentCharge.CurrencyCode'),
            ]);
        }

        return new RateResponse([
            'response' => $data,
            'rates' => $rates,
        ]);
    }

    /**
     * @throws InvalidRequestException
     */
    public function getLabels(Shipment $shipment, Rate $rate, array $options = []): ?LabelResponse
    {
        $this->validate('clientId', 'clientSecret', 'accountNumber', 'freightClass', 'freightService', 'freightPackingType');

        $payload = [
            'FreightShipRequest' => [
                'Shipment' => [
                    'ShipperNumber' => $this->accountNumber,
                    'ShipFrom' => $this->getContact($shipment->getFrom()),
                    'ShipTo' => $this->getContact($shipment->getTo()),
                    'PaymentInformation' => [
                        'Payer' => $this->getContact($shipment->getFrom()),
                        'ShipmentBillingOption' => [
                            'Code' => $this->shipmentBillingOption,
                        ],
                    ],
                    'Service' => [
                        'Code' => $this->freightService,
                    ],
                    'Commodity' => $this->getPackages($shipment),
                    'PickupRequest' => [
                        'Requester' => $this->getContact($shipment->getFrom()),
                        'PickupDate' => $this->pickupDate,
                        'EarliestTimeReady' => $this->earliestTimeReady,
                        'LatestTimeReady' => $this->latestTimeReady,
                    ],
                    'HandlingUnitOne' => [
                        'Quantity' => (string)count($shipment->getPackages()),
                        'Type' =>  [
                            'Code' => $this->freightPackingType,
                        ],
                    ],
                ],
            ],
        ];

        $payload['FreightShipRequest']['PaymentInformation']['Payer']['ShipperNumber'] = $this->accountNumber;

        $request = new Request([
            'endpoint' => 'api/freight/v1/shipments/Ground',
            'payload' => [
                'json' => $payload,
            ],
        ]);

        // Custom parsing for multipart response
        $data = $this->fetchLabels($request, function(Response $response) {
            return $response->json();
        });

        $shipmentId = Arr::get($data, 'FreightShipResponse.ShipmentResults.ShipmentIdentificationNumber', '');

        $labels = [];

        if ($shipmentId) {
            $labels[] = new Label([
                'carrier' => $this,
                'response' => $data,
                'rate' => $rate,
                'trackingNumber' => Arr::get($data, 'FreightShipResponse.ShipmentResults.PackageResults.TrackingNumber', ''),
                'labelId' => $shipmentId,
                'labelData' => Arr::get($data, 'FreightShipResponse.ShipmentResults.PackageResults.ShippingLabel.GraphicImage', ''),
                'labelMime' => 'image/gif',
            ]);
        }

        return new LabelResponse([
            'response' => $data,
            'labels' => $labels,
        ]);
    }

    protected function getPackages(Shipment $shipment): array
    {
        return array_map(function($key, Package $package) use ($shipment) {
            return [
                'Description' => 'FRS-Freight-' . $key,
                'Weight' => [
                    'Value' => $package->getWeight(),
                    'UnitOfMeasurement' => [
                        'Code' => $this->_getWeightUnit($shipment),
                    ],
                ],
                'Dimensions' => [
                    'Length' => $package->getLength(),
                    'Width' => $package->getWidth(),
                    'Height' => $package->getHeight(),
                    'UnitOfMeasurement' => [
                        'Code' => $this->_getDimensionUnit($shipment),
                    ],
                ],
                'FreightClass' => $this->freightClass,
                'NumberOfPieces' => '1',
                'PackagingType' => [
                    'Code' => $this->freightPackingType,
                ],
            ];
        }, array_keys($shipment->getPackages()), $shipment->getPackages());
    }

    // Private Methods
    // =========================================================================

    private function _getWeightUnit(Shipment $shipment): string
    {
        $unit = self::getWeightUnit($shipment);

        return match ($unit) {
            'lb' => 'LBS',
            'kg' => 'KGS',
        };
    }

    private function _getDimensionUnit(Shipment $shipment): string
    {
        $unit = self::getDimensionUnit($shipment);

        return match ($unit) {
            'in' => 'IN',
            'cm' => 'CM',
        };
    }
}

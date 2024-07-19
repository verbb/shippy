<?php
namespace verbb\shippy\carriers;

use Exception;
use Illuminate\Support\Arr;
use verbb\shippy\exceptions\InvalidRequestException;
use verbb\shippy\models\Address;
use verbb\shippy\models\HttpClient;
use verbb\shippy\models\LabelResponse;
use verbb\shippy\models\Rate;
use verbb\shippy\models\RateResponse;
use verbb\shippy\models\Request;
use verbb\shippy\models\Response;
use verbb\shippy\models\Shipment;
use verbb\shippy\models\Tracking;
use verbb\shippy\models\TrackingDetail;
use verbb\shippy\models\TrackingResponse;

class Interparcel extends AbstractCarrier
{
    // Static Methods
    // =========================================================================

    public static function getName(): string
    {
        return 'Interparcel';
    }

    public static function getWeightUnit(Shipment $shipment): string
    {
        return 'kg';
    }

    public static function getDimensionUnit(Shipment $shipment): string
    {
        return 'cm';
    }
    
    public static function getTrackingUrl(string $trackingNumber): ?string
    {
        return "https://www.interparcel.com.au/tracking.php?action=dotrack&trackno={$trackingNumber}";
    }

    public static function supportsLabels(): bool
    {
        return false;
    }


    // Properties
    // =========================================================================

    protected ?string $apiKey = null;
    protected array $carriers = [];
    protected array $serviceLevels = [];
    protected array $pickupTypes = [];


    // Public Methods
    // =========================================================================

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    public function setApiKey(?string $apiKey): Interparcel
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    public function getCarriers(): array
    {
        return $this->carriers;
    }

    public function setCarriers(array $carriers): Interparcel
    {
        $this->carriers = $carriers;
        return $this;
    }

    public function getServiceLevels(): array
    {
        return $this->serviceLevels;
    }

    public function setServiceLevels(array $serviceLevels): Interparcel
    {
        $this->serviceLevels = $serviceLevels;
        return $this;
    }

    public function getPickupTypes(): array
    {
        return $this->pickupTypes;
    }

    public function setPickupTypes(array $pickupTypes): Interparcel
    {
        $this->pickupTypes = $pickupTypes;
        return $this;
    }

    /**
     * @throws InvalidRequestException
     */
    public function getRates(Shipment $shipment): ?RateResponse
    {
        $this->validate('apiKey');

        $payload = [
            'collection' => $this->getAddress($shipment->getFrom()),
            'delivery' => $this->getAddress($shipment->getTo()),
            'parcels' => array_map(function($package) {
                return [
                    'weight' => $package->getWeight(),
                    'length' => $package->getLength(),
                    'width' => $package->getWidth(),
                    'height' => $package->getHeight(),
                ];
            }, $shipment->getPackages()),
        ];

        $carriers = $this->carriers;
        $serviceLevels = $this->serviceLevels;
        $pickupTypes = $this->pickupTypes;

        if ($carriers) {
            $payload['filter']['carriers'] = $carriers;
        }

        if ($serviceLevels) {
            $payload['filter']['serviceLevel'] = $serviceLevels;
        }

        if ($pickupTypes) {
            $payload['filter']['pickupType'] = $pickupTypes;
        }

        $request = new Request([
            'endpoint' => 'quote/v2',
            'payload' => [
                'json' => ['shipment' => $payload],
            ],
        ]);

        $data = $this->fetchRates($request, function(Response $response) {
            return $response->json();
        });

        $rates = [];

        foreach (Arr::get($data, 'services', []) as $service) {
            $serviceCode = Arr::get($service, 'id');
            $serviceName = Arr::get($service, 'service');
            $rate = Arr::get($service, 'price', 0);
            $currency = Arr::get($service, 'currency');

            $rates[] = new Rate([
                'carrier' => $this,
                'response' => $service,
                'serviceName' => $serviceName,
                'serviceCode' => $serviceCode,
                'rate' => $rate,
                'currency' => $currency,
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
    public function getTrackingStatus(array $trackingNumbers, array $options = []): ?TrackingResponse
    {
        $this->validate('apiKey');

        $data = [];
        $tracking = [];

        foreach ($trackingNumbers as $trackingNumber) {
            $trackingNumber = str_replace(' ', '', $trackingNumber);

            $request = new Request([
                'method' => 'GET',
                'endpoint' => "tracking/v1/{$trackingNumber}",
            ]);

            $data = $this->fetchTracking($request, function(Response $response) {
                return $response->json();
            });

            $statusCode = Arr::get($data, 'currentStatus', '');
            $status = $this->_mapTrackingStatus($statusCode);

            $tracking[] = new Tracking([
                'carrier' => $this,
                'response' => $data,
                'trackingNumber' => $trackingNumber,
                'status' => $status,
                'estimatedDelivery' => null,
                'details' => array_map(function($detail) {
                    return new TrackingDetail([
                        'location' => Arr::get($detail, 'location', ''),
                        'description' => Arr::get($detail, 'event', ''),
                        'date' => Arr::get($detail, 'date', '') . ' ' . Arr::get($detail, 'time', ''),
                    ]);
                }, Arr::get($data, 'events', [])),
            ]);
        }

        return new TrackingResponse([
            'response' => $data,
            'tracking' => $tracking,
        ]);
    }

    /**
     * @throws Exception
     */
    public function getLabels(Shipment $shipment, Rate $rate, array $options = []): ?LabelResponse
    {
        throw new Exception('Not implemented.');
    }

    public function getHttpClient(): HttpClient
    {
        return new HttpClient([
            'base_uri' => 'https://api.au.interparcel.com/',
            'headers' => [
                'X-Interparcel-Auth' => $this->apiKey,
                'Content-Type' => 'application/json',
            ],
        ]);
    }


    // Protected Methods
    // =========================================================================

    protected function getAddress(Address $address): array
    {
        return [
            'city' => $address->getCity(),
            'postcode' => $address->getPostalCode(),
            'state' => $address->getStateProvince(),
            'country' => $address->getCountryCode(),
        ];
    }


    // Private Methods
    // =========================================================================

    private function _mapTrackingStatus(string $status): string
    {
        return match ($status) {
            'B', 'C', 'I', 'T' => Tracking::STATUS_IN_TRANSIT,
            'D' => Tracking::STATUS_DELIVERED,
            default => Tracking::STATUS_UNKNOWN,
        };
    }
}

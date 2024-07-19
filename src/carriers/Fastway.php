<?php
namespace verbb\shippy\carriers;

use Exception;
use Illuminate\Support\Arr;
use verbb\shippy\exceptions\InvalidRequestException;
use verbb\shippy\helpers\Json;
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


class Fastway extends AbstractCarrier
{
    // Static Methods
    // =========================================================================

    public static function getName(): string
    {
        return 'Fastway';
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
        return "https://fastway.com.au/courier-services/track-your-parcel?l={$trackingNumber}";
    }

    public static function getServiceCodes(): array
    {
        return [
            'RED' => 'Road Parcel (Red)',
            'GREEN' => 'Road Parcel (Green)',

            'BROWN' => 'Local Parcel (Brown)',
            'BLACK' => 'Local Parcel (Black)',
            'BLUE' => 'Local Parcel (Blue)',
            'YELLOW' => 'Local Parcel (Yellow)',

            'PINK' => 'Shorthaul Parcel (Pink)',

            'SAT_NAT_A2' => 'National Network A2 Satchel',
            'SAT_NAT_A3' => 'National Network A3 Satchel',
            'SAT_NAT_A4' => 'National Network A4 Satchel',
            'SAT_NAT_A5' => 'National Network A5 Satchel',
        ];
    }

    public static function supportsLabels(): bool
    {
        return false;
    }


    // Properties
    // =========================================================================

    protected ?string $apiKey = null;


    // Public Methods
    // =========================================================================

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    public function setApiKey(?string $apiKey): Fastway
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    /**
     * @throws InvalidRequestException
     */
    public function getRates(Shipment $shipment): ?RateResponse
    {
        $this->validate('apiKey');

        $countryCode = $this->_getCountryCode($shipment->getTo()->getCountryCode());

        if (!$countryCode) {
            return null;
        }

        $response = $this->getHttpClient()->request('GET', 'pickuprf/' . $shipment->getTo()->getPostalCode() . '/' . $countryCode, [
            'query' => [
                'api_key' => $this->apiKey,
            ],
        ]);

        $data = Json::decode($response->getBody()->getContents());
        $franchiseCode = $data['result']['franchise_code'] ?? false;

        if (!$franchiseCode) {
            return null;
        }

        $url = [
            'lookup',
            $franchiseCode,
            $shipment->getTo()->getCity(),
            $shipment->getTo()->getPostalCode(),
            $shipment->getTotalWeight($this),
        ];

        $request = new Request([
            'method' => 'GET',
            'endpoint' => 'v4/psc/' . implode('/', $url),
            'payload' => [
                'query' => [
                    'api_key' => $this->apiKey,
                ],
            ],
        ]);

        $data = $this->fetchRates($request, function(Response $response) {
            return $response->json();
        });

        $rates = [];

        foreach (Arr::get($data, 'result.services', []) as $service) {
            $serviceCode = Arr::get($service, 'labelcolour');
            $serviceName = Arr::get($service, 'labelcolour_pretty');
            $rate = Arr::get($service, 'totalprice_normal', 0);

            $rates[] = new Rate([
                'carrier' => $this,
                'response' => $service,
                'serviceName' => $serviceName,
                'serviceCode' => $serviceCode,
                'rate' => $rate,
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

        $countryCode = $this->_getCountryCode(Arr::get($options, 'countryCode', 'AU'));

        foreach ($trackingNumbers as $trackingNumber) {
            $trackingNumber = str_replace(' ', '', $trackingNumber);

            $request = new Request([
                'method' => 'GET',
                'endpoint' => "v6/tracktrace/detail/{$trackingNumber}/{$countryCode}",
                'payload' => [
                    'query' => [
                        'api_key' => $this->apiKey,
                    ],
                ],
            ]);

            $data = $this->fetchTracking($request, function(Response $response) {
                return $response->json();
            });

            $statusCode = Arr::get($data, 'result.Scans.0.Type', '');
            $status = $this->_mapTrackingStatus($statusCode);

            $tracking[] = new Tracking([
                'carrier' => $this,
                'response' => $data,
                'trackingNumber' => $trackingNumber,
                'status' => $status,
                'estimatedDelivery' => Arr::get($data, 'result.DeliveryETADate', ''),
                'details' => array_map(function($detail) {
                    return new TrackingDetail([
                        'location' => Arr::get($detail, 'Name', ''),
                        'description' => Arr::get($detail, 'StatusDescription', ''),
                        'date' => Arr::get($detail, 'RealDateTime', ''),
                    ]);
                }, Arr::get($data, 'result.Scans', [])),
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
            'base_uri' => 'https://au.api.fastway.org/',
        ]);
    }


    // Private Methods
    // =========================================================================

    private function _getCountryCode(string $countryCode): bool|int
    {
        if ($countryCode == 'AU') {
            return 1;
        }

        if ($countryCode == 'NZ') {
            return 6;
        }

        // Ireland
        if ($countryCode == 'IE') {
            return 11;
        }

        // South Africa
        if ($countryCode == 'SA') {
            return 24;
        }

        return false;
    }

    private function _mapTrackingStatus(string $status): string
    {
        return match ($status) {
            'P' => Tracking::STATUS_AVAILABLE_FOR_PICKUP,
            'T' => Tracking::STATUS_IN_TRANSIT,
            'D' => Tracking::STATUS_DELIVERED,
            default => Tracking::STATUS_UNKNOWN,
        };
    }
}

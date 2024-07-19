<?php
namespace verbb\shippy\carriers;

use Illuminate\Support\Arr;
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

class AramexAustralia extends AbstractCarrier
{
    // Static Methods
    // =========================================================================

    public static function getName(): string
    {
        return 'Aramex Australia';
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
        return "https://www.aramex.com.au/tools/track?l={$trackingNumber}";
    }


    // Properties
    // =========================================================================

    protected ?string $clientId = null;
    protected ?string $clientSecret = null;


    // Public Methods
    // =========================================================================

    public function getClientId(): ?string
    {
        return $this->clientId;
    }

    public function setClientId(?string $clientId): AramexAustralia
    {
        $this->clientId = $clientId;
        return $this;
    }

    public function getClientSecret(): ?string
    {
        return $this->clientSecret;
    }

    public function setClientSecret(?string $clientSecret): AramexAustralia
    {
        $this->clientSecret = $clientSecret;
        return $this;
    }

    public function getRates(Shipment $shipment): ?RateResponse
    {
        $payload = [
            'To' => $this->getContact($shipment->getTo()),
            'Items' => $this->getPackages($shipment),
        ];

        $request = new Request([
            'endpoint' => 'api/consignments/quote',
            'payload' => [
                'json' => $payload,
            ],
        ]);

        $data = $this->fetchRates($request, function(Response $response) {
            return $response->json();
        });

        $rates = [];

        if ($rate = Arr::get($data, 'data.total')) {
            $rates[] = new Rate([
                'carrier' => $this,
                'response' => $data,
                'serviceName' => 'Postage',
                'serviceCode' => 'Postage',
                'rate' => round($rate, 2),
            ]);
        }

        return new RateResponse([
            'response' => $data,
            'rates' => $rates,
        ]);
    }

    public function getTrackingStatus(array $trackingNumbers, array $options = []): ?TrackingResponse
    {
        $data = [];
        $tracking = [];

        foreach ($trackingNumbers as $trackingNumber) {
            $trackingNumber = str_replace(' ', '', $trackingNumber);

            $request = new Request([
                'method' => 'GET',
                'endpoint' => "api/track/label/{$trackingNumber}",
            ]);

            $data = $this->fetchTracking($request, function(Response $response) {
                return $response->json();
            });

            $trackingData = Arr::get($data, 'data', []);
            $trackingData = array_reverse($trackingData);

            $statusCode = Arr::get($trackingData, '0.scanType', '');
            $status = $this->_mapTrackingStatus($statusCode);

            $tracking[] = new Tracking([
                'carrier' => $this,
                'response' => $trackingData,
                'trackingNumber' => $trackingNumber,
                'status' => $status,
                'estimatedDelivery' => null,
                'details' => array_map(function($detail) {
                    return new TrackingDetail([
                        'description' => Arr::get($detail, 'description', ''),
                        'date' => Arr::get($detail, 'scannedDateTime', ''),
                    ]);
                }, $trackingData),
            ]);
        }

        return new TrackingResponse([
            'response' => $data,
            'tracking' => $tracking,
        ]);
    }

    public function getLabels(Shipment $shipment, Rate $rate, array $options = []): ?LabelResponse
    {
        $payload = [
            'To' => $this->getContact($shipment->getTo()),
            'Items' => $this->getPackages($shipment),
        ];

        $request = new Request([
            'endpoint' => 'api/consignments',
            'payload' => [
                'json' => $payload,
            ],
        ]);

        $data = $this->fetchLabels($request, function(Response $response) {
            return $response->json();
        });

        $consignmentId = Arr::get($data, 'data.conId', '');

        $labels = [];

        if ($consignmentId) {
            $request = new Request([
                'method' => 'GET',
                'endpoint' => "api/consignments/{$consignmentId}/labels",
            ]);

            $labelData = $this->fetchLabels($request, function(Response $response) {
                return ['label' => base64_encode($response->getContent())];
            });

            $labels[] = new Label([
                'carrier' => $this,
                'response' => $data,
                'rate' => $rate,
                'trackingNumber' => Arr::get($data, 'data.items.0.label'),
                'labelId' => $consignmentId,
                'labelData' => Arr::get($labelData, 'label', ''),
                'labelMime' => 'application/pdf',
            ]);
        }

        return new LabelResponse([
            'response' => $data,
            'labels' => $labels,
        ]);
    }

    public function getHttpClient(): HttpClient
    {
        // Fetch an access token first
        $authResponse = Json::decode((string)(new HttpClient())
            ->request('POST', 'https://identity.fastway.org/connect/token', [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'scope' => 'fw-fl2-api-au',
                ],
            ])->getBody());

        return new HttpClient([
            'base_uri' => 'https://api.myfastway.com.au',
            'headers' => [
                'client_id' => $this->clientId,
                'Authorization' => 'Bearer ' . $authResponse['access_token'] ?? '',
                'Content-Type' => 'application/json',
            ],
        ]);
    }


    // Protected Methods
    // =========================================================================

    protected function getAddress(Address $address): array
    {
        return [
            'StreetAddress' => $address->getStreet1(),
            'Locality' => $address->getCity(),
            'StateOrProvince' => $address->getStateProvince(),
            'PostalCode' => $address->getPostalCode(),
            'Country' => $address->getCountryCode(),
        ];
    }

    protected function getContact(Address $address): array
    {
        return [
            'ContactName' => $address->getFullName(),
            'PhoneNumber' => $address->getPhone(),
            'Address' => $this->getAddress($address),
        ];
    }

    protected function getPackages(Shipment $shipment): array
    {
        return array_map(function(Package $package) {
            return [
                'Quantity' => 1,
                'Reference' => '',
                'PackageType' => 'P',
                'WeightDead' => $package->getWeight(),
                'Height' => $package->getHeight(),
                'Length' => $package->getLength(),
                'Width' => $package->getWidth(),
            ];
        }, $shipment->getPackages());
    }


    // Private Methods
    // =========================================================================

    private function _mapTrackingStatus(string $status): string
    {
        return match ($status) {
            'D' => Tracking::STATUS_DELIVERED,
            'P' => Tracking::STATUS_AVAILABLE_FOR_PICKUP,
            'T' => Tracking::STATUS_IN_TRANSIT,
            default => Tracking::STATUS_UNKNOWN,
        };
    }
}

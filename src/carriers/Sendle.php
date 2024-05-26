<?php
namespace verbb\shippy\carriers;

use Illuminate\Support\Arr;
use verbb\shippy\exceptions\InvalidRequestException;
use verbb\shippy\models\HttpClient;
use verbb\shippy\models\Label;
use verbb\shippy\models\LabelResponse;
use verbb\shippy\models\Rate;
use verbb\shippy\models\RateResponse;
use verbb\shippy\models\Request;
use verbb\shippy\models\Response;
use verbb\shippy\models\Shipment;
use verbb\shippy\models\Tracking;
use verbb\shippy\models\TrackingDetail;
use verbb\shippy\models\TrackingResponse;

class Sendle extends AbstractCarrier
{
    // Static Methods
    // =========================================================================

    public static function getName(): string
    {
        return 'Sendle';
    }

    public static function getWeightUnit(Shipment $shipment): string
    {
        return 'kg';
    }

    public static function getDimensionUnit(Shipment $shipment): string
    {
        return 'cm';
    }

    public static function isDomestic(string $countryCode): bool
    {
        return $countryCode === 'AU';
    }
    
    public static function getTrackingUrl(string $trackingNumber): ?string
    {
        return "https://track.sendle.com/tracking?ref=${trackingNumber}";
    }


    // Properties
    // =========================================================================

    protected ?string $apiKey = null;
    protected ?string $sendleId = null;


    // Public Methods
    // =========================================================================

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    public function setApiKey(?string $apiKey): Sendle
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    public function getSendleId(): ?string
    {
        return $this->sendleId;
    }

    public function setSendleId(?string $sendleId): Sendle
    {
        $this->sendleId = $sendleId;
        return $this;
    }

    /**
     * @throws InvalidRequestException
     */
    public function getRates(Shipment $shipment): ?RateResponse
    {
        $this->validate('apiKey', 'sendleId');

        $payload = [
            'pickup_suburb' => $shipment->getFrom()->getCity(),
            'pickup_postcode' => $shipment->getFrom()->getPostalCode(),
            'pickup_country' => $shipment->getFrom()->getCountryCode(),
            'delivery_suburb' => $shipment->getTo()->getCity(),
            'delivery_postcode' => $shipment->getTo()->getPostalCode(),
            'delivery_country' => $shipment->getTo()->getCountryCode(),
            'weight_units' => self::getWeightUnit($shipment),
            'weight_value' => $shipment->getTotalWeight($this),
        ];

        $request = new Request([
            'method' => 'GET',
            'endpoint' => 'quote',
            'payload' => [
                'query' => $payload,
            ],
        ]);

        $data = $this->fetchRates($request, function(Response $response) {
            return $response->json();
        });

        $rates = [];

        foreach ($data as $service) {
            $serviceCode = Arr::get($service, 'plan_name');
            $serviceName = Arr::get($service, 'plan_name');
            $rate = Arr::get($service, 'quote.gross.amount');

            if ($rate) {
                $rates[] = new Rate([
                    'carrier' => $this,
                    'response' => $service,
                    'serviceName' => $serviceName,
                    'serviceCode' => $serviceCode,
                    'rate' => $rate,
                ]);
            }
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
        $this->validate('apiKey', 'sendleId');

        $data = [];
        $tracking = [];

        foreach ($trackingNumbers as $trackingNumber) {
            $trackingNumber = str_replace(' ', '', $trackingNumber);

            $request = new Request([
                'method' => 'GET',
                'endpoint' => "tracking/{$trackingNumber}",
            ]);

            $data = $this->fetchTracking($request, function(Response $response) {
                return $response->json();
            });

            $statusCode = Arr::get($data, 'state', '');
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
                        'description' => Arr::get($detail, 'description', ''),
                        'date' => Arr::get($detail, 'scan_time', ''),
                    ]);
                }, Arr::get($data, 'tracking_events', [])),
            ]);
        }

        return new TrackingResponse([
            'response' => $data,
            'tracking' => $tracking,
        ]);
    }

    /**
     * @throws InvalidRequestException
     */
    public function getLabels(Shipment $shipment, Rate $rate, array $options = []): ?LabelResponse
    {
        $this->validate('apiKey', 'sendleId');

        $payload = [
            'sender' => [
                'contact' => [
                    'name' => $shipment->getFrom()->getFullName(),
                ],
                'address' => [
                    'address_street1' => $shipment->getFrom()->getStreet1(),
                    'suburb' => $shipment->getFrom()->getCity(),
                    'postcode' => $shipment->getFrom()->getPostalCode(),
                    'state_name' => $shipment->getFrom()->getStateProvince(),
                    'country' => $shipment->getFrom()->getCountryCode(),
                ],
            ],
            'receiver' => [
                'contact' => [
                    'name' => $shipment->getTo()->getFullName(),
                ],
                'address' => [
                    'address_street1' => $shipment->getTo()->getStreet1(),
                    'suburb' => $shipment->getTo()->getCity(),
                    'postcode' => $shipment->getTo()->getPostalCode(),
                    'state_name' => $shipment->getTo()->getStateProvince(),
                    'country' => $shipment->getTo()->getCountryCode(),
                ],
                'instructions' => 'NA',
            ],
            'weight' => [
                'units' => self::getWeightUnit($shipment),
                'value' => $shipment->getTotalWeight($this),
            ],
            'description' => 'NA',
        ];

        $request = new Request([
            'method' => 'POST',
            'endpoint' => 'orders',
            'payload' => [
                'json' => $payload,
            ],
        ]);

        $data = $this->fetchLabels($request, function(Response $response) {
            return $response->json();
        });

        $labels = [];

        $labels[] = new Label([
            'carrier' => $this,
            'response' => $data,
            'rate' => $rate,
            'trackingNumber' => Arr::get($data, 'sendle_reference'),
            'labelId' => Arr::get($data, 'order_id'),
            'labelData' => $this->_getLabelData(Arr::get($data, 'labels.0.url')),
            'labelMime' => 'application/pdf',
        ]);

        return new LabelResponse([
            'response' => $data,
            'labels' => $labels,
        ]);
    }

    public function getHttpClient(): HttpClient
    {
        if ($this->isProduction()) {
            $url = 'https://api.sendle.com/api/';
        } else {
            $url = 'https://sandbox.sendle.com/api/';
        }

        return new HttpClient([
            'base_uri' => $url,
            'auth' => [$this->sendleId, $this->apiKey],
        ]);
    }


    // Private Methods
    // =========================================================================

    private function _mapTrackingStatus(string $status): string
    {
        return match ($status) {
            'Pickup Attempted', 'Unable to Deliver', 'Damaged', 'Left with Agent', 'Card Left', 'Local Delivery', 'Delivery Attempted', 'In Transit', 'Info', 'Dropped Off', 'Drop Off', 'Pickup' => Tracking::STATUS_IN_TRANSIT,
            'Out for Delivery' => Tracking::STATUS_OUT_FOR_DELIVERY,
            'Delivered' => Tracking::STATUS_DELIVERED,
            default => Tracking::STATUS_UNKNOWN,
        };
    }

    private function _getLabelData(string $url): string
    {
        return base64_encode((new HttpClient())
            ->request('GET', $url, [
                'auth' => [$this->sendleId, $this->apiKey],
            ])->getBody()->getContents());
    }
}

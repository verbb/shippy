<?php
namespace verbb\shippy\carriers;

use Illuminate\Support\Arr;
use verbb\shippy\exceptions\InvalidRequestException;
use verbb\shippy\helpers\Json;
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

class NewZealandPost extends AbstractCarrier
{
    // Static Methods
    // =========================================================================

    public static function getName(): string
    {
        return 'New Zealand Post';
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
        return $countryCode === 'NZ';
    }
    
    public static function getTrackingUrl(string $trackingNumber): ?string
    {
        return "https://www.nzpost.co.nz/tools/tracking?trackid={$trackingNumber}";
    }


    // Properties
    // =========================================================================

    protected ?string $clientId = null;
    protected ?string $clientSecret = null;
    protected ?string $accountNumber = null;
    protected ?string $siteCode = null;


    // Public Methods
    // =========================================================================

    public function getClientId(): ?string
    {
        return $this->clientId;
    }

    public function setClientId(?string $clientId): NewZealandPost
    {
        $this->clientId = $clientId;
        return $this;
    }

    public function getClientSecret(): ?string
    {
        return $this->clientSecret;
    }

    public function setClientSecret(?string $clientSecret): NewZealandPost
    {
        $this->clientSecret = $clientSecret;
        return $this;
    }

    public function getAccountNumber(): ?string
    {
        return $this->accountNumber;
    }

    public function setAccountNumber(?string $accountNumber): NewZealandPost
    {
        $this->accountNumber = $accountNumber;
        return $this;
    }

    public function getSiteCode(): ?string
    {
        return $this->siteCode;
    }

    public function setSiteCode(?string $siteCode): NewZealandPost
    {
        $this->siteCode = $siteCode;
        return $this;
    }

    /**
     * @throws InvalidRequestException
     */
    public function getRates(Shipment $shipment): ?RateResponse
    {
        $this->validate('clientId', 'clientSecret', 'accountNumber');

        if (self::isDomestic($shipment->getTo()->getCountryCode())) {
            $payload = [
                'pickup_city' => $shipment->getFrom()->getCity(),
                'pickup_postcode' => $shipment->getFrom()->getPostalCode(),
                'pickup_country' => $shipment->getFrom()->getCountryCode(),
                'delivery_city' => $shipment->getTo()->getCity(),
                'delivery_postcode' => $shipment->getTo()->getPostalCode(),
                'delivery_country' => $shipment->getTo()->getCountryCode(),
                'envelope_size' => 'ALL',
                'length' => $shipment->getTotalLength($this, 0),
                'width' => $shipment->getTotalWidth($this, 0),
                'height' => $shipment->getTotalHeight($this, 0),
                'weight' => $shipment->getTotalWeight($this, 0),
            ];

            $request = new Request([
                'method' => 'GET',
                'endpoint' => 'shippingoptions/2.0/domestic',
                'payload' => [
                    'query' => $payload,
                ],
            ]);
        } else {
            $payload = [
                'country_code' => $shipment->getTo()->getCountryCode(),
                'value' => 100,
                'format' => 'json',
                'documents' => '',
                'account_number' => $this->accountNumber,
                'length' => $shipment->getTotalLength($this, 0),
                'width' => $shipment->getTotalWidth($this, 0),
                'height' => $shipment->getTotalHeight($this, 0),
                'weight' => $shipment->getTotalWeight($this, 0),
            ];

            $request = new Request([
                'method' => 'GET',
                'endpoint' => 'shippingoptions/2.0/international',
                'payload' => [
                    'query' => $payload,
                ],
            ]);
        }

        $data = $this->fetchRates($request, function(Response $response) {
            return $response->json();
        });

        $rates = [];

        foreach (Arr::get($data, 'services', []) as $service) {
            $serviceCode = Arr::get($service, 'service_code');
            $serviceName = Arr::get($service, 'description');

            if (self::isDomestic($shipment->getTo()->getCountryCode())) {
                $rate = Arr::get($service, 'price_including_surcharge_and_gst', 0);
            } else {
                $rate = Arr::get($service, 'price_including_gst', 0);
            }

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
        $this->validate('clientId', 'clientSecret');

        $data = [];
        $tracking = [];

        foreach ($trackingNumbers as $trackingNumber) {
            $trackingNumber = str_replace(' ', '', $trackingNumber);

            $request = new Request([
                'method' => 'GET',
                'endpoint' => "parceltrack/3.0/parcels/{$trackingNumber}",
            ]);

            $data = $this->fetchTracking($request, function(Response $response) {
                return $response->json();
            });

            $statusCode = Arr::get($data, 'results.tracking_events.0.event_edifact_code', '');
            $status = $this->_mapTrackingStatus($statusCode);

            $tracking[] = new Tracking([
                'carrier' => $this,
                'response' => $data,
                'trackingNumber' => $trackingNumber,
                'status' => $status,
                'estimatedDelivery' => null,
                'details' => array_map(function($detail) {
                    return new TrackingDetail([
                        'description' => Arr::get($detail, 'event_description', ''),
                        'date' => Arr::get($detail, 'event_datetime', ''),
                    ]);
                }, Arr::get($data, 'results.tracking_events', [])),
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
        $this->validate('clientId', 'clientSecret', 'accountNumber');

        if (self::isDomestic($shipment->getTo()->getCountryCode())) {
            $payload = [
                'carrier' => 'COURIERPOST',
                'account_number' => $this->accountNumber,
                'sender_details' => [
                    'name' => $shipment->getFrom()->getFullName(),
                    'phone' => $shipment->getFrom()->getPhone(),
                    'email' => $shipment->getFrom()->getEmail(),
                    'site_code' => (int)$this->siteCode,
                ],
                'receiver_details' => [
                    'name' => $shipment->getTo()->getFullName(),
                    'phone' => $shipment->getTo()->getPhone(),
                    'email' => $shipment->getTo()->getEmail(),
                ],
                'pickup_address' => [
                    'street' => $shipment->getFrom()->getStreet1(),
                    'suburb' => $shipment->getFrom()->getCity(),
                    'city' => $shipment->getFrom()->getCity(),
                    'postcode' => $shipment->getFrom()->getPostalCode(),
                    'country_code' => $shipment->getFrom()->getCountryCode(),
                ],
                'delivery_address' => [
                    'street' => $shipment->getTo()->getStreet1(),
                    'suburb' => $shipment->getTo()->getCity(),
                    'city' => $shipment->getTo()->getCity(),
                    'postcode' => $shipment->getTo()->getPostalCode(),
                    'country_code' => $shipment->getTo()->getCountryCode(),
                ],
                'parcel_details' => array_map(function(Package $package) use ($rate) {
                    return [
                        'service_code' => $rate->getServiceCode(),
                        'return_indicator' => 'OUTBOUND',
                        'dimensions' => [
                            'length_cm' => (int)$package->getLength(0),
                            'width_cm' => (int)$package->getWidth(0),
                            'height_cm' => (int)$package->getHeight(0),
                            'weight_kg' => (int)$package->getWeight(0),
                        ],
                    ];
                }, $shipment->getPackages()),
            ];
        } else {
            $payload = [
                'carrier' => 'PARCELPOST',
                'sender_details' => [
                    'name' => $shipment->getFrom()->getFullName(),
                    'phone' => $shipment->getFrom()->getPhone(),
                    'email' => $shipment->getFrom()->getEmail(),
                    'site_code' => (int)$this->siteCode,
                ],
                'receiver_details' => [
                    'name' => $shipment->getTo()->getFullName(),
                    'phone' => $shipment->getTo()->getPhone(),
                    'email' => $shipment->getTo()->getEmail(),
                ],
                'pickup_address' => [
                    'street' => $shipment->getFrom()->getStreet1(),
                    'suburb' => $shipment->getFrom()->getCity(),
                    'city' => $shipment->getFrom()->getCity(),
                    'postcode' => $shipment->getFrom()->getPostalCode(),
                    'state' => $shipment->getFrom()->getStateProvince(),
                    'country_code' => $shipment->getFrom()->getCountryCode(),
                ],
                'return_address' => [
                    'street' => $shipment->getFrom()->getStreet1(),
                    'suburb' => $shipment->getFrom()->getCity(),
                    'city' => $shipment->getFrom()->getCity(),
                    'postcode' => $shipment->getFrom()->getPostalCode(),
                    'state' => $shipment->getFrom()->getStateProvince(),
                    'country_code' => $shipment->getFrom()->getCountryCode(),
                ],
                'delivery_address' => [
                    'street' => $shipment->getTo()->getStreet1(),
                    'suburb' => $shipment->getTo()->getCity(),
                    'city' => $shipment->getTo()->getCity(),
                    'postcode' => $shipment->getTo()->getPostalCode(),
                    'state' => $shipment->getTo()->getStateProvince(),
                    'country_code' => $shipment->getTo()->getCountryCode(),
                ],
                'parcel_details' => array_map(function(Package $package) use ($rate) {
                    return [
                        'service_code' => $rate->getServiceCode(),
                        'undeliverable_instructions' => 'RETURN',
                        'insurance_required' => false,
                        'postage_paid_amount' => 0.01,
                        'currency' => 'NZD',
                        'nature_of_transaction_code' => '91',
                        'dimensions' => [
                            'length_cm' => (int)$package->getLength(0),
                            'width_cm' => (int)$package->getWidth(0),
                            'height_cm' => (int)$package->getHeight(0),
                            'weight_kg' => (int)$package->getWeight(0),
                        ],
                        'parcel_contents' => [
                            [
                                'content_number' => 1,
                                'description' => 'Package Desc',
                                'quantity' => 1,
                                'weight_kg' => (int)$package->getWeight(0),
                                'value' => 1,
                            ],
                        ],
                    ];
                }, $shipment->getPackages()),
            ];
        }

        $request = new Request([
            'method' => 'POST',
            'endpoint' => 'parcellabel/v3/labels',
            'payload' => [
                'json' => $payload,
            ],
        ]);

        $data = $this->fetchLabels($request, function(Response $response) {
            return $response->json();
        });

        $labels = [];
        $consignmentId = Arr::get($data, 'consignment_id');

        if ($consignmentId) {
            // Need to provide some time for the label to generate
            sleep(5);

            $request = new Request([
                'method' => 'GET',
                'endpoint' => 'parcellabel/v3/labels/' . $consignmentId . '/status',
            ]);

            $data = $this->fetchLabels($request, function(Response $response) {
                return $response->json();
            });

            foreach (Arr::get($data, 'labels', []) as $label) {
                $errors = Arr::get($label, 'errors');

                if (!$errors) {
                    $labels[] = new Label([
                        'carrier' => $this,
                        'response' => $data,
                        'rate' => $rate,
                        'trackingNumber' => Arr::get($label, 'tracking_reference'),
                        'labelData' => $this->_getLabelData(Arr::get($data, 'consignment_url', '')),
                        'labelMime' => 'application/pdf',
                    ]);
                }
            }
        }

        return new LabelResponse([
            'response' => $data,
            'labels' => $labels,
        ]);
    }

    public function getHttpClient(): HttpClient
    {
        if ($this->isProduction()) {
            $url = 'https://api.nzpost.co.nz/';
        } else {
            $url = 'https://api.uat.nzpost.co.nz/';
        }

        // Fetch an access token first
        $authResponse = Json::decode((string)(new HttpClient())
            ->request('POST', 'https://oauth.nzpost.co.nz/as/token.oauth2', [
                'query' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                ],
            ])->getBody());

        return new HttpClient([
            'base_uri' => $url,
            'headers' => [
                'client_id' => $this->clientId,
                'Authorization' => 'Bearer ' . $authResponse['access_token'] ?? '',
                'Content-Type' => 'application/json',
            ],
        ]);
    }


    // Private Methods
    // =========================================================================

    private function _mapTrackingStatus(string $status): string
    {
        return match ($status) {
            '8', '13', '14', '580', '584', '585', '586', '587', '588', '589', '590', '591', '595', '596', '597', '997', '1013', '1014', '1054', '1060', '1080', '1081', '1146', '54', '55', '56', '57', '58', '59', '60', '61', '63', '67', '77', '78', '80', '85', '95', '141', '142', '143', '144', '145', '146', '147', '148', '149', '200', '201', '202', '203', '542', '544', '546', '547', '548', '549', '550', '552', '553', '554', '555', '556', '558', '559', '560', '561', '562', '564', '571' => Tracking::STATUS_IN_TRANSIT,
            '22' => Tracking::STATUS_DELIVERED,
            '32', '35', '42', '50', '51', '52', '53', '62' => Tracking::STATUS_OUT_FOR_DELIVERY,
            '578' => Tracking::STATUS_RETURN_TO_SENDER,
            default => Tracking::STATUS_UNKNOWN,
        };
    }

    private function _getLabelData(string $url): string
    {
        return base64_encode((new HttpClient())
            ->request('GET', $url, [
                'headers' => [
                    'client_id' => $this->clientId,
                ],
            ])->getBody()->getContents());
    }
}

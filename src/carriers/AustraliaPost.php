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

class AustraliaPost extends AbstractCarrier
{
    // Static Methods
    // =========================================================================

    public static function getName(): string
    {
        return 'Australia Post';
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
        return "https://auspost.com.au/track/track.html?id={$trackingNumber}";
    }

    public static function getServiceCodes(): array
    {
        return [
            'AU' => [
                // Domestic - Parcel
                'AUS_PARCEL_REGULAR' => 'Parcel Post',
                'AUS_PARCEL_REGULAR_SATCHEL_SMALL' => 'Parcel Post Small Satchel',
                'AUS_PARCEL_REGULAR_SATCHEL_MEDIUM' => 'Parcel Post Medium Satchel',
                'AUS_PARCEL_REGULAR_SATCHEL_LARGE' => 'Parcel Post Large Satchel',
                'AUS_PARCEL_REGULAR_SATCHEL_EXTRA_LARGE' => 'Parcel Post Extra Large Satchel',
                'AUS_PARCEL_REGULAR_PACKAGE_SMALL' => 'Parcel Post Small Package',
                'AUS_PARCEL_REGULAR_PACKAGE_MEDIUM' => 'Parcel Post Medium Package',
                'AUS_PARCEL_REGULAR_PACKAGE_LARGE' => 'Parcel Post Large Package',
                'AUS_PARCEL_REGULAR_PACKAGE_EXTRA_LARGE' => 'Parcel Post Extra Large Package',
                'AUS_PARCEL_EXPRESS' => 'Express Post',
                'AUS_PARCEL_EXPRESS_SATCHEL_SMALL' => 'Express Post Small Satchel',
                'AUS_PARCEL_EXPRESS_SATCHEL_MEDIUM' => 'Express Post Medium Satchel',
                'AUS_PARCEL_EXPRESS_SATCHEL_LARGE' => 'Express Post Large Satchel',
                'AUS_PARCEL_EXPRESS_SATCHEL_EXTRA_LARGE' => 'Express Post Extra Large Satchel',
                'AUS_PARCEL_EXPRESS_PACKAGE_SMALL' => 'Express Post Small Package',
                'AUS_PARCEL_EXPRESS_PACKAGE_MEDIUM' => 'Express Post Medium Package',
                'AUS_PARCEL_EXPRESS_PACKAGE_LARGE' => 'Express Post Large Package',
                'AUS_PARCEL_EXPRESS_PACKAGE_EXTRA_LARGE' => 'Express Post Extra Large Package',
                'AUS_PARCEL_COURIER' => 'Courier Post',
                'AUS_PARCEL_COURIER_SATCHEL_MEDIUM' => 'Courier Post Assessed Medium Satchel',

                // Domestic - Letter
                'AUS_LETTER_REGULAR_SMALL' => 'Letter Regular Small',
                'AUS_LETTER_REGULAR_MEDIUM' => 'Letter Regular Medium',
                'AUS_LETTER_REGULAR_LARGE' => 'Letter Regular Large',
                'AUS_LETTER_REGULAR_LARGE_125' => 'Letter Regular Large (125g)',
                'AUS_LETTER_REGULAR_LARGE_250' => 'Letter Regular Large (250g)',
                'AUS_LETTER_REGULAR_LARGE_500' => 'Letter Regular Large (500g)',
                'AUS_LETTER_EXPRESS_SMALL' => 'Letter Express Small',
                'AUS_LETTER_EXPRESS_MEDIUM' => 'Letter Express Medium',
                'AUS_LETTER_EXPRESS_LARGE' => 'Letter Express Large',
                'AUS_LETTER_EXPRESS_LARGE_125' => 'Letter Express Large (125g)',
                'AUS_LETTER_EXPRESS_LARGE_250' => 'Letter Express Large (250g)',
                'AUS_LETTER_EXPRESS_LARGE_500' => 'Letter Express Large (500g)',
                'AUS_LETTER_PRIORITY_SMALL' => 'Letter Priority Small',
                'AUS_LETTER_PRIORITY_MEDIUM' => 'Letter Priority Medium',
                'AUS_LETTER_PRIORITY_LARGE' => 'Letter Priority Large',
                'AUS_LETTER_PRIORITY_LARGE_125' => 'Letter Priority Large (125g)',
                'AUS_LETTER_PRIORITY_LARGE_250' => 'Letter Priority Large (250g)',
                'AUS_LETTER_PRIORITY_LARGE_500' => 'Letter Priority Large (500g)',
            ],

            'international' => [
                // International - Parcel
                'INT_PARCEL_STD_OWN_PACKAGING' => 'International Standard',
                'INT_PARCEL_EXP_OWN_PACKAGING' => 'International Express',
                'INT_PARCEL_COR_OWN_PACKAGING' => 'International Courier',
                'INT_PARCEL_AIR_OWN_PACKAGING' => 'International Economy Air',
                'INT_PARCEL_SEA_OWN_PACKAGING' => 'International Economy Sea',

                // International - Letter
                'INT_LETTER_REG_SMALL_ENVELOPE' => 'International Letter DL',
                'INT_LETTER_REG_LARGE_ENVELOPE' => 'International Letter B4',
                'INT_LETTER_EXP_OWN_PACKAGING' => 'International Letter Express',
                'INT_LETTER_COR_OWN_PACKAGING' => 'International Letter Courier',
                'INT_LETTER_AIR_OWN_PACKAGING_LIGHT' => 'International Letter Air Light',
                'INT_LETTER_AIR_OWN_PACKAGING_MEDIUM' => 'International Letter Air Medium',
                'INT_LETTER_AIR_OWN_PACKAGING_HEAVY' => 'International Letter Air Heavy',
            ],
        ];
    }


    // Properties
    // =========================================================================

    protected ?string $apiKey = null;
    protected ?string $password = null;
    protected ?string $accountNumber = null;

    protected array $pickupMethods = [
        'Daily Pickup' => '01',
        'Customer Counter' => '03',
        'One Time Pickup' => '06',
        'On Call Air Pickup' => '07',
        'Letter Center' => '19',
        'Air Service Center' => '20',
    ];

    protected array $packagingTypes = [
        'Unknown' => '00',
        'UPS Letter' => '01',
        'Customer Package' => '02',
        'UPS Tube' => '03',
        'UPS Pak' => '04',
        'UPS Express Box' => '21',
        'UPS 25kg Box' => '24',
        'UPS 10kg Box' => '25',
    ];


    // Public Methods
    // =========================================================================

    public function __construct(array $config = [])
    {
        // Ensure the account number is zero padded 10 digits
        if (array_key_exists('accountNumber', $config) && $config['accountNumber']) {
            $config['accountNumber'] = str_pad($config['accountNumber'], 10, '0', STR_PAD_LEFT);
        }

        parent::__construct($config);
    }

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    public function setApiKey(?string $apiKey): AustraliaPost
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): AustraliaPost
    {
        $this->password = $password;
        return $this;
    }

    public function getAccountNumber(): ?string
    {
        return $this->accountNumber;
    }

    public function setAccountNumber(?string $accountNumber): AustraliaPost
    {
        $this->accountNumber = $accountNumber;
        return $this;
    }

    /**
     * @throws InvalidRequestException
     */
    public function getRates(Shipment $shipment): ?RateResponse
    {
        // If using the Shipping & Tracking API, it's a whole other API structure.
        if ($this->accountNumber) {
            $this->validate('apiKey', 'password', 'accountNumber');

            $payload = [
                'from' => [
                    'postcode' => $shipment->getFrom()->getPostalCode(),
                ],
                'to' => [
                    'postcode' => $shipment->getTo()->getPostalCode(),
                ],
                'items' => array_map(function($package) {
                    return [
                        'length' => $package->getLength(),
                        'width' => $package->getWidth(),
                        'height' => $package->getHeight(),
                        'weight' => $package->getWeight(),
                    ];
                }, $shipment->getPackages()),
            ];

            $request = new Request([
                'method' => 'POST',
                'endpoint' => 'shipping/v1/prices/items',
                'payload' => [
                    'json' => $payload,
                ],
            ]);

            $data = $this->fetchRates($request, function(Response $response) {
                return $response->json();
            });

            $rates = [];

            foreach (Arr::get($data, 'items.0.prices', []) as $service) {
                $serviceCode = Arr::get($service, 'product_id', '');
                $rate = Arr::get($service, 'calculated_price', 0);

                $serviceRegion = Arr::get(self::getServiceCodes(), $shipment->getFrom()->getCountryCode(), Arr::get(self::getServiceCodes(), 'international'));
                $serviceName = Arr::get($serviceRegion, $serviceCode, Arr::get($service, 'product_type', ''));

                $rates[] = new Rate([
                    'carrier' => $this,
                    'response' => $service,
                    'serviceName' => $serviceName,
                    'serviceCode' => $serviceCode,
                    'rate' => $rate,
                ]);
            }
        } else {
            $this->validate('apiKey');

            if (self::isDomestic($shipment->getTo()->getCountryCode())) {
                $endpoint = 'postage/parcel/domestic/service.json';

                $payload = [
                    'from_postcode' => $shipment->getFrom()->getPostalCode(),
                    'to_postcode' => $shipment->getTo()->getPostalCode(),
                    'length' => $shipment->getTotalLength($this),
                    'width' => $shipment->getTotalWidth($this),
                    'height' => $shipment->getTotalHeight($this),
                    'weight' => $shipment->getTotalWeight($this),
                ];
            } else {
                $endpoint = 'postage/parcel/international/service.json';

                $payload = [
                    'country_code' => $shipment->getTo()->getCountryCode(),
                    'weight' => $shipment->getTotalWeight($this),
                ];
            }

            $request = new Request([
                'httpClient' => $this->getRatesHttpClient(),
                'method' => 'GET',
                'endpoint' => $endpoint,
                'payload' => [
                    'query' => $payload,
                ],
            ]);

            $data = $this->fetchRates($request, function(Response $response) {
                return $response->json();
            });

            $rates = [];

            $services = Arr::get($data, 'services.service', []);

            // Normalise the services from AusPost to ensure we're always dealing with a collection
            if (!isset($services[0])) {
                $services = [$services];
            }

            foreach ($services as $service) {
                $serviceCode = Arr::get($service, 'code', '');
                $rate = Arr::get($service, 'price', 0);

                $serviceRegion = Arr::get(self::getServiceCodes(), $shipment->getFrom()->getCountryCode(), Arr::get(self::getServiceCodes(), 'international'));
                $serviceName = Arr::get($serviceRegion, $serviceCode, Arr::get($service, 'name', ''));

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
        $this->validate('apiKey');

        $request = new Request([
            'method' => 'GET',
            'endpoint' => 'shipping/v1/track',
            'payload' => [
                'query' => [
                    'tracking_ids' => implode(',', array_map(function($trackingNumber) {
                        return str_replace(' ', '', $trackingNumber);
                    }, $trackingNumbers)),
                ],
            ],
        ]);

        $data = $this->fetchTracking($request, function(Response $response) {
            return $response->json();
        });

        $tracking = [];

        foreach (Arr::get($data, 'tracking_results', []) as $result) {
            $trackingNumber = Arr::get($result, 'tracking_id', '');
            $statusCode = Arr::get($result, 'status', '');
            $errorCode = Arr::get($result, 'errors.0.code', '');

            $status = $this->_mapTrackingStatus($statusCode);

            if ($errorCode) {
                $status = $this->_mapTrackingErrorStatus($errorCode);
            }

            $tracking[] = new Tracking([
                'carrier' => $this,
                'response' => $result,
                'trackingNumber' => $trackingNumber,
                'status' => $status,
                'estimatedDelivery' => null,
                'details' => array_map(function($detail) {
                    return new TrackingDetail([
                        'location' => Arr::get($detail, 'location', ''),
                        'description' => Arr::get($detail, 'description', ''),
                        'date' => Arr::get($detail, 'date', ''),
                    ]);
                }, Arr::get($result, 'trackable_items.0.events', [])),
                'errors' => array_map(function($error) {
                    return [
                        'errorCode' => Arr::get($error, 'code', ''),
                        'description' => Arr::get($error, 'message', ''),
                    ];
                }, Arr::get($result, 'errors', [])),
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
        $this->validate('apiKey', 'password', 'accountNumber');

        $payload = [
            'shipments' => [
                [
                    'email_tracking_enabled' => true,
                    'from' => [
                        'name' => $shipment->getFrom()->getFullName(),
                        'lines' => array_filter([$shipment->getFrom()->getStreet1(), $shipment->getFrom()->getStreet2()]),
                        'suburb' => $shipment->getFrom()->getCity(),
                        'state' => $shipment->getFrom()->getStateProvince(),
                        'postcode' => $shipment->getFrom()->getPostalCode(),
                        'country' => $shipment->getFrom()->getCountryCode(),
                        'email' => $shipment->getFrom()->getEmail(),
                    ],
                    'to' => [
                        'name' => $shipment->getTo()->getFullName(),
                        'lines' => array_filter([$shipment->getTo()->getStreet1(), $shipment->getTo()->getStreet2()]),
                        'suburb' => $shipment->getTo()->getCity(),
                        'state' => $shipment->getTo()->getStateProvince(),
                        'postcode' => $shipment->getTo()->getPostalCode(),
                        'country' => $shipment->getTo()->getCountryCode(),
                        'email' => $shipment->getTo()->getEmail(),
                    ],
                    'items' => array_map(function($package) use ($rate) {
                        return [
                            'product_id' => $rate->getServiceCode(),
                            'length' => $package->getLength(),
                            'width' => $package->getWidth(),
                            'height' => $package->getHeight(),
                            'weight' => $package->getWeight(),
                        ];
                    }, $shipment->getPackages()),
                ],
            ],
        ];

        $request = new Request([
            'method' => 'POST',
            'endpoint' => 'shipping/v1/shipments',
            'payload' => [
                'json' => $payload,
            ],
        ]);

        $data = $this->fetchRates($request, function(Response $response) {
            return $response->json();
        });

        $shipmentIds = [];
        $trackingNumbers = [];

        foreach (Arr::get($data, 'shipments', []) as $shipmentObject) {
            $shipmentIds[] = [
                'shipment_id' => Arr::get($shipmentObject, 'shipment_id'),
            ];

            foreach (Arr::get($shipmentObject, 'items', []) as $shipmentItem) {
                $shipmentItemId = Arr::get($shipmentItem, 'item_id');
                $trackingNumber = Arr::get($shipmentItem, 'tracking_details.consignment_id');

                $trackingNumbers[$shipmentItemId] = $trackingNumber;
            }
        }

        $data = [];
        $labels = [];

        // Fetch the labels for the shipment
        if ($shipmentIds) {
            $types = [
                'Parcel Post',
                'Express Post',
            ];

            $payload = [
                'wait_for_label_url' => true,
                'preferences' => [
                    'type' => 'PRINT',
                    'format' => 'PDF',
                    'groups' => array_map(function($type) use ($options) {
                        return [
                            'group' => $type,
                            'layout' => Arr::get($options, 'layoutType', 'A4-1pp'),
                            'branded' => Arr::get($options, 'branded', true),
                            'left_offset' => Arr::get($options, 'leftOffset', 0),
                            'top_offset' => Arr::get($options, 'topOffset', 0),
                        ];
                    }, $types),
                ],
                'shipments' => $shipmentIds,
            ];

            $request = new Request([
                'method' => 'POST',
                'endpoint' => 'shipping/v1/labels',
                'payload' => [
                    'json' => $payload,
                ],
            ]);

            $data = $this->fetchRates($request, function(Response $response) {
                return $response->json();
            });

            foreach (Arr::get($data, 'labels', []) as $label) {
                $shipmentItemId = Arr::get($label, 'shipments.0.items.0.item_id');
                $trackingNumber = Arr::get($trackingNumbers, $shipmentItemId);

                $labels[] = new Label([
                    'carrier' => $this,
                    'response' => $label,
                    'rate' => $rate,
                    'trackingNumber' => $trackingNumber,
                    'labelId' => Arr::get($label, 'request_id'),
                    'labelData' => $this->_getLabelData(Arr::get($label, 'url')),
                    'labelMime' => 'application/pdf',
                ]);
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
            $url = 'https://digitalapi.auspost.com.au/';
        } else {
            $url = 'https://digitalapi.auspost.com.au/test/';
        }

        return new HttpClient([
            'base_uri' => $url,
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($this->apiKey . ':' . $this->password),
                'Account-Number' => $this->accountNumber,
                'AUTH-KEY' => $this->apiKey,
            ],
        ]);
    }

    public function getRatesHttpClient(): HttpClient
    {
        return new HttpClient([
            'base_uri' => 'https://digitalapi.auspost.com.au/',
            'headers' => [
                'AUTH-KEY' => $this->apiKey,
            ],
        ]);
    }


    // Private Methods
    // =========================================================================

    private function _mapTrackingStatus(string $status): string
    {
        return match (strtolower($status)) {
            'created', 'sealed' => Tracking::STATUS_PENDING,
            'initiated', 'awaiting collection' => Tracking::STATUS_PRE_TRANSIT,
            'in transit', 'held by courier', 'article damaged', 'possible delay' => Tracking::STATUS_IN_TRANSIT,
            'delivered' => Tracking::STATUS_DELIVERED,
            'unsuccessful pickup', 'cannot be delivered' => Tracking::STATUS_FAILURE,
            'cancelled' => Tracking::STATUS_CANCELLED,
            default => Tracking::STATUS_UNKNOWN,
        };
    }

    private function _mapTrackingErrorStatus(string $status): string
    {
        return match (strtolower($status)) {
            'esb-10001', '51104', 'esb-10002' => Tracking::STATUS_NOT_FOUND,
            'esb-20010', '51100', 'esb-20050' => Tracking::STATUS_FAILURE,
            default => Tracking::STATUS_UNKNOWN,
        };
    }

    private function _getLabelData(string $url): string
    {
        return base64_encode((new HttpClient())->request('GET', $url)->getBody()->getContents());
    }
}

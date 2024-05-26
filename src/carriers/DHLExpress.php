<?php
namespace verbb\shippy\carriers;

use DateTime;
use Illuminate\Support\Arr;
use verbb\shippy\exceptions\InvalidRequestException;
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

class DHLExpress extends AbstractCarrier
{
    // Static Methods
    // =========================================================================

    public static function getName(): string
    {
        return 'DHL Express';
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
        return "https://www.dhl.com/content/g0/en/express/tracking.shtml?brand=DHL&AWB=${trackingNumber}";
    }

    public static function getServiceCodes(): array
    {
        return [
            // Documents
            '2' => 'Easy Shop',
            '5' => 'Sprintline',
            '6' => 'Secureline',
            '7' => 'Express Easy',
            '9' => 'Europack',
            'B' => 'Break Bulk Express',
            'C' => 'Medical Express',
            'D' => 'Express Worldwide',
            'U' => 'Express Worldwide',
            'K' => 'Express 9:00',
            'L' => 'Express 10:30',
            'G' => 'Domestic Economy Select',
            'W' => 'Economy Select',
            'I' => 'Break Bulk Economy',
            'N' => 'Domestic Express',
            'O' => 'Others',
            'R' => 'Globalmail Business',
            'S' => 'Same Day',
            'T' => 'Express 12:00',
            'X' => 'Express Envelope',

            // Non-documents
            '1' => 'Customer Services',
            '3' => 'Easy Shop',
            '4' => 'Jetline',
            '8' => 'Express Easy',
            'P' => 'Express Worldwide',
            'Q' => 'Medical Express',
            'E' => 'Express 9:00',
            'F' => 'Freight Worldwide',
            'H' => 'Economy Select',
            'J' => 'Jumbo Box',
            'M' => 'Express 10:30',
            'V' => 'Europack',
            'Y' => 'Express 12:00',
        ];
    }


    // Properties
    // =========================================================================

    protected ?string $clientId = null;
    protected ?string $username = null;
    protected ?string $password = null;
    protected ?string $accountNumber = null;
    protected ?DateTime $shipDateTime = null;


    // Public Methods
    // =========================================================================

    public function getClientId(): ?string
    {
        return $this->clientId;
    }

    public function setClientId(?string $clientId): DHLExpress
    {
        $this->clientId = $clientId;
        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): DHLExpress
    {
        $this->username = $username;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): DHLExpress
    {
        $this->password = $password;
        return $this;
    }

    public function getAccountNumber(): ?string
    {
        return $this->accountNumber;
    }

    public function setAccountNumber(?string $accountNumber): DHLExpress
    {
        $this->accountNumber = $accountNumber;
        return $this;
    }

    public function getShipDateTime(): ?DateTime
    {
        return $this->shipDateTime;
    }

    public function setShipDateTime(?DateTime $shipDateTime): DHLExpress
    {
        $this->shipDateTime = $shipDateTime;
        return $this;
    }

    /**
     * @throws InvalidRequestException
     */
    public function getRates(Shipment $shipment): ?RateResponse
    {
        $this->validate('username', 'password', 'accountNumber');

        $payload = [
            'customerDetails' => [
                'shipperDetails' => array_filter([
                    'addressLine1' => $shipment->getFrom()->getStreet1(),
                    'addressLine2' => $shipment->getFrom()->getStreet2(),
                    'cityName' => $shipment->getFrom()->getCity(),
                    'postalCode' => $shipment->getFrom()->getPostalCode(),
                    'countryCode' => $shipment->getFrom()->getCountryCode(),
                ]),
                'receiverDetails' => array_filter([
                    'addressLine1' => $shipment->getTo()->getStreet1(),
                    'addressLine2' => $shipment->getTo()->getStreet2(),
                    'cityName' => $shipment->getTo()->getCity(),
                    'postalCode' => $shipment->getTo()->getPostalCode(),
                    'countryCode' => $shipment->getTo()->getCountryCode(),
                ]),
            ],
            'accounts' => [
                [
                    'typeCode' => 'shipper',
                    'number' => $this->accountNumber,
                ],
            ],
            'payerCountryCode' => $shipment->getFrom()->getCountryCode(),
            'plannedShippingDateAndTime' => ($this->shipDateTime ?? new DateTime())->format('Y-m-d\TH:i:s \G\M\TP'),
            'unitOfMeasurement' => 'metric',
            'isCustomsDeclarable' => false,
            'estimatedDeliveryDate' => [
                'isRequested' => true,
                'typeCode' => 'QDDC',
            ],
            'returnStandardProductsOnly' => false,
            'nextBusinessDay' => true,
            'productTypeCode' => 'all',
            'packages' => array_map(function(Package $package) {
                return [
                    'typeCode' => '3BX',
                    'weight' => (int)$package->getWeight(0),
                    'dimensions' => [
                        'length' => (int)$package->getLength(0),
                        'width' => (int)$package->getWidth(0),
                        'height' => (int)$package->getHeight(0),
                    ],
                ];
            }, $shipment->getPackages()),
        ];

        $request = new Request([
            'endpoint' => 'rates',
            'payload' => [
                'json' => $payload,
            ],
        ]);

        $data = $this->fetchRates($request, function(Response $response) {
            return $response->json();
        });

        $rates = [];

        foreach (Arr::get($data, 'products', []) as $service) {
            $serviceCode = Arr::get($service, 'productCode');
            $serviceName = Arr::get($service, 'productName');
            $rate = Arr::get($service, 'totalPrice.0.price', 0);
            $currency = Arr::get($service, 'totalPrice.0.currency');

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
        $this->validate('clientId');

        $data = [];
        $tracking = [];

        foreach ($trackingNumbers as $trackingNumber) {
            $trackingNumber = str_replace(' ', '', $trackingNumber);

            $request = new Request([
                'httpClient' => $this->getTrackingHttpClient(),
                'method' => 'GET',
                'endpoint' => 'track/shipments',
                'payload' => [
                    'query' => [
                        'trackingNumber' => $trackingNumber,
                    ],
                ],
            ]);

            $data = $this->fetchTracking($request, function(Response $response) {
                return $response->json();
            });

            $statusCode = Arr::get($data, 'shipments.0.status.statusCode', '');
            $status = $this->_mapTrackingStatus($statusCode);

            $tracking[] = new Tracking([
                'carrier' => $this,
                'response' => $data,
                'trackingNumber' => $trackingNumber,
                'status' => $status,
                'estimatedDelivery' => null,
                'details' => array_map(function($detail) {
                    $location = array_filter([
                        Arr::get($detail, 'location.address.addressLocality', ''),
                        Arr::get($detail, 'location.address.postalCode', ''),
                        Arr::get($detail, 'location.address.countryCode', ''),
                    ]);

                    return new TrackingDetail([
                        'location' => implode(' ', $location),
                        'description' => Arr::get($detail, 'description', ''),
                        'date' => Arr::get($detail, 'timestamp', ''),
                    ]);
                }, Arr::get($data, 'shipments.0.events', [])),
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
        $this->validate('username', 'password', 'accountNumber');

        $payload = array_replace_recursive([
            'customerDetails' => [
                'shipperDetails' => [
                    'postalAddress' => array_filter([
                        'addressLine1' => $shipment->getFrom()->getStreet1(),
                        'addressLine2' => $shipment->getFrom()->getStreet2(),
                        'cityName' => $shipment->getFrom()->getCity(),
                        'postalCode' => $shipment->getFrom()->getPostalCode(),
                        'countryCode' => $shipment->getFrom()->getCountryCode(),
                    ]),
                    'contactInformation' => [
                        'email' => $shipment->getFrom()->getEmail(),
                        'phone' => $shipment->getFrom()->getPhone(),
                        'fullName' => $shipment->getFrom()->getFullName(),
                        'companyName' => $shipment->getFrom()->getCompanyName(),
                    ],
                ],
                'receiverDetails' => [
                    'postalAddress' => array_filter([
                        'addressLine1' => $shipment->getTo()->getStreet1(),
                        'addressLine2' => $shipment->getTo()->getStreet2(),
                        'cityName' => $shipment->getTo()->getCity(),
                        'postalCode' => $shipment->getTo()->getPostalCode(),
                        'countryCode' => $shipment->getTo()->getCountryCode(),
                    ]),
                    'contactInformation' => [
                        'email' => $shipment->getTo()->getEmail(),
                        'phone' => $shipment->getTo()->getPhone(),
                        'fullName' => $shipment->getTo()->getFullName(),
                        'companyName' => $shipment->getTo()->getCompanyName(),
                    ],
                ],
            ],
            'accounts' => [
                [
                    'typeCode' => 'shipper',
                    'number' => $this->accountNumber,
                ],
            ],
            'plannedShippingDateAndTime' => ($this->shipDateTime ?? new DateTime())->format('Y-m-d\TH:i:s \G\M\TP'),
            'estimatedDeliveryDate' => [
                'isRequested' => true,
                'typeCode' => 'QDDC',
            ],
            'content' => [
                'isCustomsDeclarable' => false,
                'description' => 'Shipment Description',
                'incoterm' => 'DAP',
                'unitOfMeasurement' => 'metric',
                'packages' => array_map(function(Package $package) {
                    return [
                        'typeCode' => '3BX',
                        'weight' => (int)$package->getWeight(0),
                        'dimensions' => [
                            'length' => (int)$package->getLength(0),
                            'width' => (int)$package->getWidth(0),
                            'height' => (int)$package->getHeight(0),
                        ],
                    ];
                }, $shipment->getPackages()),
            ],
            'productCode' => $rate->getServiceCode(),
            'getRateEstimates' => false,
            'getTransliteratedResponse' => false,
            'outputImageProperties' => [
                'printerDPI' => 300,
                'encodingFormat' => 'pdf',
                'imageOptions' => [
                    [
                        'typeCode' => 'label',
                        'templateName' => 'ECOM26_84_001',
                        'isRequested' => true,
                    ],
                ],
                'splitTransportAndWaybillDocLabels' => true,
                'allDocumentsInOneImage' => false,
                'splitDocumentsByPages' => true,
                'splitInvoiceAndReceipt' => true,
                'receiptAndLabelsInOneImage' => false,
            ],
            'pickup' => [
                'isRequested' => false,
            ],
        ], $options);

        $request = new Request([
            'endpoint' => 'shipments',
            'payload' => [
                'json' => $payload,
            ],
        ]);

        // Custom parsing for multipart response
        $data = $this->fetchLabels($request, function(Response $response) {
            return $response->json();
        });

        $labels = [];

        $labels[] = new Label([
            'carrier' => $this,
            'response' => $data,
            'rate' => $rate,
            'trackingNumber' => Arr::get($data, 'shipmentTrackingNumber'),
            'labelData' => Arr::get($data, 'documents.0.content', ''),
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
            $url = 'https://express.api.dhl.com/mydhlapi/';
        } else {
            $url = 'https://express.api.dhl.com/mydhlapi/test/';
        }

        return new HttpClient([
            'base_uri' => $url,
            'auth' => [$this->username, $this->password],
        ]);
    }

    public function getTrackingHttpClient(): HttpClient
    {
        return new HttpClient([
            'base_uri' => 'https://api-eu.dhl.com/',
            'headers' => [
                'DHL-API-Key' => $this->clientId,
                'Accept' => 'application/json',
            ],
        ]);
    }


    // Private Methods
    // =========================================================================

    private function _mapTrackingStatus(string $status): string
    {
        return match ($status) {
            'pre-transit' => Tracking::STATUS_PRE_TRANSIT,
            'transit' => Tracking::STATUS_IN_TRANSIT,
            'delivered' => Tracking::STATUS_DELIVERED,
            'failure' => Tracking::STATUS_FAILURE,
            default => Tracking::STATUS_UNKNOWN,
        };
    }
}

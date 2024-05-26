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

class Bring extends AbstractCarrier
{
    // Static Methods
    // =========================================================================

    public static function getName(): string
    {
        return 'Bring';
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
        return "https://tracking.bring.se/tracking.html?q=${trackingNumber}";
    }

    public static function getServiceCodes(): array
    {
        return [
            // https://developer.bring.com/files/Labelspecifications_for_Bring_v_4_991.pdf
            // Bring Parcels AB
            '0330' => 'Business Parcel',
            '0331' => 'Business Parcel Return',
            '0332' => 'Business Parcel Bulk',
            '0333' => 'Business Parcel Return Bulk',
            '0334' => 'Express Nordic 0900 Bulk',
            '0335' => 'Express Nordic 0900',
            '0336' => 'Business Pallet',
            '0339' => 'Express Nordic 0900 Pallet',
            '0340' => 'PickUp Parcel',
            '0341' => 'PickUp Parcel Return',
            '0342' => 'PickUp Parcel Bulk',
            '0343' => 'PickUp Parcel Return Bulk',
            '0345' => 'Home Delivery Mailbox',
            '0348' => 'Home Delivery Parcel Return',
            '0349' => 'Home Delivery Parcel',
            
            // Parcel Domestic
            '1000' => 'Bedriftspakke',
            '1002' => 'Bedriftspakke Ekspress-Over natten',
            '1020' => 'Postens pallelaster',
            '1202' => 'Klimanøytral Servicepakke',
            '1206' => 'Bedriftspakke postkontor',
            '1312' => 'På Døren Prosjekt',
            '1736' => 'På Døren',
            '1885' => 'Abonnementstransport',
            '1988' => 'Bedriftspakke Flerkolli',
            '3110' => 'Minipakke',

            '3570' => 'Pakke i postkassen',
            '3584' => 'Pakke i postkassen (sporbar)',
            '4850' => 'Ekspress neste dag',
            '5000' => 'Pakke til bedrift',
            '5100' => 'Stykkgods til bedrift',
            '5300' => 'Partigods til bedrift',
            '5400' => 'Pall til bedrift',
            '5600' => 'Pakke levert hjem',
            '5800' => 'Pakke til hentested',
            '9000' => 'Retur pakke fra bedrift',
            '9100' => 'Retur stykkgods fra bedrift',
            '9300' => 'Retur fra hentested',
            '9600' => 'Returekspress',
            'MAIL' => 'Brev',
            'VIP25' => 'Budbil VIP',
        ];
    }


    // Properties
    // =========================================================================

    protected ?string $username = null;
    protected ?string $apiKey = null;
    protected ?string $customerNumber = null;
    protected ?string $clientUrl = null;


    // Public Methods
    // =========================================================================

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): Bring
    {
        $this->username = $username;
        return $this;
    }

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    public function setApiKey(?string $apiKey): Bring
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    public function getCustomerNumber(): ?string
    {
        return $this->customerNumber;
    }

    public function setCustomerNumber(?string $customerNumber): Bring
    {
        $this->customerNumber = $customerNumber;
        return $this;
    }

    public function getClientUrl(): ?string
    {
        return $this->clientUrl;
    }

    public function setClientUrl(?string $clientUrl): Bring
    {
        $this->clientUrl = $clientUrl;
        return $this;
    }

    /**
     * @throws InvalidRequestException
     */
    public function getRates(Shipment $shipment): ?RateResponse
    {
        $this->validate('username', 'apiKey');

        $payload = [
            'fromPostalCode' => $shipment->getFrom()->getPostalCode(),
            'fromCountryCode' => $shipment->getFrom()->getCountryCode(),
            'toPostalCode' => $shipment->getTo()->getPostalCode(),
            'toCountryCode' => $shipment->getTo()->getCountryCode(),

            // Tells whether the parcel is delivered at a post office when it is shipped.
            // A surcharge will be applied for SERVICEPAKKE and BPAKKE_DOR-DOR
            'postingAtPostoffice' => false,
            'withExpectedDelivery' => true,
            'withGuiInformation' => true,
            'withPrice' => true,

            'products' => array_map(function($code) {
                return ['id' => (string)$code];
            }, array_keys(self::getServiceCodes())),

            'packages' => array_map(function($key, Package $package) {
                return [
                    'id' => ($key + 1),
                    'grossWeight' => $package->getWeight(),
                    'height' => $package->getHeight(),
                    'length' => $package->getLength(),
                    'width' => $package->getWidth(),
                ];
            }, array_keys($shipment->getPackages()), $shipment->getPackages()),
        ];

        $request = new Request([
            'method' => 'POST',
            'endpoint' => 'shippingguide/api/v2/products',
            'payload' => [
                'json' => ['consignments' => [$payload]],
            ],
        ]);

        $data = $this->fetchRates($request, function(Response $response) {
            $json = $response->json();

            // Parse errors, which aren't flagged via HTTP status codes
            foreach (Arr::get($json, 'consignments.0.products', []) as $service) {
                $serviceCode = Arr::get($service, 'id');

                foreach (Arr::get($service, 'errors', []) as $serviceError) {
                    $errorCode = Arr::get($serviceError, 'code');
                    $errorMessage = Arr::get($serviceError, 'description');

                    if ($errorCode || $errorMessage) {
                        $json['__errors'][$serviceCode] = implode(': ', [$errorCode, $errorMessage]);
                    }
                }
            }

            return $json;
        });

        $rates = [];

        foreach (Arr::get($data, 'consignments.0.products', []) as $service) {
            $serviceCode = Arr::get($service, 'id');
            $rate = Arr::get($service, 'price.listPrice.priceWithoutAdditionalServices.amountWithVAT', 0);

            $rates[] = new Rate([
                'carrier' => $this,
                'response' => $service,
                'serviceName' => Arr::get($service, 'guiInformation.getName', ''),
                'serviceCode' => $serviceCode,
                'deliveryDate' => Arr::get($service, 'expectedDelivery.formattedExpectedDeliveryDate', ''),
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
        $this->validate('username', 'apiKey');

        $data = [];
        $tracking = [];

        foreach ($trackingNumbers as $trackingNumber) {
            $trackingNumber = str_replace(' ', '', $trackingNumber);

            $request = new Request([
                'method' => 'GET',
                'endpoint' => 'tracking/api/v2/tracking.json',
                'payload' => [
                    'query' => [
                        'q' => $trackingNumber,
                    ],
                ],
            ]);

            $data = $this->fetchTracking($request, function(Response $response) {
                return $response->json();
            });

            $trackingData = Arr::get($data, 'consignmentSet.0.packageSet.0', []);

            if ($trackingData) {
                $statusCode = Arr::get($trackingData, 'eventSet.0.status', '');
                $status = $this->_mapTrackingStatus($statusCode);

                $tracking[] = new Tracking([
                    'carrier' => $this,
                    'response' => $trackingData,
                    'trackingNumber' => $trackingNumber,
                    'status' => $status,
                    'estimatedDelivery' => null,
                    'details' => array_map(function($detail) {
                        $location = array_filter([
                            Arr::get($detail, 'city', ''),
                            Arr::get($detail, 'postalCode', ''),
                            Arr::get($detail, 'country', ''),
                        ]);

                        return new TrackingDetail([
                            'location' => $location,
                            'description' => Arr::get($detail, 'description', ''),
                            'date' => Arr::get($detail, 'dateIso', ''),
                        ]);
                    }, Arr::get($data, 'eventSet', [])),
                ]);
            }
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
        $this->validate('username', 'apiKey', 'customerNumber');

        $shipDate = (new DateTime())->modify('+1 day')->format('c');

        $payload = [
            'schemaVersion' => 1,
            'testIndicator' => !$this->isProduction(),
            'consignments' => [
                [
                    'packages' => array_map(function($package) {
                        return [
                            'packageType' => 'hd_half',
                            'weightInKg' => $package->getWeight(),
                            'dimensions' => [
                                'heightInCm' => $package->getHeight(),
                                'lengthInCm' => $package->getLength(),
                                'widthInCm' => $package->getWidth(),
                            ],
                        ];
                    }, $shipment->getPackages()),
                    'parties' => [
                        'recipient' => [
                            'addressLine' => $shipment->getTo()->getStreet1(),
                            'addressLine2' => $shipment->getTo()->getStreet2(),
                            'city' => $shipment->getTo()->getCity(),
                            'contact' => [
                                'email' => $shipment->getTo()->getEmail(),
                            ],
                            'name' => $shipment->getTo()->getFullName(),
                            'countryCode' => $shipment->getTo()->getCountryCode(),
                            'postalCode' => $shipment->getTo()->getPostalCode(),
                        ],
                        'sender' => [
                            'addressLine' => $shipment->getFrom()->getStreet1(),
                            'addressLine2' => $shipment->getFrom()->getStreet2(),
                            'city' => $shipment->getFrom()->getCity(),
                            'contact' => [
                                'email' => $shipment->getFrom()->getEmail(),
                                'phoneNumber' => $shipment->getFrom()->getPhone(),
                            ],
                            'name' => $shipment->getFrom()->getFullName(),
                            'countryCode' => $shipment->getFrom()->getCountryCode(),
                            'postalCode' => $shipment->getFrom()->getPostalCode(),
                        ],
                    ],
                    'product' => [
                        'customerNumber' => $this->customerNumber,
                        'id' => $rate->getServiceCode(),

                    ],
                    'shippingDateTime' => $shipDate,
                ],
            ],
        ];

        $request = new Request([
            'method' => 'POST',
            'endpoint' => 'booking-api/api/booking',
            'payload' => [
                'json' => $payload,
            ],
        ]);

        $data = $this->fetchLabels($request, function(Response $response) {
            return $response->json();
        });

        $labels = [];

        foreach (Arr::get($data, 'consignments', []) as $consignment) {
            $errors = Arr::get($consignment, 'errors');

            if (!$errors) {
                $labels[] = new Label([
                    'carrier' => $this,
                    'response' => $consignment,
                    'rate' => $rate,
                    'trackingNumber' => Arr::get($consignment, 'confirmation.consignmentNumber'),
                    'labelData' => $this->_getLabelData(Arr::get($consignment, 'confirmation.links.labels', '')),
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
        return new HttpClient([
            'base_uri' => 'https://api.bring.com/',
            'headers' => [
                'X-MyBring-API-Uid' => $this->username,
                'X-MyBring-API-Key' => $this->apiKey,
                'X-Bring-HttpClient-URL' => $this->clientUrl,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);
    }


    // Private Methods
    // =========================================================================

    private function _mapTrackingStatus(string $status): string
    {
        return match ($status) {
            'ATTEMPTED_DELIVERY', 'TERMINAL', 'TRANSPORT_TO_RECIPIENT', 'NOTIFICATION_SENT', 'IN_TRANSIT', 'INTERNATIONAL', 'HANDED_IN', 'DEVIATION', 'DELIVERY_ORDERED', 'DELIVERY_CHANGED', 'DELIVERY_CANCELLED', 'DELIVERED_SENDER', 'COLLECTED', 'CUSTOMS' => Tracking::STATUS_IN_TRANSIT,
            'DELIVERED' => Tracking::STATUS_DELIVERED,
            'PRE_NOTIFIED' => Tracking::STATUS_PRE_TRANSIT,
            'READY_FOR_PICKUP' => Tracking::STATUS_AVAILABLE_FOR_PICKUP,
            'RETURN' => Tracking::STATUS_RETURN_TO_SENDER,
            default => Tracking::STATUS_UNKNOWN,
        };
    }

    private function _getLabelData(string $url): string
    {
        return base64_encode((new HttpClient())->request('GET', $url)->getBody()->getContents());
    }
}

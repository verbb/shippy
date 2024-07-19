<?php
namespace verbb\shippy\carriers;

use Exception;
use Illuminate\Support\Arr;
use verbb\shippy\helpers\Xml;
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

class Aramex extends AbstractCarrier
{
    // Static Methods
    // =========================================================================

    public static function getName(): string
    {
        return 'Aramex';
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
        return "https://www.aramex.com/tools/track?l={$trackingNumber}";
    }

    public static function supportsLabels(): bool
    {
        return false;
    }


    // Properties
    // =========================================================================

    protected ?string $username = null;
    protected ?string $password = null;
    protected ?string $version = null;
    protected ?string $accountNumber = null;
    protected ?string $accountPin = null;
    protected ?string $accountEntity = null;
    protected ?string $accountCountryCode = null;
    protected ?string $source = null;


    // Public Methods
    // =========================================================================

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): Aramex
    {
        $this->username = $username;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): Aramex
    {
        $this->password = $password;
        return $this;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(?string $version): Aramex
    {
        $this->version = $version;
        return $this;
    }

    public function getAccountNumber(): ?string
    {
        return $this->accountNumber;
    }

    public function setAccountNumber(?string $accountNumber): Aramex
    {
        $this->accountNumber = $accountNumber;
        return $this;
    }

    public function getAccountPin(): ?string
    {
        return $this->accountPin;
    }

    public function setAccountPin(?string $accountPin): Aramex
    {
        $this->accountPin = $accountPin;
        return $this;
    }

    public function getAccountEntity(): ?string
    {
        return $this->accountEntity;
    }

    public function setAccountEntity(?string $accountEntity): Aramex
    {
        $this->accountEntity = $accountEntity;
        return $this;
    }

    public function getAccountCountryCode(): ?string
    {
        return $this->accountCountryCode;
    }

    public function setAccountCountryCode(?string $accountCountryCode): Aramex
    {
        $this->accountCountryCode = $accountCountryCode;
        return $this;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(?string $source): Aramex
    {
        $this->source = $source;
        return $this;
    }

    public function getRates(Shipment $shipment): ?RateResponse
    {
        $payload = [
            'ClientInfo' => [
                'UserName' => $this->username,
                'Password' => $this->password,
                'Version' => $this->version,
                'AccountNumber' => $this->accountNumber,
                'AccountPin' => $this->accountPin,
                'AccountEntity' => $this->accountEntity,
                'AccountCountryCode' => $this->accountCountryCode,
                'Source' => $this->source,
            ],
            'OriginAddress' => $this->getAddress($shipment->getFrom()),
            'DestinationAddress' => $this->getAddress($shipment->getTo()),
            'PreferredCurrencyCode' => $shipment->getCurrency(),
            'ShipmentDetails' => [
                'Dimensions' => null,
                'ActualWeight' => [
                    'Unit' => self::getWeightUnit($shipment),
                    'Value' => $shipment->getTotalWeight($this),
                ],
                'ChargeableWeight' => null,
                'DescriptionOfGoods' => null,
                'GoodsOriginCountry' => null,
                'NumberOfPieces' => count($shipment->getPackages()),
                'ProductGroup' => 'EXP',
                'ProductType' => 'PPX',
                'PaymentType' => 'P',
                'PaymentOptions' => '',
            ],
        ];

        $request = new Request([
            'endpoint' => 'ShippingAPI.V2/RateCalculator/Service_1_0.svc/json/CalculateRate',
            'payload' => [
                'json' => $payload,
            ],
        ]);

        $data = $this->fetchRates($request, function(Response $response) {
            $json = $response->json();

            // Parse errors, which aren't flagged via HTTP status codes
            foreach (Arr::get($json, 'Notifications', []) as $notification) {
                $errorCode = Arr::get($notification, 'Code');
                $errorMessage = Arr::get($notification, 'Message');

                if ($errorCode || $errorMessage) {
                    $json['__errors'][] = implode(': ', [$errorCode, $errorMessage]);
                }
            }

            return $json;
        });

        $rates = [];

        $currency = Arr::get($data, 'TotalAmount.CurrencyCode');
        $rate = Arr::get($data, 'TotalAmount.Value');

        if ($rate) {
            $rates[] = new Rate([
                'carrier' => $this,
                'response' => $data,
                'serviceName' => '',
                'serviceCode' => '',
                'rate' => $rate,
                'currency' => $currency,
            ]);
        }

        return new RateResponse([
            'response' => $data,
            'rates' => $rates,
        ]);
    }

    public function getTrackingStatus(array $trackingNumbers, array $options = []): ?TrackingResponse
    {
        $tracking = [];

        $payload = [
            'ClientInfo' => [
                'UserName' => $this->username,
                'Password' => $this->password,
                'Version' => $this->version,
                'AccountNumber' => $this->accountNumber,
                'AccountPin' => $this->accountPin,
                'AccountEntity' => $this->accountEntity,
                'AccountCountryCode' => $this->accountCountryCode,
                'Source' => $this->source,
            ],
            'Shipments' => array_map(function($trackingNumber) {
                return ['arr:string' => str_replace(' ', '', $trackingNumber)];
            }, $trackingNumbers),
            'GetLastTrackingUpdateOnly' => false,
        ];

        $body = Xml::encode([
            '@xmlns' => 'http://ws.aramex.net/ShippingAPI/v1/',
            '@xmlns:arr' => 'http://schemas.microsoft.com/2003/10/Serialization/Arrays',
            '@xmlns:soap' => 'http://schemas.xmlsoap.org/soap/envelope/',
            'soap:Header' => '',
            'soap:Body' => [
                'ShipmentTrackingRequest' => $payload,
            ],

        ], [
            'xml_root_node_name' => 'soap:Envelope',
        ]);

        $request = new Request([
            'endpoint' => 'ShippingAPI.V2/Tracking/Service_1_0.svc',
            'payload' => [
                'headers' => [
                    'Content-Type' => 'text/xml',
                    'SOAPAction' => 'http://ws.aramex.net/ShippingAPI/v1/Service_1_0/TrackShipments',
                ],
                'body' => $body,
            ],
        ]);

        $data = $this->fetchTracking($request, function(Response $response) {
            return $response->xml();
        });

        $trackingItems = Arr::get($data, 's:Body.ShipmentTrackingResponse.TrackingResults', []);

        foreach ($trackingItems as $trackingItem) {
            $trackingNumber = Arr::get($trackingItem, 'a:Key', '');
            $statusCode = Arr::get($trackingItem, 'a:Value.TrackingResult.0.UpdateCode', '');
            $status = $this->_mapTrackingStatus($statusCode);

            $tracking[] = new Tracking([
                'carrier' => $this,
                'response' => $trackingItem,
                'trackingNumber' => $trackingNumber,
                'status' => $status,
                'estimatedDelivery' => null,
                'details' => array_map(function($detail) {
                    return new TrackingDetail([
                        'location' => Arr::get($detail, 'UpdateLocation', ''),
                        'description' => Arr::get($detail, 'UpdateDescription', ''),
                        'date' => Arr::get($detail, 'UpdateDateTime', ''),
                    ]);
                }, Arr::get($trackingItem, 'a:Value.TrackingResult', [])),
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
        if ($this->isProduction()) {
            $url = 'https://ws.aramex.net/';
        } else {
            $url = 'https://ws.dev.aramex.net/';
        }

        return new HttpClient([
            'base_uri' => $url,
        ]);
    }


    // Protected Methods
    // =========================================================================

    protected function getAddress(Address $address): array
    {
        return [
            'Line1' => $address->getStreet1(),
            'Line2' => $address->getStreet2(),
            'Line3' => '',
            'City' => $address->getCity(),
            'StateOrProvinceCode' => $address->getStateProvince(),
            'PostCode' => $address->getPostalCode(),
            'CountryCode' => $address->getCountryCode(),
        ];
    }


    // Private Methods
    // =========================================================================

    private function _mapTrackingStatus(string $status): string
    {
        return match ($status) {
            'SH001' => Tracking::STATUS_PRE_TRANSIT,
            'SH003', 'SH004' => Tracking::STATUS_OUT_FOR_DELIVERY,
            'SH005' => Tracking::STATUS_DELIVERED,
            'SH308', 'SH312' => Tracking::STATUS_PENDING,
            default => Tracking::STATUS_UNKNOWN,
        };
    }
}

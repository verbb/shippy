<?php
namespace verbb\shippy\carriers;

use DateTime;
use Illuminate\Support\Arr;
use verbb\shippy\exceptions\InvalidRequestException;
use verbb\shippy\helpers\Xml;
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

class CanadaPost extends AbstractCarrier
{
    // Static Methods
    // =========================================================================

    public static function getName(): string
    {
        return 'Canada Post';
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
        return "https://www.canadapost.ca/cpotools/apps/track/personal/findByTrackNumber?trackingNumber={$trackingNumber}";
    }

    public static function getServiceCodes(): array
    {
        return [
            'CA' => [ // Domestic
                'DOM.RP' => 'Regular Parcel',
                'DOM.EP' => 'Expedited Parcel',
                'DOM.XP' => 'Xpresspost',
                'DOM.XP.CERT' => 'Xpresspost Certified',
                'DOM.PC' => 'Priority',
                'DOM.DT' => 'Delivered Tonight',
                'DOM.LIB' => 'Library Books',
            ],

            'US' => [ // USA
                'USA.EP' => 'Expedited Parcel USA',
                'USA.TP' => 'Tracked Packet - USA',

                'USA.TP.LVM' => 'Tracked Packet USA (LVM)',
                'USA.PW.ENV' => 'Priority Worldwide Envelope USA',
                'USA.PW.PAK' => 'Priority Worldwide pak USA',

                'USA.PW_PARCEL' => 'Priority Worldwide parcel USA',
                'USA.SP.AIR' => 'Small Packet USA Air',
                'USA.SP.AIR.LVM' => 'Tracked Packet USA (LVM)',
                'USA.XP' => 'Xpresspost USA',
            ],

            'international' => [ // International
                'INT.XP' => 'Xpresspost International',
                'INT.TP' => 'Tracked Packet - International',
                'INT.IP.AIR' => 'International Parcel Air',
                'INT.IP.SURF' => 'International Parcel Surface',
                'INT.PW.ENV' => 'Priority Worldwide envelope INTL',
                'INT.PW.PAK' => 'Priority Worldwide pak INTL',
                'INT.PW.PARCEL' => 'Priority Worldwide parcel INTL',
                'INT.SP.AIR' => 'Small Packet International Air',
                'INT.SP.SURF' => 'Small Packet International Surface',
            ],
        ];
    }


    // Properties
    // =========================================================================

    protected ?string $customerNumber = null;
    protected ?string $contractId = null;
    protected ?string $username = null;
    protected ?string $password = null;
    protected array $additionalOptions = [];


    // Public Methods
    // =========================================================================

    public function getCustomerNumber(): ?string
    {
        return $this->customerNumber;
    }

    public function setCustomerNumber(?string $customerNumber): CanadaPost
    {
        $this->customerNumber = $customerNumber;
        return $this;
    }

    public function getContractId(): ?string
    {
        return $this->contractId;
    }

    public function setContractId(?string $contractId): CanadaPost
    {
        $this->contractId = $contractId;
        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): CanadaPost
    {
        $this->username = $username;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): CanadaPost
    {
        $this->password = $password;
        return $this;
    }

    public function getAdditionalOptions(): array
    {
        return $this->additionalOptions;
    }

    public function setAdditionalOptions(array $additionalOptions): CanadaPost
    {
        $this->additionalOptions = $additionalOptions;
        return $this;
    }

    /**
     * @throws InvalidRequestException
     */
    public function getRates(Shipment $shipment): ?RateResponse
    {
        $this->validate('username', 'password', 'customerNumber');

        $payload = [
            'customer-number' => $this->customerNumber,
            'parcel-characteristics' => [
                'weight' => $shipment->getTotalWeight($this),
            ],
        ];

        foreach ($this->additionalOptions as $i => $option) {
            $payload['options']['option'][$i]['option-code'] = $option;

            if ($option === 'COV') {
                $payload['options']['option'][$i]['option-amount'] = 100;
            }
        }

        $payload['origin-postal-code'] = $shipment->getFrom()->getPostalCode();

        if ($shipment->getTo()->getCountryCode() === 'CA') {
            $payload['destination']['domestic']['postal-code'] = $shipment->getTo()->getPostalCode();
        } else if ($shipment->getTo()->getCountryCode() === 'US') {
            $payload['destination']['united-states']['zip-code'] = $shipment->getTo()->getPostalCode();
        } else {
            $payload['destination']['international']['country-code'] = $shipment->getTo()->getCountryCode();
        }

        $body = Xml::encode([
            '@xmlns' => 'http://www.canadapost.ca/ws/ship/rate-v4',
            '#' => $payload,
        ], [
            'xml_root_node_name' => 'mailing-scenario',
        ]);

        $request = new Request([
            'endpoint' => 'rs/ship/price',
            'payload' => [
                'headers' => [
                    'Content-Type' => 'application/vnd.cpc.ship.rate-v4+xml',
                    'Accept' => 'application/vnd.cpc.ship.rate-v4+xml',
                ],
                'body' => $body,
            ],
        ]);

        $data = $this->fetchRates($request, function(Response $response) {
            return $response->xml();
        });

        $rates = [];

        foreach (Arr::get($data, 'price-quote', []) as $service) {
            $serviceCode = Arr::get($service, 'service-code', '');
            $serviceName = Arr::get($service, 'service-name', '');
            $rate = Arr::get($service, 'price-details.due', 0);

            $rates[] = new Rate([
                'carrier' => $this,
                'response' => $service,
                'serviceName' => $serviceName,
                'serviceCode' => $serviceCode,
                'rate' => $rate,
                'currency' => 'CAD',
                'deliveryDate' => Arr::get($service, 'service-standard.expected-delivery-date', ''),
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
        $this->validate('username', 'password');

        $data = [];
        $tracking = [];

        foreach ($trackingNumbers as $trackingNumber) {
            $trackingNumber = str_replace(' ', '', $trackingNumber);

            $request = new Request([
                'method' => 'GET',
                'endpoint' => "vis/track/pin/{$trackingNumber}/details",
                'payload' => [
                    'headers' => [
                        'Content-Type' => 'application/vnd.cpc.track-v2+xml',
                        'Accept' => 'application/vnd.cpc.track-v2+xml',
                    ],
                ],
            ]);

            $data = $this->fetchTracking($request, function(Response $response) {
                return $response->xml();
            });

            $statusCode = Arr::get($data, 'significant-events.occurrence.0.event-identifier', '');
            $status = $this->_mapTrackingStatus($statusCode);

            $tracking[] = new Tracking([
                'carrier' => $this,
                'response' => $data,
                'trackingNumber' => $trackingNumber,
                'status' => $status,
                'estimatedDelivery' => null,
                'details' => array_map(function($detail) {
                    $location = array_filter([
                        Arr::get($detail, 'event-retail-name', ''),
                        Arr::get($detail, 'event-site', ''),
                        Arr::get($detail, 'event-province', ''),
                    ]);

                    return new TrackingDetail([
                        'location' => implode(' ', $location),
                        'description' => Arr::get($detail, 'event-description', ''),
                        'date' => Arr::get($detail, 'event-date', '') . ' ' . Arr::get($detail, 'event-time', ''),
                    ]);
                }, Arr::get($data, 'significant-events.occurrence', [])),
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
        $this->validate('username', 'password', 'customerNumber', 'contractId');

        $mailingDate = (new DateTime())->modify('+1 day')->format('Y-m-d');

        $payload = [
            'group-id' => Arr::get($options, 'groupId', '4326432'),
            'requested-shipping-point' => Arr::get($options, 'requestedShippingPoint', 'H2B1A0'),
            'cpc-pickup-indicator' => true,
            'expected-mailing-date' => $mailingDate,
            'delivery-spec' => [
                'service-code' => $rate->getServiceCode(),
                'sender' => [
                    'name' => $shipment->getFrom()->getFullName(),
                    'company' => 'Sender',
                    'contact-phone' => '',
                    'address-details' => [
                        'address-line-1' => $shipment->getFrom()->getStreet1(),
                        'city' => $shipment->getFrom()->getCity(),
                        'prov-state' => $shipment->getFrom()->getStateProvince(),
                        'postal-zip-code' => $shipment->getFrom()->getPostalCode(),
                        'country-code' => $shipment->getFrom()->getCountryCode(),
                    ],
                ],
                'destination' => [
                    'name' => $shipment->getTo()->getFullName(),
                    'company' => '',
                    'address-details' => [
                        'address-line-1' => $shipment->getTo()->getStreet1(),
                        'city' => $shipment->getTo()->getCity(),
                        'prov-state' => $shipment->getTo()->getStateProvince(),
                        'postal-zip-code' => $shipment->getTo()->getPostalCode(),
                        'country-code' => $shipment->getTo()->getCountryCode(),
                    ],
                ],
                'preferences' => [
                    'show-packing-instructions' => true,
                    'show-postage-rate' => false,
                    'show-insured-value' => true,
                ],
                'settlement-info' => [
                    'contract-id' => $this->contractId,
                    'intended-method-of-payment' => 'Account',
                ],
                'parcel-characteristics' => [
                    'dimensions' => [
                        'length' => $shipment->getTotalLength($this),
                        'width' => $shipment->getTotalWidth($this),
                        'height' => $shipment->getTotalHeight($this),
                    ],
                    'weight' => $shipment->getTotalWeight($this),
                ],
            ],
        ];

        $body = Xml::encode([
            '@xmlns' => 'http://www.canadapost.ca/ws/shipment-v8',
            '#' => $payload,
        ], [
            'xml_root_node_name' => 'shipment',
        ]);

        $request = new Request([
            'method' => 'POST',
            'endpoint' => 'rs/' . $this->customerNumber . '/' . $this->customerNumber . '/shipment',
            'payload' => [
                'headers' => [
                    'Content-Type' => 'application/vnd.cpc.shipment-v8+xml',
                    'Accept' => 'application/vnd.cpc.shipment-v8+xml',
                ],
                'body' => $body,
            ],
        ]);

        $data = $this->fetchLabels($request, function(Response $response) {
            return $response->xml();
        });

        $labels = [];
        $labelUrl = '';

        foreach (Arr::get($data, 'links.link', []) as $link) {
            if (Arr::get($link, '@media-type') === 'application/pdf') {
                $labelUrl = Arr::get($link, '@href');
            }
        }

        $labels[] = new Label([
            'carrier' => $this,
            'response' => $data,
            'rate' => $rate,
            'trackingNumber' => Arr::get($data, 'tracking-pin'),
            'labelId' => Arr::get($data, 'shipment-id'),
            'labelData' => $this->_getLabelData($labelUrl),
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
            $url = 'https://soa-gw.canadapost.ca/';
        } else {
            $url = 'https://ct.soa-gw.canadapost.ca/';
        }

        return new HttpClient([
            'base_uri' => $url,
            'auth' => [
                $this->username, $this->password,
            ],
        ]);
    }


    // Private Methods
    // =========================================================================

    private function _mapTrackingStatus(string $status): string
    {
        return match ($status) {
            '1408', '1499', '1498', '1497', '1496', '1441', '1434', '1433', '1432', '1431', '1430', '1429', '1428', '1427', '1426', '1425', '1424', '1423', '1422', '1421', '1409' => Tracking::STATUS_DELIVERED,
            default => Tracking::STATUS_UNKNOWN,
        };
    }

    private function _getLabelData(string $url): string
    {
        return base64_encode((new HttpClient())
            ->request('GET', $url, [
                'auth' => [
                    $this->username, $this->password,
                ],
                'headers' => [
                    'Content-Type' => 'application/pdf',
                    'Accept' => 'application/pdf',
                ],
            ])->getBody()->getContents());
    }
}

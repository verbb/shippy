<?php
namespace verbb\shippy\carriers;

use DateTime;
use Illuminate\Support\Arr;
use verbb\shippy\exceptions\InvalidRequestException;
use verbb\shippy\helpers\Json;
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

class USPS extends AbstractCarrier
{
    // Static Methods
    // =========================================================================

    public static function getName(): string
    {
        return 'USPS';
    }

    public static function getWeightUnit(Shipment $shipment): string
    {
        return 'lb';
    }

    public static function getDimensionUnit(Shipment $shipment): string
    {
        return 'in';
    }
    
    public static function getTrackingUrl(string $trackingNumber): ?string
    {
        return "https://tools.usps.com/go/TrackConfirmAction_input?qtc_tLabels1={$trackingNumber}";
    }

    public static function getServiceCodes(): array
    {
        return [
            'US' => [
                'FIRST-CLASS_MAIL' => 'First-Class Mail',
                'PRIORITY_MAIL' => 'Priority Mail',
                'PRIORITY_MAIL_GUARANTEED' => 'Priority Mail Guaranteed',
                'PRIORITY_MAIL_EXPRESS' => 'Priority Mail Express',
                'PRIORITY_MAIL_SAME_DAY' => 'Priority Mail Same Day',
                'BOUND_PRINTED_MATTER' => 'Bound Printed Matter',
                'LIBRARY_MAIL' => 'Library Mail',
                'USPS_RETAIL_GROUND' => 'Retail Ground',
                'MEDIA_MAIL' => 'Media Mail',
                'CRITICAL_MAIL' => 'Critical Mail',
                'DOMESTIC_MATTER_FOR_THE_BLIND' => 'Domestic Matter for the Blind',
                'PARCEL_SELECT_LIGHTWEIGHT' => 'Parcel Select Lightweight',
                'PARCEL_SELECT' => 'Parcel Select',
                'USPS_MARKETING_MAIL' => 'Maketing Mail',
            ],
            'international' => [
                'PRIORITY_MAIL_EXPRESS_INTERNATIONAL' => 'Priority Mail Express Mail International',
                'PRIORITY_MAIL_INTERNATIONAL_PARCELS' => 'Priority Mail International Parcels',
                'GLOBAL_EXPRESS_GUARANTEED' => 'Global Express Guaranteed',
                'FIRST-CLASS_PACKAGE_INTERNATIONAL_SERVICE' => 'First-Class Package International Service',
            ],
        ];
    }

    public static function isDomestic(string $countryCode): bool
    {
        $domestic = ['US', 'PR', 'VI', 'MH', 'FM', 'GU', 'MP', 'AS', 'UM'];

        return in_array($countryCode, $domestic);
    }


    // Properties
    // =========================================================================

    protected ?string $clientId = null;
    protected ?string $clientSecret = null;
    protected ?string $accountNumber = null;
    protected ?string $customerRegistrationId = null;
    protected ?string $mailerId = null;


    // Public Methods
    // =========================================================================

    public function getClientId(): ?string
    {
        return $this->clientId;
    }

    public function setClientId(?string $clientId): USPS
    {
        $this->clientId = $clientId;
        return $this;
    }

    public function getClientSecret(): ?string
    {
        return $this->clientSecret;
    }

    public function setClientSecret(?string $clientSecret): USPS
    {
        $this->clientSecret = $clientSecret;
        return $this;
    }

    public function getAccountNumber(): ?string
    {
        return $this->accountNumber;
    }

    public function setAccountNumber(?string $accountNumber): USPS
    {
        $this->accountNumber = $accountNumber;
        return $this;
    }

    public function getCustomerRegistrationId(): ?string
    {
        return $this->customerRegistrationId;
    }

    public function setCustomerRegistrationId(?string $customerRegistrationId): USPS
    {
        $this->customerRegistrationId = $customerRegistrationId;
        return $this;
    }

    public function getMailerId(): ?string
    {
        return $this->mailerId;
    }

    public function setMailerId(?string $mailerId): USPS
    {
        $this->mailerId = $mailerId;
        return $this;
    }

    /**
     * @throws InvalidRequestException
     */
    public function getRates(Shipment $shipment): ?RateResponse
    {
        $this->validate('clientId', 'clientSecret', 'accountNumber');

        $shipDate = (new DateTime())->modify('+1 day')->format('Y-m-d');

        $payload = [
            'originZIPCode' => $shipment->getFrom()->getPostalCode(),
            'mailingDate' => $shipDate,
            'accountType' => 'EPS',
            'accountNumber' => $this->accountNumber,
            'length' => (float)$shipment->getTotalLength($this, 0),
            'width' => (float)$shipment->getTotalWidth($this, 0),
            'height' => (float)$shipment->getTotalHeight($this, 0),
            'weight' => (float)$shipment->getTotalWeight($this, 0),
        ];

        if (self::isDomestic($shipment->getTo()->getCountryCode())) {
            $payload = array_merge($payload, [
                'destinationZIPCode' => $shipment->getTo()->getPostalCode(),
            ]);

            $request = new Request([
                'endpoint' => 'prices/v3/base-rates-list/search',
                'payload' => [
                    'json' => $payload,
                ],
            ]);
        } else {
            $payload = array_merge($payload, [
                'foreignPostalCode' => $shipment->getTo()->getPostalCode(),
                'destinationCountryCode' => $shipment->getTo()->getCountryCode(),
            ]);

            $request = new Request([
                'endpoint' => 'international-prices/v3/base-rates-list/search',
                'payload' => [
                    'json' => $payload,
                ],
            ]);
        }

        $data = $this->fetchRates($request, function(Response $response) {
            return $response->json();
        });

        $rates = [];
        $processedRates = [];

        foreach (Arr::get($data, 'rateOptions', []) as $shippingRate) {
            $serviceCode = Arr::get($shippingRate, 'rates.0.mailClass', '');
            $serviceName = Arr::get($shippingRate, 'rates.0.description', '');
            $rate = Arr::get($shippingRate, 'rates.0.price', 0);

            // We get duplicate rates (the same `price` and `mailClass`) but different description. Skip them.
            $rateKey = $rate . '_' . $serviceCode;

            if (isset($processedRates[$rateKey])) {
                continue;
            }

            $rates[] = new Rate([
                'carrier' => $this,
                'response' => $shippingRate,
                'serviceName' => $serviceName,
                'serviceCode' => $serviceCode,
                'rate' => $rate,
            ]);

            $processedRates[$rateKey] = true;
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
            $request = new Request([
                'method' => 'GET',
                'endpoint' => 'tracking/v3/tracking/' . str_replace(' ', '', $trackingNumber),
                'payload' => [
                    'query' => [
                        'expand' => 'DETAIL',
                    ],
                ],
            ]);

            $data = $this->fetchTracking($request, function(Response $response) {
                return $response->json();
            });

            $trackingNumber = Arr::get($data, 'trackingNumber', '');
            $statusCode = Arr::get($data, 'statusCategory', '');
            $status = $this->_mapTrackingStatus($statusCode);

            $tracking[] = new Tracking([
                'carrier' => $this,
                'response' => $data,
                'trackingNumber' => $trackingNumber,
                'status' => $status,
                'estimatedDelivery' => null,
                'details' => array_map(function($detail) {
                    $location = array_filter([
                        Arr::get($detail, 'eventCity', ''),
                        Arr::get($detail, 'eventState', ''),
                        Arr::get($detail, 'eventZIP', ''),
                        Arr::get($detail, 'eventCountry', ''),
                    ]);

                    return new TrackingDetail([
                        'location' => implode(' ', $location),
                        'description' => Arr::get($detail, 'eventType', ''),
                        'date' => Arr::get($detail, 'eventTimestamp', ''),
                    ]);
                }, Arr::get($data, 'trackingEvents', [])),
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
        $this->validate('clientId', 'clientSecret', 'accountNumber', 'customerRegistrationId', 'mailerId');

        $shipDate = (new DateTime())->modify('+1 day')->format('Y-m-d');

        $payload = [
            'imageInfo' => [
                'imageType' => Arr::get($options, 'labelFormat', 'PDF'),
                'labelType' => Arr::get($options, 'labelType', '4X6LABEL'),
                'shipInfo' => true,
                'receiptOption' => 'NONE',
                'suppressPostage' => false,
                'suppressMailDate' => false,
                'returnLabel' => false,
            ],
            'toAddress' => [
                'firstName' => $shipment->getTo()->getFirstName(),
                'lastName' => $shipment->getTo()->getLastName(),
                'streetAddress' => $shipment->getTo()->getStreet1(),
                'secondaryAddress' => $shipment->getTo()->getStreet2(),
                'city' => $shipment->getTo()->getCity(),
                'state' => $shipment->getTo()->getStateProvince(),
                'ZIPCode' => $shipment->getTo()->getPostalCode(),
            ],
            'fromAddress' => [
                'firstName' => $shipment->getFrom()->getFirstName(),
                'lastName' => $shipment->getFrom()->getLastName(),
                'streetAddress' => $shipment->getFrom()->getStreet1(),
                'secondaryAddress' => $shipment->getFrom()->getStreet2(),
                'city' => $shipment->getFrom()->getCity(),
                'state' => $shipment->getFrom()->getStateProvince(),
                'ZIPCode' => $shipment->getFrom()->getPostalCode(),
            ],
            'packageDescription' => [
                'mailClass' => $rate->getServiceCode(),
                'rateIndicator' => 'SP',
                'weightUOM' => self::getWeightUnit($shipment),
                'dimensionsUOM' => self::getDimensionUnit($shipment),
                'processingCategory' => 'MACHINABLE',
                'mailingDate' => $shipDate,
                'destinationEntryFacilityType' => 'NONE',
                'length' => (float)$shipment->getTotalLength($this, 0),
                'width' => (float)$shipment->getTotalWidth($this, 0),
                'height' => (float)$shipment->getTotalHeight($this, 0),
                'weight' => (float)$shipment->getTotalWeight($this, 0),
            ],
        ];

        $paymentRequest = new Request([
            'method' => 'POST',
            'endpoint' => 'payments/v3/payment-authorization',
            'payload' => [
                'json' => [
                    'roles' => [
                        [
                            'roleName' => 'PAYER',
                            'CRID' => $this->customerRegistrationId,
                            'MID' => $this->mailerId,
                            'manifestMID' => $this->mailerId,
                            'accountType' => 'EPS',
                            'accountNumber' => $this->accountNumber,
                        ],
                        [
                            'roleName' => 'LABEL_OWNER',
                            'CRID' => $this->customerRegistrationId,
                            'MID' => $this->mailerId,
                            'manifestMID' => $this->mailerId,
                            'accountType' => 'EPS',
                            'accountNumber' => $this->accountNumber,
                        ],
                    ],
                ],
            ],
        ]);

        // Create a payment request first to authorize label creation
        $paymentResponse = $this->request($paymentRequest);
        $paymentData = Json::decode($paymentResponse->getContent());
        $paymentAuthorizationToken = Arr::get($paymentData, 'paymentAuthorizationToken');

        if (self::isDomestic($shipment->getTo()->getCountryCode())) {
            $endpoint = 'labels/v3/label';
        } else {
            $endpoint = 'international-labels/v3/international-label';
        }

        $request = new Request([
            'method' => 'POST',
            'endpoint' => $endpoint,
            'payload' => [
                'headers' => [
                    'X-Payment-Authorization-Token' => $paymentAuthorizationToken,
                ],
                'json' => $payload,
            ],
        ]);

        // Custom parsing for multipart response
        $data = $this->fetchLabels($request, function(Response $response) {
            $responseData = [];
            $contentType = $response->getResponse()->getHeader('Content-Type')[0] ?? null;
            $boundary = str_replace('multipart/mixed; boundary=', '', $contentType);

            $multipart = $this->_parseMultipartResponse($response->getContent(), $boundary);

            foreach ($multipart as $multi) {
                if (Arr::get($multi, 'headers.Content-Type') === 'application/pdf') {
                    $responseData['label'] = Arr::get($multi, 'content');
                }

                if (Arr::get($multi, 'headers.Content-type') === 'application/json') {
                    $responseData['data'] = Json::decode(Arr::get($multi, 'content'));
                }
            }

            return $responseData;
        });

        $labels = [];

        $labels[] = new Label([
            'carrier' => $this,
            'response' => $data,
            'rate' => $rate,
            'trackingNumber' => Arr::get($data, 'data.trackingNumber'),
            'labelId' => Arr::get($data, 'data.SKU'),
            'labelData' => Arr::get($data, 'label'),
            'labelMime' => 'application/pdf',
        ]);

        return new LabelResponse([
            'response' => $data,
            'labels' => $labels,
        ]);
    }

    public function getHttpClient(): HttpClient
    {
        // Fetch an access token first
        $authResponse = Json::decode((string)(new HttpClient())
            ->request('POST', 'https://api.usps.com/oauth2/v3/token', [
                'json' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                ],
            ])->getBody());

        return new HttpClient([
            'base_uri' => 'https://api.usps.com',
            'headers' => [
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
            'Delivered' => Tracking::STATUS_DELIVERED,
            'USPS in possession of item', 'Arrived at USPS Regional Facility', 'Departed USPS Regional Facility', 'Sorting Complete', 'Out for Delivery', 'Acceptance', 'Origin Post is Preparing Shipment', 'Processed Through Facility', 'Processed Through Sort Facility', 'Arrived at USPS Facility', 'Departed USPS Facility', 'Arrived at Unit', 'Notice Left' => Tracking::STATUS_IN_TRANSIT,
            default => Tracking::STATUS_UNKNOWN,
        };
    }

    private function _parseMultipartResponse(string $data, string $boundary): array
    {
        $parsed = [];

        $bodies = explode('--' . $boundary, $data);

        foreach ($bodies as $j => $body) {
            $isHeader = true;

            foreach (explode(PHP_EOL, $body) as $i => $line) {
                if ($i === 0) {
                    continue;
                }

                if (trim($line) === '') {
                    $isHeader = false;

                    continue;
                }

                if ($isHeader) {
                    [$header, $value] = explode(':', $line);

                    if ($header) {
                        $parsed[$j]['headers'][$header] = trim($value);
                    }
                } else {
                    $parsed[$j]['content'] = $line;
                }
            }
        }

        return array_values($parsed);
    }
}

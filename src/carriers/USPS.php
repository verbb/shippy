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
                'PRIORITY_MAIL__FE' => 'Priority Mail Flat Rate Envelope',
                'PRIORITY_MAIL__FP' => 'Priority Mail Padded Flat Rate Envelope',
                'PRIORITY_MAIL__FA' => 'Priority Mail Legal Flat Rate Envelope',
                'PRIORITY_MAIL__FS' => 'Priority Mail Small Flat Rate Box',
                'PRIORITY_MAIL__FB' => 'Priority Mail Medium Flat Rate Box',
                'PRIORITY_MAIL__PL' => 'Priority Mail Large Flat Rate Box',
                'PRIORITY_MAIL__PM' => 'Priority Mail Large Flat Rate Box APO/FPO/DPO',
                'PRIORITY_MAIL__SP' => 'PMOD DSCF Single-piece',
                'PRIORITY_MAIL__CP' => 'Priority Mail Cubic Non-Soft Pack Tier 1',
                'PRIORITY_MAIL__P5' => 'Priority Mail Cubic Soft Pack Tier 1',
                'PRIORITY_MAIL__O1' => 'PMOD DSCF Full Tray Box',
                'PRIORITY_MAIL__O2' => 'PMOD DSCF Half Tray Box',
                'PRIORITY_MAIL__O3' => 'PMOD DSCF Extended Managed Mail Tray Box',
                'PRIORITY_MAIL__O4' => 'PMOD DSCF Flat Tub Tray Box',
                
                'PRIORITY_MAIL_EXPRESS__E4' => 'Priority Mail Express Flat Rate Envelope',
                'PRIORITY_MAIL_EXPRESS__FP' => 'Priority Mail Express Padded Flat Rate Envelope',
                'PRIORITY_MAIL_EXPRESS__E6' => 'Priority Mail Express Legal Flat Rate Envelope',
                'PRIORITY_MAIL_EXPRESS__E7' => 'Priority Mail Express Legal Flat Rate Envelope Holiday Delivery',
                'PRIORITY_MAIL_EXPRESS__PA' => 'Priority Mail Express Single-piece',
                
                'USPS_GROUND_ADVANTAGE__SP' => 'USPS Ground Advantage Single-piece',
                'USPS_GROUND_ADVANTAGE__CP' => 'USPS Ground Advantage Cubic Non-Soft Pack Tier 1',
                'USPS_GROUND_ADVANTAGE__P5' => 'USPS Ground Advantage Cubic Soft Pack Tier 1',
                'USPS_GROUND_ADVANTAGE__P6' => 'USPS Ground Advantage Cubic Soft Pack Tier 2',
                
                'SA__N5' => 'USPS Marketing Mail Nonprofit Parcels DSCF Nonprofit 5-digit',
                'SA__ND' => 'USPS Marketing Mail Nonprofit Parcels Nonprofit NDC',
                'SA__NT' => 'USPS Marketing Mail Nonprofit Parcels DSCF Nonprofit SCF',
                'SA__NM' => 'USPS Marketing Mail Nonprofit Parcels Nonprofit Mixed NDC',
                
                'USPS_MARKETING_MAIL__NT' => 'USPS Marketing Mail Parcels DSCF Nonprofit SCF',
                'USPS_MARKETING_MAIL__N5' => 'USPS Marketing Mail Parcels DSCF Nonprofit 5-digit',
                'USPS_MARKETING_MAIL__BM' => 'USPS Marketing Mail Parcels NDC',
                'USPS_MARKETING_MAIL__ND' => 'USPS Marketing Mail Parcels Nonprofit NDC',
                'USPS_MARKETING_MAIL__3D' => 'USPS Marketing Mail Parcels DSCF 3-Digit',
                'USPS_MARKETING_MAIL__5D' => 'USPS Marketing Mail Parcels DSCF 5-digit',
                'USPS_MARKETING_MAIL__MB' => 'USPS Marketing Mail Parcels Mixed NDC',
                'USPS_MARKETING_MAIL__NM' => 'USPS Marketing Mail Parcels Nonprofit Mixed NDC',
                
                'BOUND_PRINTED_MATTER__PR' => 'Bound Printed Matter DSCF Presorted',
                
                'LIBRARY_MAIL__BA' => 'Library Mail Basic',
                'LIBRARY_MAIL__5D' => 'Library Mail 5-digit',
                
                'MEDIA_MAIL__BA' => 'Media Mail Basic',
                'MEDIA_MAIL__5D' => 'Media Mail 5-digit',
            ],
            'international' => [
                'FIRST-CLASS_PACKAGE_INTERNATIONAL_SERVICE__SP' => 'First-Class Package International Service ISC Single-piece',

                'PRIORITY_MAIL_INTERNATIONAL__FE' => 'Priority Mail International ISC Flat Rate Envelope',
                'PRIORITY_MAIL_INTERNATIONAL__FS' => 'Priority Mail International ISC Small Flat Rate Box',
                'PRIORITY_MAIL_INTERNATIONAL__FB' => 'Priority Mail International ISC Medium Flat Rate Box',
                'PRIORITY_MAIL_INTERNATIONAL__PL' => 'Priority Mail International ISC Large Flat Rate Box',
                'PRIORITY_MAIL_INTERNATIONAL__FP' => 'Priority Mail International ISC Padded Flat Rate Envelope',
                'PRIORITY_MAIL_INTERNATIONAL__SP' => 'Priority Mail International ISC Single-piece',
                'PRIORITY_MAIL_INTERNATIONAL__FA' => 'Priority Mail International ISC Legal Flat Rate Envelope',

                'PRIORITY_MAIL_EXPRESS_INTERNATIONAL__E4' => 'Priority Mail Express International ISC Flat Rate Envelope',
                'PRIORITY_MAIL_EXPRESS_INTERNATIONAL__E6' => 'Priority Mail Express International ISC Legal Flat Rate Envelope',
                'PRIORITY_MAIL_EXPRESS_INTERNATIONAL__PA' => 'Priority Mail Express International ISC Single-piece',
                'PRIORITY_MAIL_EXPRESS_INTERNATIONAL__FP' => 'Priority Mail Express International ISC Padded Flat Rate Envelope',
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
    protected bool $useLegacyApi = false;


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

    public function getUseLegacyApi(): bool
    {
        return $this->useLegacyApi;
    }

    public function setUseLegacyApi(bool $useLegacyApi): USPS
    {
        $this->useLegacyApi = $useLegacyApi;
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
            'length' => max($shipment->getTotalLength($this, 2), 0.1),
            'width' => max($shipment->getTotalWidth($this, 2), 0.1),
            'height' => max($shipment->getTotalHeight($this, 2), 0.1),
            'weight' => max($shipment->getTotalWeight($this, 2), 0.1),
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
            $serviceName = Arr::get($shippingRate, 'rates.0.description', '');
            $rate = Arr::get($shippingRate, 'rates.0.price', 0);

            $mailClass = Arr::get($shippingRate, 'rates.0.mailClass', '');
            $rateIndicator = Arr::get($shippingRate, 'rates.0.rateIndicator', '');
            $processingCategory = Arr::get($shippingRate, 'rates.0.processingCategory', '');

            // Store a few things as the "service code" to give more options for services and handle labels
            $serviceCode = implode('__', [$mailClass, $rateIndicator]);

            // Different service codes can produce the same price, so no need to duplicate them (machinable vs standard)
            $rateKey = implode('__', [$mailClass, $rateIndicator, $rate]);

            $processedRates[$rateKey][] = new Rate([
                'carrier' => $this,
                'response' => $shippingRate,
                'serviceName' => $serviceName,
                'serviceCode' => $serviceCode,
                'rate' => $rate,
            ]);
        }

        // There will be lots of rates per `mailClass`, so just return the cheapest for each
        foreach ($processedRates as $serviceCode => $serviceProcessedRates) {
            usort($serviceProcessedRates, function(Rate $a, Rate $b) {
                return $a->getRate() <=> $b->getRate();
            });

            $rates[] = $serviceProcessedRates[0] ?? [];
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

        // The service code will contain `mailClass`, `rateIndicator` and `processingCategory` but provide fallbacks
        $serviceCode = explode('__', $rate->getServiceCode());
        $mailClass = $serviceCode[0] ?? '';
        $rateIndicator = $serviceCode[1] ?? 'SP';
        $processingCategory = $serviceCode[2] ?? 'MACHINABLE';

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
                'mailClass' => $mailClass,
                'rateIndicator' => $rateIndicator,
                'weightUOM' => self::getWeightUnit($shipment),
                'dimensionsUOM' => self::getDimensionUnit($shipment),
                'processingCategory' => $processingCategory,
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
        $domain = $this->getUseLegacyApi() ? 'api.usps.com' : 'apis.usps.com';

        // Fetch an access token first
        $authResponse = Json::decode((string)(new HttpClient())
            ->request('POST', "https://$domain/oauth2/v3/token", [
                'json' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                ],
            ])->getBody());

        return new HttpClient([
            'base_uri' => "https://$domain/",
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

<?php
namespace verbb\shippy\carriers;

use DateTime;
use Exception;
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

class RoyalMail extends AbstractCarrier
{
    // Static Methods
    // =========================================================================

    public static function getName(): string
    {
        return 'Royal Mail';
    }

    public static function getWeightUnit(Shipment $shipment): string
    {
        return 'g';
    }

    public static function getDimensionUnit(Shipment $shipment): string
    {
        return 'mm';
    }
    
    public static function getTrackingUrl(string $trackingNumber): ?string
    {
        return "https://www.royalmail.com/portal/rm/track?trackNumber={$trackingNumber}";
    }

    public static function getServiceCodes(): array
    {
        return [
            // Domestic
            'first-class' => 'Royal Mail 1st Class',
            'first-class-signed' => 'Royal Mail Signed For® 1st Class',
            'second-class' => 'Royal Mail 2nd Class',
            'second-class-signed' => 'Royal Mail Signed For® 2nd Class',

            'special-delivery-9am' => 'Royal Mail Special Delivery Guaranteed by 9am®',
            'special-delivery-1pm' => 'Royal Mail Special Delivery Guaranteed by 1pm®',

            'parcelforce-express-9' => 'Parcelforce Worldwide Express 9',
            'parcelforce-express-10' => 'Parcelforce Worldwide Express 10',
            'parcelforce-express-am' => 'Parcelforce Worldwide Express AM',
            'parcelforce-express-24' => 'Parcelforce Worldwide Express 24',
            'parcelforce-express-48' => 'Parcelforce Worldwide Express 48',
            'parcelforce-express-48-large' => 'Parcelforce Worldwide Express 48 Large',

            'tracked-24' => 'Royal Mail Tracked 24',
            'tracked-48' => 'Royal Mail Tracked 48',

            // International
            'international-standard' => 'Royal Mail International Standard',
            'international-tracked-signed' => 'Royal Mail International Tracked & Signed',
            'international-tracked' => 'Royal Mail International Tracked',
            'international-signed' => 'Royal Mail International Signed',
            'international-economy' => 'Royal Mail International Economy',

            'parcelforce-europriority' => 'Parcelforce Worldwide Euro Priority',
            'parcelforce-irelandexpress' => 'Parcelforce Worldwide Ireland Express',
            'parcelforce-globaleconomy' => 'Parcelforce Worldwide Global Economy',
            'parcelforce-globalexpress' => 'Parcelforce Worldwide Global Express',
            'parcelforce-globalpriority' => 'Parcelforce Worldwide Global Priority',
            'parcelforce-globalvalue' => 'Parcelforce Worldwide Global Value',
        ];
    }


    // Properties
    // =========================================================================

    protected ?string $clientId = null;
    protected ?string $clientSecret = null;
    protected ?string $clickAndDropApiKey = null;
    protected bool $acceptTerms = true;
    protected bool $checkCompensation = true;
    protected bool $includeVat = true;
    protected bool $useClickAndDropLabels = false;


    // Public Methods
    // =========================================================================

    public function getClientId(): ?string
    {
        return $this->clientId;
    }

    public function setClientId(?string $clientId): RoyalMail
    {
        $this->clientId = $clientId;
        return $this;
    }

    public function getClientSecret(): ?string
    {
        return $this->clientSecret;
    }

    public function setClientSecret(?string $clientSecret): RoyalMail
    {
        $this->clientSecret = $clientSecret;
        return $this;
    }

    public function getRatesType(): ?string
    {
        return $this->ratesType;
    }

    public function setRatesType(?string $ratesType): RoyalMail
    {
        $this->ratesType = $ratesType;
        return $this;
    }

    public function getClickAndDropApiKey(): ?string
    {
        return $this->clickAndDropApiKey;
    }

    public function setClickAndDropApiKey(?string $clickAndDropApiKey): RoyalMail
    {
        $this->clickAndDropApiKey = $clickAndDropApiKey;
        return $this;
    }

    public function isAcceptTerms(): bool
    {
        return $this->acceptTerms;
    }

    public function setAcceptTerms(bool $acceptTerms): RoyalMail
    {
        $this->acceptTerms = $acceptTerms;
        return $this;
    }

    public function isCheckCompensation(): bool
    {
        return $this->checkCompensation;
    }

    public function setCheckCompensation(bool $checkCompensation): RoyalMail
    {
        $this->checkCompensation = $checkCompensation;
        return $this;
    }

    public function isIncludeVat(): bool
    {
        return $this->includeVat;
    }

    public function setIncludeVat(bool $includeVat): RoyalMail
    {
        $this->includeVat = $includeVat;
        return $this;
    }

    public function isUseClickAndDropLabels(): bool
    {
        return $this->useClickAndDropLabels;
    }

    public function setUseClickAndDropLabels(bool $useClickAndDropLabels): RoyalMail
    {
        $this->useClickAndDropLabels = $useClickAndDropLabels;
        return $this;
    }

    public function getRates(Shipment $shipment): ?RateResponse
    {
        $rates = [];

        foreach (self::getServiceCodes() as $serviceCode => $serviceName) {
            if ($rate = RoyalMailRates::getRate($serviceCode, $this, $shipment)) {
                $rates[] = $rate;
            }
        }

        return new RateResponse([
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
                'endpoint' => "mailpieces/v2/{$trackingNumber}/events",
            ]);

            $data = $this->fetchTracking($request, function(Response $response) {
                return $response->json();
            });

            if (Arr::get($data, 'mailPieces', '')) {
                $statusCode = Arr::get($data, 'mailPieces.summary.statusCategory', '');
                $status = $this->_mapTrackingStatus($statusCode);

                $tracking[] = new Tracking([
                    'carrier' => $this,
                    'response' => $data,
                    'trackingNumber' => $trackingNumber,
                    'status' => $status,
                    'estimatedDelivery' => Arr::get($data, 'mailPieces.estimatedDelivery.date', ''),
                    'details' => array_map(function($detail) {
                        return new TrackingDetail([
                            'location' => Arr::get($detail, 'locationName', ''),
                            'description' => Arr::get($detail, 'eventName', ''),
                            'date' => Arr::get($detail, 'eventDateTime', ''),
                        ]);
                    }, Arr::get($data, 'mailPieces.events', [])),
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
     * @throws Exception
     */
    public function getLabels(Shipment $shipment, Rate $rate, array $options = []): ?LabelResponse
    {
        if ($this->useClickAndDropLabels) {
            $this->validate('clickAndDropApiKey');

            $payload = [
                'items' => [
                    [
                        'recipient' => [
                            'address' => [
                                'fullName' => $shipment->getTo()->getFullName(),
                                'companyName' => $shipment->getTo()->getCompanyName(),
                                'addressLine1' => $shipment->getTo()->getStreet1(),
                                'addressLine2' => $shipment->getTo()->getStreet2(),
                                'addressLine3' => $shipment->getTo()->getStreet3(),
                                'city' => $shipment->getTo()->getCity(),
                                'county' => $shipment->getTo()->getStateProvince(),
                                'postcode' => $shipment->getTo()->getPostalCode(),
                                'countryCode' => $shipment->getTo()->getCountryCode(),
                            ],
                            'phoneNumber' => $shipment->getTo()->getPhone(),
                            'emailAddress' => $shipment->getTo()->getEmail(),
                        ],
                        'sender' => [
                            'tradingName' => $shipment->getFrom()->getCompanyName(),
                            'phoneNumber' => $shipment->getFrom()->getPhone(),
                            'emailAddress' => $shipment->getFrom()->getEmail(),
                        ],
                        'billing' => [
                            'address' => [
                                'fullName' => $shipment->getFrom()->getFullName(),
                                'companyName' => $shipment->getFrom()->getCompanyName(),
                                'addressLine1' => $shipment->getFrom()->getStreet1(),
                                'addressLine2' => $shipment->getFrom()->getStreet2(),
                                'addressLine3' => $shipment->getFrom()->getStreet3(),
                                'city' => $shipment->getFrom()->getCity(),
                                'county' => $shipment->getFrom()->getStateProvince(),
                                'postcode' => $shipment->getFrom()->getPostalCode(),
                                'countryCode' => $shipment->getFrom()->getCountryCode(),
                            ],
                            'phoneNumber' => $shipment->getFrom()->getPhone(),
                            'emailAddress' => $shipment->getFrom()->getEmail(),
                        ],
                        'packages' => array_map(function($package) {
                            return [
                                'weightInGrams' => (int)$package->getWeight(0),
                                'packageFormatIdentifier' => 'parcel',
                                'dimensions' => [
                                    'heightInMms' => (int)$package->getHeight(0),
                                    'widthInMms' => (int)$package->getWidth(0),
                                    'depthInMms' => (int)$package->getLength(0),
                                ],
                            ];
                        }, $shipment->getPackages()),
                        'orderDate' => (new DateTime())->format('c'),
                        'subtotal' => 0,
                        'shippingCostCharged' => 0,
                        'total' => 0,
                        'label' => [
                            'includeLabelInResponse' => true,
                            'includeCN' => true,
                            'includeReturnsLabel' => true,
                        ],
                    ],
                ],
            ];

            $request = new Request([
                'httpClient' => $this->getClickDropClient(),
                'endpoint' => 'orders',
                'payload' => [
                    'json' => $payload,
                ],
            ]);

            $data = $this->fetchLabels($request, function(Response $response) {
                return $response->json();
            });

            $labels = [];

            foreach (Arr::get($data, 'createdOrders') as $order) {
                $trackingNumber = Arr::get($order, 'trackingNumber');

                $labels[] = new Label([
                    'carrier' => $this,
                    'response' => $order,
                    'rate' => $rate,
                    'trackingNumber' => $trackingNumber,
                    'labelId' => Arr::get($order, 'orderIdentifier'),
                    'labelData' => Arr::get($order, 'label'),
                    'labelMime' => 'application/pdf',
                ]);
            }

            return new LabelResponse([
                'response' => $data,
                'labels' => $labels,
            ]);
        }

        throw new Exception('Not implemented.');
    }

    public function getHttpClient(): HttpClient
    {
        return new HttpClient([
            'base_uri' => 'https://api.royalmail.net/',
            'headers' => [
                'X-Accept-RMG-Terms' => $this->acceptTerms ? 'yes' : 'no',
                'X-IBM-HttpClient-Id' => $this->clientId,
                'X-IBM-HttpClient-Secret' => $this->clientSecret,
                'Accept' => 'application/json',
            ],
        ]);
    }

    public function getClickDropClient(): HttpClient
    {
        return new HttpClient([
            'base_uri' => 'https://api.parcel.royalmail.com/api/v1/',
            'headers' => [
                'Authorization' => $this->clickAndDropApiKey,
                'Accept' => 'application/json',
            ],
        ]);
    }


    // Private Methods
    // =========================================================================

    private function _mapTrackingStatus(string $status): string
    {
        return match ($status) {
            'IN TRANSIT' => Tracking::STATUS_IN_TRANSIT,
            'DELIVERED' => Tracking::STATUS_DELIVERED,
            default => Tracking::STATUS_UNKNOWN,
        };
    }
}

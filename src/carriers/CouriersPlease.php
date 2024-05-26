<?php
namespace verbb\shippy\carriers;

use Exception;
use Illuminate\Support\Arr;
use verbb\shippy\exceptions\InvalidRequestException;
use verbb\shippy\models\HttpClient;
use verbb\shippy\models\LabelResponse;
use verbb\shippy\models\Rate;
use verbb\shippy\models\RateResponse;
use verbb\shippy\models\Request;
use verbb\shippy\models\Response;
use verbb\shippy\models\Shipment;
use verbb\shippy\models\TrackingResponse;

class CouriersPlease extends AbstractCarrier
{
    // Static Methods
    // =========================================================================

    public static function getName(): string
    {
        return 'Couriers Please';
    }

    public static function getWeightUnit(Shipment $shipment): string
    {
        return 'kg';
    }

    public static function getDimensionUnit(Shipment $shipment): string
    {
        return 'cm';
    }

    public static function supportsTrackingStatus(): bool
    {
        return false;
    }

    public static function supportsLabels(): bool
    {
        return false;
    }


    // Properties
    // =========================================================================

    protected ?string $accountNumber = null;
    protected ?string $apiKey = null;


    // Public Methods
    // =========================================================================

    public function getAccountNumber(): ?string
    {
        return $this->accountNumber;
    }

    public function setAccountNumber(?string $accountNumber): CouriersPlease
    {
        $this->accountNumber = $accountNumber;
        return $this;
    }

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    public function setApiKey(?string $apiKey): CouriersPlease
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    /**
     * @throws InvalidRequestException
     */
    public function getRates(Shipment $shipment): ?RateResponse
    {
        $this->validate('accountNumber', 'apiKey');

        $payload = [
            'fromSuburb' => $shipment->getFrom()->getCity(),
            'fromPostcode' => $shipment->getFrom()->getPostalCode(),
            'toSuburb' => $shipment->getTo()->getCity(),
            'toPostcode' => $shipment->getTo()->getPostalCode(),

            'items' => array_map(function($package) {
                return [
                    'quantity' => 1,
                    'length' => $package->getLength(0),
                    'height' => $package->getHeight(0),
                    'width' => $package->getWidth(0),
                    'physicalWeight' => $package->getWeight(0),
                ];
            }, $shipment->getPackages()),
        ];

        $request = new Request([
            'endpoint' => 'domestic/quote',
            'payload' => [
                'json' => $payload,
            ],
        ]);

        $data = $this->fetchRates($request, function(Response $response) {
            return $response->json();
        });

        $rates = [];

        foreach ($data as $service) {
            $serviceCode = Arr::get($service, 'RateCardCode');
            $serviceName = Arr::get($service, 'RateCardDescription');
            $rate = Arr::get($service, 'CalculatedFreightCharge', 0);

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
     * @throws Exception
     */
    public function getTrackingStatus(array $trackingNumbers, array $options = []): ?TrackingResponse
    {
        throw new Exception('Not implemented.');
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
            $url = 'https://api.couriersplease.com.au/v2/';
        } else {
            $url = 'https://api-test.couriersplease.com.au/v2/';
        }

        return new HttpClient([
            'base_uri' => $url,
            'auth' => [$this->accountNumber, $this->apiKey],
        ]);
    }
}

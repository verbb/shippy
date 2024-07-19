<?php
namespace verbb\shippy\carriers;

use Exception;
use verbb\shippy\models\HttpClient;
use verbb\shippy\models\LabelResponse;
use verbb\shippy\models\Rate;
use verbb\shippy\models\RateResponse;
use verbb\shippy\models\Shipment;
use verbb\shippy\models\TrackingResponse;

class Colissimo extends AbstractCarrier
{
    // Static Methods
    // =========================================================================

    public static function getName(): string
    {
        return 'Colissimo';
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
        return "https://www.colissimo.fr/portail_colissimo/suivre.do?language=fr_FR&parcelnumber={$trackingNumber}";
    }

    public static function getServiceCodes(): array
    {
        return [
            'france' => 'France',
            'emballage-france' => 'Emballage France',
            'outremer' => 'Outre-Mer',
            'europe' => 'Europe',
            'economique-outremer' => 'Economique Outre-Mer',
            'international' => 'International',
            'emballage-international' => 'Emballage International',
        ];
    }

    public static function supportsTrackingStatus(): bool
    {
        return false;
    }

    public static function supportsLabels(): bool
    {
        return false;
    }


    // Public Methods
    // =========================================================================

    public function getRates(Shipment $shipment): ?RateResponse
    {
        $rates = [];

        foreach (self::getServiceCodes() as $serviceCode => $serviceName) {
            if ($rate = ColissimoRates::getRate($serviceCode, $this, $shipment)) {
                $rates[] = $rate;
            }
        }

        return new RateResponse([
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
        return new HttpClient();
    }
}

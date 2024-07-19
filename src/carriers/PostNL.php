<?php
namespace verbb\shippy\carriers;

use Exception;
use verbb\shippy\models\TrackingResponse;
use verbb\shippy\models\RateResponse;
use verbb\shippy\models\Rate;
use verbb\shippy\models\HttpClient;
use verbb\shippy\models\Shipment;
use verbb\shippy\models\LabelResponse;

class PostNL extends AbstractCarrier
{
    // Static Methods
    // =========================================================================

    public static function getName(): string
    {
        return 'PostNL';
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
        return "https://mijnpakket.postnl.nl/Claim?Barcode={$trackingNumber}";
    }

    public static function getServiceCodes(): array
    {
        return [
            'brief' => 'Brief',
            'brievenbuspakje' => 'Brievenbuspakje',
            'pakket-no-track-and-trace' => 'Pakket no Track & Trace',
            'pakket' => 'Pakket',
            'aangetekend' => 'Aangetekend',
            'verzekerservice' => 'Verzekerservice',
            'betaalservice' => 'Betaalservice',
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
            if ($rate = PostNLRates::getRate($serviceCode, $this, $shipment)) {
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

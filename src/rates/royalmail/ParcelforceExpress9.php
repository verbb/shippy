<?php
namespace verbb\shippy\rates\royalmail;

use verbb\shippy\carriers\RoyalMailRates;

class ParcelforceExpress9 extends RoyalMailRates
{
    // Static Methods
    // =========================================================================

    public static function getRates(string $countryCode): array
    {
        $zone = self::getZone($countryCode);

        if ($zone !== 'UK') {
            return [];
        }

        $bands = [
            '2024' => [
                'packet-200' => [
                    2000 => 5745,
                    5000 => 5745,
                    10000 => 6545,
                    15000 => 7395,
                    20000 => 7395,
                    25000 => 8795,
                    30000 => 8795,
                ],
            ],
        ];

        $boxes = [
            'packet-200' => [
                'length' => 1500,
                'width' => 750,
                'height' => 750,
                'weight' => 30000,
                'itemValue' => 500,
            ],
        ];

        $boxPricing = self::getBoxPricing($boxes, $bands);

        foreach ($boxPricing as $key => $box) {
            // 20% VAT
            if (!self::$includeVat) {
                $boxPricing[$key]['price'] = $box['price'] / 1.2;
            }
        }

        return $boxPricing;
    }
}

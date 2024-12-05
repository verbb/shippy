<?php
namespace verbb\shippy\rates\royalmail\postoffice;

use verbb\shippy\carriers\RoyalMailRates;

class ParcelforceExpress24 extends RoyalMailRates
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
                'packet-150' => [
                    2000 => 1345,
                    5000 => 1345,
                    10000 => 1645,
                    15000 => 1995,
                    20000 => 1995,
                    25000 => 2395,
                    30000 => 2395,
                ],
            ],
        ];

        $boxes = [
            'packet-150' => [
                'length' => 1500,
                'width' => 750,
                'height' => 750,
                'weight' => 30000,
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

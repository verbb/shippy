<?php
namespace verbb\shippy\rates\royalmail\postoffice;

use verbb\shippy\carriers\RoyalMailRates;

class ParcelforceExpressAm extends RoyalMailRates
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
                    2000 => 1745,
                    5000 => 1745,
                    10000 => 2045,
                    15000 => 2395,
                    20000 => 2395,
                    25000 => 2795,
                    30000 => 2795,
                ],
            ],
            '2025-10' => [
                'packet-150' => [
                    5000 => 1570,
                    10000 => 1895,
                    20000 => 2275,
                    30000 => 2716,
                ],
            ],
            '2026' => [
                'packet-150' => [
                    5000 => 1835,
                    10000 => 2150,
                    20000 => 2515,
                    30000 => 2935,
                ],
            ],
        ];

        $boxes = [
            'packet-200' => [
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

<?php
namespace verbb\shippy\rates\royalmail\postoffice;

use verbb\shippy\carriers\RoyalMailRates;

class ParcelforceExpress10 extends RoyalMailRates
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
                    2000 => 2745,
                    5000 => 2745,
                    10000 => 3045,
                    15000 => 3395,
                    20000 => 3395,
                    25000 => 3795,
                    30000 => 3795,
                ],
            ],
            '2025-10' => [
                'packet-150' => [
                    5000 => 2675,
                    10000 => 2995,
                    20000 => 3380,
                    30000 => 3820,
                ],
            ],
            '2026' => [
                'packet-150' => [
                    5000 => 2885,
                    10000 => 3200,
                    20000 => 3565,
                    30000 => 3985,
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

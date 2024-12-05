<?php
namespace verbb\shippy\rates\royalmail;

use verbb\shippy\carriers\RoyalMailRates;

class ParcelforceExpress48Large extends RoyalMailRates
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
            '2023' => [
                'packet-150' => [
                    2000 => 4795,
                    5000 => 4795,
                    10000 => 5495,
                    15000 => 6795,
                    20000 => 6795,
                    25000 => 9095,
                    30000 => 9095,
                ],
            ],
        ];

        $boxes = [
            'packet-150' => [
                'length' => 2500,
                'width' => 1250,
                'height' => 1250,
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

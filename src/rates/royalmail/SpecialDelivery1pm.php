<?php
namespace verbb\shippy\rates\royalmail;

use verbb\shippy\carriers\RoyalMailRates;

class SpecialDelivery1pm extends RoyalMailRates
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
                'packet-750' => [
                    100 => 795,
                    500 => 895,
                    1000 => 995,
                    2000 => 1275,
                    10000 => 1775,
                    20000 => 2175,
                ],
                'packet-1000' => [
                    100 => 1095,
                    500 => 1195,
                    1000 => 1295,
                    2000 => 1575,
                    10000 => 2075,
                    20000 => 2475,
                ],
                'packet-2500' => [
                    100 => 1795,
                    500 => 1895,
                    1000 => 1995,
                    2000 => 2275,
                    10000 => 2775,
                    20000 => 3175,
                ],
            ],
            '2024-10' => [
                'packet-750' => [
                    100 => 835,
                    500 => 935,
                    1000 => 1035,
                    2000 => 1335,
                    10000 => 1855,
                    20000 => 2255,
                ],
                'packet-1000' => [
                    100 => 1135,
                    500 => 1235,
                    1000 => 1335,
                    2000 => 1635,
                    10000 => 2155,
                    20000 => 2555,
                ],
                'packet-2500' => [
                    100 => 1835,
                    500 => 1935,
                    1000 => 2035,
                    2000 => 2335,
                    10000 => 2855,
                    20000 => 3255,
                ],
            ],
        ];

        $boxes = [
            'packet-750' => [
                'length' => 610,
                'width' => 460,
                'height' => 460,
                'weight' => 20000,
                'itemValue' => 750,
            ],
            'packet-1000' => [
                'length' => 610,
                'width' => 460,
                'height' => 460,
                'weight' => 20000,
                'itemValue' => 1000,
            ],
            'packet-2500' => [
                'length' => 610,
                'width' => 460,
                'height' => 460,
                'weight' => 20000,
                'itemValue' => 2500,
            ],
        ];

        return self::getBoxPricing($boxes, $bands);
    }
}

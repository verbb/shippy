<?php
namespace verbb\shippy\rates\royalmail\postoffice;

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
            '2025' => [
                'packet-750' => [
                    100 => 875,
                    500 => 985,
                    1000 => 1095,
                    2000 => 1405,
                    10000 => 1955,
                    20000 => 2375,
                ],
                'packet-1000' => [
                    100 => 1175,
                    500 => 1285,
                    1000 => 1395,
                    2000 => 1705,
                    10000 => 2255,
                    20000 => 2675,
                ],
                'packet-2500' => [
                    100 => 1875,
                    500 => 1985,
                    1000 => 2095,
                    2000 => 2405,
                    10000 => 2955,
                    20000 => 3375,
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

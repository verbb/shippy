<?php
namespace verbb\shippy\rates\royalmail\online;

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
                    100 => 775,
                    500 => 875,
                    1000 => 975,
                    2000 => 1275,
                    10000 => 1735,
                    20000 => 2135,
                ],
                'packet-1000' => [
                    100 => 1075,
                    500 => 1175,
                    1000 => 1275,
                    2000 => 1575,
                    10000 => 2035,
                    20000 => 2435,
                ],
                'packet-2500' => [
                    100 => 1775,
                    500 => 1875,
                    1000 => 1975,
                    2000 => 2275,
                    10000 => 2735,
                    20000 => 3135,
                ],
            ],
            '2025' => [
                'packet-750' => [
                    100 => 815,
                    500 => 925,
                    1000 => 1035,
                    2000 => 1345,
                    10000 => 1835,
                    20000 => 2255,
                ],
                'packet-1000' => [
                    100 => 1115,
                    500 => 1225,
                    1000 => 1335,
                    2000 => 1645,
                    10000 => 2135,
                    20000 => 2555,
                ],
                'packet-2500' => [
                    100 => 1815,
                    500 => 1925,
                    1000 => 2035,
                    2000 => 2345,
                    10000 => 2835,
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

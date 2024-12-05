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
                    100 => 735,
                    500 => 835,
                    1000 => 935,
                    2000 => 1215,
                    10000 => 1655,
                    20000 => 2055,
                ],
                'packet-1000' => [
                    100 => 1035,
                    500 => 1135,
                    1000 => 1235,
                    2000 => 1515,
                    10000 => 1955,
                    20000 => 2355,
                ],
                'packet-2500' => [
                    100 => 1735,
                    500 => 1835,
                    1000 => 1935,
                    2000 => 2215,
                    10000 => 2655,
                    20000 => 3055,
                ],
            ],
            '2024-10' => [
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

<?php
namespace verbb\shippy\rates\royalmail\online;

use verbb\shippy\carriers\RoyalMailRates;

class InternationalEconomy extends RoyalMailRates
{
    // Static Methods
    // =========================================================================

    public static function getRates(string $countryCode): array
    {
        $zone = self::getZone($countryCode);

        if ($zone === 'UK') {
            return [];
        }

        $bands = [
            '2024' => [
                self::LETTER => [
                    100 => 260,
                ],
                self::LARGE_LETTER => [
                    100 => 350,
                    250 => 585,
                    500 => 660,
                    750 => 775,
                ],
                self::PACKET => [
                    100 => 740,
                    250 => 740,
                    500 => 1030,
                    750 => 1160,
                    1000 => 1295,
                    1250 => 1425,
                    1500 => 1425,
                    2000 => 1615,
                ],
            ],
            '2025' => [
                self::LETTER => [
                    100 => 310,
                ],
                self::LARGE_LETTER => [
                    100 => 410,
                    250 => 730,
                    500 => 845,
                    750 => 1025,
                ],
                self::PACKET => [
                    250 => 925,
                    500 => 1395,
                    750 => 1440,
                    1000 => 1555,
                    1500 => 1710,
                    2000 => 2020,
                ],
            ],
            '2025-10' => [
                self::LETTER => [
                    100 => 330,
                ],
                self::LARGE_LETTER => [
                    100 => 410,
                    250 => 730,
                    500 => 845,
                    750 => 1025,
                ],
                self::PACKET => [
                    250 => 1050,
                    500 => 1620,
                    750 => 1680,
                    1000 => 1820,
                    1500 => 1980,
                    2000 => 2350,
                ],
            ],
            '2026' => [
                self::LETTER => [
                    100 => 350,
                ],
                self::LARGE_LETTER => [
                    100 => 430,
                    250 => 785,
                    500 => 1115,
                    750 => 1925,
                ],
                self::PACKET => [
                    100 => 1470,
                    250 => 1550,
                    500 => 2165,
                    750 => 2550,
                    1000 => 2890,
                    1250 => 3235,
                    1500 => 3510,
                    2000 => 3630,
                ],
            ],
        ];

        return self::getBoxPricing(self::$internationalDefaultBox, $bands, 20);
    }
}

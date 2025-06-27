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
        ];

        return self::getBoxPricing(self::$internationalDefaultBox, $bands, 20);
    }
}

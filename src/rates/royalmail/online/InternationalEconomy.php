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
                    100 => 220,
                ],
                self::LARGE_LETTER => [
                    100 => 350,
                    250 => 585,
                    500 => 660,
                    750 => 775,
                ],
                self::PACKET => [
                    100 => 745,
                    250 => 745,
                    500 => 1035,
                    750 => 1165,
                    1000 => 1300,
                    1250 => 1430,
                    1500 => 1430,
                    2000 => 1620,
                ],
            ],
            '2024-10' => [
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
        ];

        return self::getBoxPricing(self::$internationalDefaultBox, $bands, 20);
    }
}

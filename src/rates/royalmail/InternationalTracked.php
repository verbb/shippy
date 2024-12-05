<?php
namespace verbb\shippy\rates\royalmail;

use verbb\shippy\carriers\RoyalMailRates;

class InternationalTracked extends RoyalMailRates
{
    // Static Methods
    // =========================================================================

    public static function getRates(string $countryCode): array
    {
        $zone = self::getZone($countryCode);

        if ($zone === 'UK') {
            return [];
        }

        $included = ['AX', 'AD', 'AU', 'AT', 'BE', 'BR', 'CA', 'HR', 'CY', 'DK', 'EE', 'FO', 'FI', 'FR', 'DE', 'GI', 'GR', 'GL', 'HK', 'HU', 'IS', 'IN', 'IE', 'IL', 'IT', 'LV', 'LB', 'LI', 'LT', 'LU', 'MY', 'MT', 'NL', 'NZ', 'NO', 'PL', 'PT', 'RU', 'SM', 'RS', 'SG', 'SK', 'SI', 'KR', 'ES', 'SE', 'CH', 'TR', 'US', 'VA'];

        if (!in_array($countryCode, $included)) {
            return [];
        }

        $bands = [
            '2024' => [
                self::LETTER => [
                    100 => [790, 790, 790, 790, 790, 790],
                ],
                self::LARGE_LETTER => [
                    100 => [950, 950, 950, 1055, 1060, 1060],
                    250 => [1050, 1050, 1050, 1215, 1335, 1240],
                    500 => [1160, 1160, 1160, 1420, 1615, 1450],
                    750 => [1205, 1205, 1205, 1625, 1925, 1670],
                ],
                self::PACKET => [
                    250 => [1205, 1240, 1325, 1525, 1670, 1720],
                    500 => [1335, 1370, 1500, 1970, 2180, 2185],
                    750 => [1435, 1475, 1595, 2240, 2485, 2470],
                    1000 => [1500, 1545, 1670, 2530, 2810, 2765],
                    1250 => [1550, 1590, 1755, 2745, 3120, 3185],
                    1500 => [1550, 1590, 1845, 2900, 3395, 3185],
                    2000 => [1550, 1745, 1920, 3020, 3580, 3185],
                ],
                self::PRINTED_PAPERS => [
                    250 => [1205, 1240, 1325, 1525, 1670, 1720],
                    500 => [1335, 1370, 1500, 1970, 2180, 2185],
                    750 => [1435, 1475, 1595, 2240, 2485, 2470],
                    1000 => [1500, 1545, 1670, 2530, 2810, 2765],
                    1250 => [1550, 1590, 1755, 2745, 3120, 3185],
                    1500 => [1550, 1590, 1845, 2900, 3395, 3185],
                    2000 => [1550, 1745, 1920, 3020, 3580, 3185],
                ],
            ],
        ];

        return self::getInternationalBoxPricing($bands, $countryCode);
    }
}

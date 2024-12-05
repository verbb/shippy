<?php
namespace verbb\shippy\rates\royalmail\postoffice;

use verbb\shippy\carriers\RoyalMailRates;

class InternationalTrackedSigned extends RoyalMailRates
{
    // Static Methods
    // =========================================================================

    public static function getRates(string $countryCode): array
    {
        $zone = self::getZone($countryCode);

        if ($zone === 'UK') {
            return [];
        }

        $included = ['AX', 'AD', 'AR', 'AT', 'BB', 'BY', 'BE', 'BZ', 'BG', 'KH', 'CA', 'KY', 'CK', 'HR', 'CY', 'CZ', 'DK', 'EC', 'FO', 'FI', 'FR', 'GE', 'DE', 'GI', 'GR', 'GL', 'HK', 'HU', 'IS', 'ID', 'IE', 'IT', 'JP', 'LV', 'LB', 'LI', 'LT', 'LU', 'MY', 'MT', 'MD', 'NL', 'NZ', 'PL', 'PT', 'RO', 'RU', 'SM', 'RS', 'SG', 'SK', 'SI', 'KR', 'ES', 'SE', 'CH', 'TH', 'TO', 'TT', 'TR', 'UG', 'AE', 'US', 'VA'];

        if (!in_array($countryCode, $included)) {
            return [];
        }

        $bands = [
            '2024' => [
                self::LETTER => [
                    100 => [815, 815, 815, 815, 815, 815],
                ],
                self::LARGE_LETTER => [
                    100 => [965, 965, 965, 1065, 1080, 1075],
                    250 => [1090, 1090, 1090, 1230, 1350, 1250],
                    500 => [1170, 1170, 1170, 1430, 1630, 1465],
                    750 => [1215, 1215, 1215, 1635, 1935, 1685],
                ],
                self::PACKET => [
                    100 => [1375, 1390, 1530, 1770, 1900, 1845],
                    250 => [1375, 1390, 1530, 1810, 1935, 2020],
                    500 => [1520, 1560, 1700, 2240, 2430, 2615],
                    750 => [1635, 1670, 1825, 2500, 2740, 2835],
                    1000 => [1740, 1770, 1955, 2795, 3085, 3235],
                    1250 => [1800, 1805, 2025, 3015, 3355, 3615],
                    1500 => [1810, 1830, 2090, 3170, 3630, 3915],
                    2000 => [1825, 1880, 2140, 3220, 3745, 3970],
                ],
                self::PRINTED_PAPERS => [
                    100 => [1375, 1390, 1530, 1770, 1900, 1845],
                    250 => [1375, 1390, 1530, 1810, 1935, 2020],
                    500 => [1520, 1560, 1700, 2240, 2430, 2615],
                    750 => [1635, 1670, 1825, 2500, 2740, 2835],
                    1000 => [1740, 1770, 1955, 2795, 3085, 3235],
                    1250 => [1800, 1805, 2025, 3015, 3355, 3615],
                    1500 => [1810, 1830, 2090, 3170, 3630, 3915],
                    2000 => [1825, 1880, 2140, 3220, 3745, 3970],
                ],
            ],
        ];

        return self::getInternationalBoxPricing($bands, $countryCode);
    }
}

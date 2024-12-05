<?php
namespace verbb\shippy\rates\royalmail;

use verbb\shippy\carriers\RoyalMailRates;

class InternationalSigned extends RoyalMailRates
{
    // Static Methods
    // =========================================================================

    public static function getRates(string $countryCode): array
    {
        $zone = self::getZone($countryCode);

        if ($zone === 'UK') {
            return [];
        }

        $included = ['AF', 'AL', 'DZ', 'AO', 'AI', 'AG', 'AM', 'AW', 'AU', 'AZ', 'BS', 'BH', 'BD', 'BJ', 'BM', 'BT', 'BO', 'BQ', 'BA', 'BW', 'BR', 'IO', 'VG', 'BN', 'BF', 'BI', 'CM', 'CV', 'CF', 'TD', 'CL', 'CN', 'CX', 'CO', 'KM', 'CG', 'CD', 'CR', 'CU', 'CW', 'DJ', 'DM', 'DO', 'EG', 'SV', 'GQ', 'ER', 'EE', 'ET', 'FK', 'FJ', 'GF', 'PF', 'TF', 'GA', 'GM', 'GH', 'GD', 'GP', 'GT', 'GN', 'GW', 'GY', 'HT', 'HN', 'IN', 'IR', 'IQ', 'IL', 'CI', 'JM', 'JO', 'KZ', 'KE', 'KI', 'KW', 'KG', 'LA', 'LS', 'LR', 'LY', 'MO', 'MK', 'MG', 'YT', 'MW', 'MV', 'ML', 'MQ', 'MR', 'MU', 'MX', 'MN', 'ME', 'MS', 'MA', 'MZ', 'MM', 'NA', 'NR', 'NP', 'NC', 'NI', 'NE', 'NG', 'NU', 'KP', 'NO', 'OM', 'PK', 'PW', 'PA', 'PG', 'PY', 'PE', 'PH', 'PN', 'PR', 'QA', 'RE', 'RW', 'ST', 'SA', 'SN', 'SC', 'SL', 'SB', 'ZA', 'SS', 'LK', 'BQ', 'SH', 'KN', 'LC', 'MF', 'SX', 'VC', 'SD', 'SR', 'SZ', 'SY', 'TW', 'TJ', 'TZ', 'TL', 'TG', 'TK', 'TN', 'TM', 'TC', 'TV', 'UA', 'UY', 'UZ', 'VU', 'VE', 'VN', 'WF', 'EH', 'WS', 'YE', 'ZM', 'ZW'];

        if (!in_array($countryCode, $included)) {
            return [];
        }

        $bands = [
            '2024' => [
                self::LETTER => [
                    100 => [815, 815, 815, 815, 815],
                ],
                self::LARGE_LETTER => [
                    100 => [965, 965, 965, 1065, 1080],
                    250 => [1090, 1090, 1090, 1230, 1350],
                    500 => [1170, 1170, 1170, 1430, 1630],
                    750 => [1215, 1215, 1215, 1635, 1935],
                ],
                self::PACKET => [
                    100 => [1375, 1390, 1530, 1770, 1900],
                    250 => [1375, 1390, 1530, 1810, 1935],
                    500 => [1520, 1560, 1700, 2240, 2430],
                    750 => [1635, 1670, 1825, 2500, 2740],
                    1000 => [1740, 1770, 1955, 2795, 3085],
                    1250 => [1800, 1805, 2025, 3015, 3355],
                    1500 => [1810, 1830, 2090, 3170, 3630],
                    2000 => [1825, 1880, 2140, 3220, 3745],
                ],
                self::PRINTED_PAPERS => [
                    100 => [1375, 1390, 1530, 1770, 1900],
                    250 => [1375, 1390, 1530, 1810, 1935],
                    500 => [1520, 1560, 1700, 2240, 2430],
                    750 => [1635, 1670, 1825, 2500, 2740],
                    1000 => [1740, 1770, 1955, 2795, 3085],
                    1250 => [1800, 1805, 2025, 3015, 3355],
                    1500 => [1810, 1830, 2090, 3170, 3630],
                    2000 => [1825, 1880, 2140, 3220, 3745],
                ],
            ],
        ];

        return self::getInternationalBoxPricing($bands, $countryCode);
    }
}

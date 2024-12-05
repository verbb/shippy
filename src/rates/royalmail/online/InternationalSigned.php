<?php
namespace verbb\shippy\rates\royalmail\online;

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
                    250 => [1020, 1020, 1020, 1160, 1280],
                    500 => [1100, 1100, 1100, 1360, 1560],
                    750 => [1145, 1145, 1145, 1565, 1865],
                ],
                self::PACKET => [
                    100 => [1015, 970, 1095, 1540, 1330],
                    250 => [1015, 970, 1095, 1575, 1365],
                    500 => [1135, 1125, 1250, 1665, 1815],
                    750 => [1240, 1225, 1365, 1905, 2095],
                    1000 => [1335, 1315, 1490, 2170, 2410],
                    1250 => [1390, 1345, 1645, 2370, 2705],
                    1500 => [1400, 1370, 1755, 2550, 3005],
                    2000 => [1415, 1415, 2135, 2655, 3210],
                ],
            ],
        ];

        return self::getInternationalBoxPricing($bands, $countryCode);
    }
}

<?php
namespace verbb\shippy\rates\royalmail\online;

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
                    100 => [815, 815, 815, 815, 815, 815, 815, 815, 815, 815, 815, 815, 815, 815, 815, 815, 815],
                ],
                self::LARGE_LETTER => [
                    100 => [965, 965, 965, 965, 965, 1065, 1065, 1065, 1080, 1080, 1065, 965, 965, 965, 1065, 1080, 1075],
                    250 => [1020, 1020, 1020, 1020, 1020, 1160, 1160, 1160, 1280, 1280, 1160, 1020, 1020, 1020, 1160, 1280, 1180],
                    500 => [1100, 1100, 1100, 1100, 1100, 1360, 1360, 1360, 1560, 1560, 1360, 1100, 1100, 1100, 1360, 1560, 1395],
                    750 => [1145, 1145, 1145, 1145, 1145, 1565, 1565, 1565, 1865, 1865, 1565, 1145, 1145, 1145, 1565, 1865, 1615],
                ],
                self::PACKET => [
                    100 => [1015, 1015, 1015, 1015, 1015, 1540, 1540, 1540, 1330, 1330, 1540, 1015, 970, 1095, 1540, 1330, 1280],
                    250 => [1015, 1015, 1015, 1015, 1015, 1575, 1575, 1575, 1365, 1365, 1575, 1015, 970, 1095, 1575, 1365, 1440],
                    500 => [1135, 1135, 1135, 1135, 1135, 1665, 1665, 1665, 1815, 1815, 1665, 1135, 1125, 1250, 1665, 1815, 1880],
                    750 => [1240, 1240, 1240, 1240, 1240, 1905, 1905, 1905, 2095, 2095, 1905, 1240, 1225, 1365, 1905, 2095, 1880],
                    1000 => [1335, 1335, 1335, 1335, 1335, 2170, 2170, 2170, 2410, 2410, 2170, 1335, 1315, 1490, 2170, 2410, 2045],
                    1250 => [1390, 1390, 1390, 1390, 1390, 2370, 2370, 2370, 2705, 2705, 2370, 1390, 1345, 1645, 2370, 2705, 2390],
                    1500 => [1400, 1400, 1400, 1400, 1400, 2550, 2550, 2550, 3005, 3005, 2550, 1400, 1370, 1755, 2550, 3005, 2665],
                    2000 => [1415, 1415, 1415, 1415, 1415, 2655, 2655, 2655, 3210, 3210, 2655, 1415, 1415, 2135, 2655, 3210, 2715],
                ],
                self::PRINTED_PAPERS => [
                    100 => [1015, 1015, 1015, 1015, 1015, 1540, 1540, 1540, 1330, 1330, 1540, 1015, 970, 1095, 1540, 1330, 1280],
                    250 => [1015, 1015, 1015, 1015, 1015, 1575, 1575, 1575, 1365, 1365, 1575, 1015, 970, 1095, 1575, 1365, 1440],
                    500 => [1135, 1135, 1135, 1135, 1135, 1665, 1665, 1665, 1815, 1815, 1665, 1135, 1125, 1250, 1665, 1815, 1880],
                    750 => [1240, 1240, 1240, 1240, 1240, 1905, 1905, 1905, 2095, 2095, 1905, 1240, 1225, 1365, 1905, 2095, 1880],
                    1000 => [1335, 1335, 1335, 1335, 1335, 2170, 2170, 2170, 2410, 2410, 2170, 1335, 1315, 1490, 2170, 2410, 2045],
                    1250 => [1390, 1390, 1390, 1390, 1390, 2370, 2370, 2370, 2705, 2705, 2370, 1390, 1345, 1645, 2370, 2705, 2390],
                    1500 => [1400, 1400, 1400, 1400, 1400, 2550, 2550, 2550, 3005, 3005, 2550, 1400, 1370, 1755, 2550, 3005, 2665],
                    2000 => [1415, 1415, 1415, 1415, 1415, 2655, 2655, 2655, 3210, 3210, 2655, 1415, 1415, 2135, 2655, 3210, 2715],
                ],
                self::MEDIUM_PARCEL => [
                    100 => [1070, 1120, 1020, 1275, 1275, 2250, 2250, 2535, 2695, 2695, 2250, 1400, 1275, 1570, 2535, 2695, 2345],
                    250 => [1070, 1120, 1020, 1275, 1275, 2250, 2250, 2535, 2695, 2695, 2250, 1400, 1275, 1570, 2535, 2695, 2345],
                    500 => [1070, 1120, 1020, 1275, 1275, 2250, 2250, 2535, 2695, 2695, 2250, 1400, 1275, 1570, 2535, 2695, 2345],
                    750 => [1070, 1120, 1020, 1275, 1275, 2250, 2250, 2535, 2695, 2695, 2250, 1400, 1275, 1570, 2535, 2695, 2345],
                    1000 => [1070, 1120, 1020, 1275, 1275, 2250, 2250, 2535, 2695, 2695, 2250, 1400, 1275, 1570, 2535, 2695, 2345],
                    1250 => [1070, 1170, 1020, 1580, 1480, 2340, 2750, 2825, 3440, 3440, 2750, 1460, 1580, 2070, 2825, 3440, 3215],
                    1500 => [1070, 1170, 1020, 1580, 1480, 2340, 2750, 2825, 3440, 3440, 2750, 1460, 1580, 2070, 2825, 3440, 3215],
                    2000 => [1070, 1170, 1020, 1580, 1480, 2340, 2750, 2825, 3440, 3440, 2750, 1460, 1580, 2070, 2825, 3440, 3215],
                    3000 => [1090, 1260, 1180, 1830, 1830, 2745, 3200, 3200, 3810, 3810, 3200, 1665, 1830, 2575, 2925, 3810, 3340],
                    4000 => [1160, 1300, 1270, 3295, 3295, 3070, 3815, 3815, 4230, 4230, 3815, 1715, 3295, 3255, 3815, 4230, 3920],
                    5000 => [1290, 1385, 1395, 3595, 3595, 3395, 4745, 4745, 4670, 4870, 4745, 1770, 3595, 3835, 4745, 4670, 4500],
                    7500 => [1480, 1440, 1765, 5340, 5340, 4180, 6405, 6405, 6270, 6670, 6405, 2315, 5340, 5360, 6405, 6270, 6100],
                    10000 => [1605, 1675, 2130, 7310, 7310, 4910, 9780, 9780, 8600, 9100, 9780, 2815, 7310, 7320, 9780, 8600, 7600],
                    15000 => [1815, 1995, 2865, 10160, 10160, 5240, 12350, 12350, 12100, 12100, 12350, 3810, 10160, 9915, 12350, 12100, 11070],
                    20000 => [1919, 2560, 3600, 15710, 15710, 5440, 17000, 17000, 16700, 16700, 17000, 4755, 15710, 12215, 17000, 16700, 13300],
                ],
            ],
            '2024-10' => [
                self::LETTER => [
                    100 => [815, 815, 815, 815, 815, 815, 815, 815, 815, 815, 815, 815, 815, 815, 815, 815, 815],
                ],
                self::LARGE_LETTER => [
                    100 => [965, 965, 965, 965, 965, 1065, 1065, 1065, 1080, 1080, 1065, 965, 965, 965, 1065, 1080, 1075],
                    250 => [1020, 1020, 1020, 1020, 1020, 1160, 1160, 1160, 1280, 1280, 1160, 1020, 1020, 1020, 1160, 1280, 1180],
                    500 => [1100, 1100, 1100, 1100, 1100, 1360, 1360, 1360, 1560, 1560, 1360, 1100, 1100, 1100, 1360, 1560, 1395],
                    750 => [1145, 1145, 1145, 1145, 1145, 1565, 1565, 1565, 1865, 1865, 1565, 1145, 1145, 1145, 1565, 1865, 1615],
                ],
                self::PACKET => [
                    100 => [1015, 1015, 1015, 1015, 1015, 1540, 1540, 1540, 1330, 1330, 1540, 1015, 970, 1095, 1540, 1330, 1280],
                    250 => [1015, 1015, 1015, 1015, 1015, 1575, 1575, 1575, 1365, 1365, 1575, 1015, 970, 1095, 1575, 1365, 1440],
                    500 => [1135, 1135, 1135, 1135, 1135, 1665, 1665, 1665, 1815, 1815, 1665, 1135, 1125, 1250, 1665, 1815, 1880],
                    750 => [1240, 1240, 1240, 1240, 1240, 1905, 1905, 1905, 2095, 2095, 1905, 1240, 1225, 1365, 1905, 2095, 1880],
                    1000 => [1335, 1335, 1335, 1335, 1335, 2170, 2170, 2170, 2410, 2410, 2170, 1335, 1315, 1490, 2170, 2410, 2045],
                    1250 => [1390, 1390, 1390, 1390, 1390, 2370, 2370, 2370, 2705, 2705, 2370, 1390, 1345, 1645, 2370, 2705, 2390],
                    1500 => [1400, 1400, 1400, 1400, 1400, 2550, 2550, 2550, 3005, 3005, 2550, 1400, 1370, 1755, 2550, 3005, 2665],
                    2000 => [1415, 1415, 1415, 1415, 1415, 2655, 2655, 2655, 3210, 3210, 2655, 1415, 1415, 2135, 2655, 3210, 2715],
                ],
                self::PRINTED_PAPERS => [
                    100 => [1015, 1015, 1015, 1015, 1015, 1540, 1540, 1540, 1330, 1330, 1540, 1015, 970, 1095, 1540, 1330, 1280],
                    250 => [1015, 1015, 1015, 1015, 1015, 1575, 1575, 1575, 1365, 1365, 1575, 1015, 970, 1095, 1575, 1365, 1440],
                    500 => [1135, 1135, 1135, 1135, 1135, 1665, 1665, 1665, 1815, 1815, 1665, 1135, 1125, 1250, 1665, 1815, 1880],
                    750 => [1240, 1240, 1240, 1240, 1240, 1905, 1905, 1905, 2095, 2095, 1905, 1240, 1225, 1365, 1905, 2095, 1880],
                    1000 => [1335, 1335, 1335, 1335, 1335, 2170, 2170, 2170, 2410, 2410, 2170, 1335, 1315, 1490, 2170, 2410, 2045],
                    1250 => [1390, 1390, 1390, 1390, 1390, 2370, 2370, 2370, 2705, 2705, 2370, 1390, 1345, 1645, 2370, 2705, 2390],
                    1500 => [1400, 1400, 1400, 1400, 1400, 2550, 2550, 2550, 3005, 3005, 2550, 1400, 1370, 1755, 2550, 3005, 2665],
                    2000 => [1415, 1415, 1415, 1415, 1415, 2655, 2655, 2655, 3210, 3210, 2655, 1415, 1415, 2135, 2655, 3210, 2715],
                ],
                self::MEDIUM_PARCEL => [
                    100 => [1070, 1120, 1020, 1275, 1275, 2250, 2250, 2535, 2695, 2695, 2250, 1400, 1275, 1570, 2535, 2695, 2345],
                    250 => [1070, 1120, 1020, 1275, 1275, 2250, 2250, 2535, 2695, 2695, 2250, 1400, 1275, 1570, 2535, 2695, 2345],
                    500 => [1070, 1120, 1020, 1275, 1275, 2250, 2250, 2535, 2695, 2695, 2250, 1400, 1275, 1570, 2535, 2695, 2345],
                    750 => [1070, 1120, 1020, 1275, 1275, 2250, 2250, 2535, 2695, 2695, 2250, 1400, 1275, 1570, 2535, 2695, 2345],
                    1000 => [1070, 1120, 1020, 1275, 1275, 2250, 2250, 2535, 2695, 2695, 2250, 1400, 1275, 1570, 2535, 2695, 2345],
                    1250 => [1070, 1170, 1020, 1580, 1480, 2340, 2750, 2825, 3440, 3440, 2750, 1460, 1580, 2070, 2825, 3440, 3215],
                    1500 => [1070, 1170, 1020, 1580, 1480, 2340, 2750, 2825, 3440, 3440, 2750, 1460, 1580, 2070, 2825, 3440, 3215],
                    2000 => [1070, 1170, 1020, 1580, 1480, 2340, 2750, 2825, 3440, 3440, 2750, 1460, 1580, 2070, 2825, 3440, 3215],
                    3000 => [1090, 1260, 1180, 1830, 1830, 2745, 3200, 3200, 3810, 3810, 3200, 1665, 1830, 2575, 3225, 3810, 3340],
                    4000 => [1160, 1300, 1270, 3295, 3295, 3070, 3815, 3815, 4230, 4230, 3815, 1715, 3295, 3255, 3865, 4230, 3920],
                    5000 => [1290, 1385, 1395, 3595, 3595, 3395, 4745, 4745, 4670, 4870, 4745, 1770, 3595, 3835, 4845, 5080, 4500],
                    7500 => [1480, 1440, 1765, 5340, 5340, 4180, 6405, 6405, 6270, 6670, 6405, 2315, 5340, 5360, 6525, 6920, 6100],
                    10000 => [1605, 1675, 2130, 7310, 7310, 4910, 9780, 9780, 8600, 9100, 9780, 2815, 7310, 7320, 9780, 9600, 7600],
                    15000 => [1815, 1995, 2865, 10160, 10160, 5240, 12350, 12350, 12100, 12100, 12350, 3810, 10160, 9915, 12550, 13500, 11070],
                    20000 => [1919, 2560, 3600, 15710, 15710, 5440, 17000, 17000, 16700, 16700, 17000, 4755, 15710, 12215, 17300, 18700, 13300],
                ],
            ],
        ];

        return self::getInternationalTrackedBoxPricing($bands, $countryCode);
    }
}

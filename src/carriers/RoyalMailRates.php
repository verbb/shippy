<?php
namespace verbb\shippy\carriers;

use verbb\shippy\helpers\StringHelper;
use verbb\shippy\models\Rate;
use verbb\shippy\models\Shipment;
use verbb\shippy\models\StaticRates;

class RoyalMailRates extends StaticRates
{
    // Constants
    // =========================================================================

    const LETTER = 'letter';
    const LARGE_LETTER = 'large-letter';
    const SMALL_PARCEL_WIDE = 'small-parcel-wide';
    const SMALL_PARCEL_DEEP = 'small-parcel-deep';
    const SMALL_PARCEL_BIGGER = 'small-parcel-bigger';
    const TUBE = 'tube';
    const MEDIUM_PARCEL = 'medium-parcel';
    const LONG_PARCEL = 'long-parcel';
    const SQUARE_PARCEL = 'square-parcel';
    const PACKET = 'packet';
    const PARCEL = 'parcel';
    const PRINTED_PAPERS = 'printed-papers';


    // Properties
    // =========================================================================

    public static CarrierInterface $carrier;
    public static Shipment $shipment;
    public static bool $checkCompensation = false;
    public static bool $includeVat = false;

    private static array $euro = ['AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'GR', 'HR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK'];

    private static array $europeZone1 = ['IE', 'DE', 'DK', 'FR', 'MC'];
    private static array $europeZone2 = ['AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'EE', 'FI', 'GR', 'HU', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE'];
    private static array $europeZone3 = ['AL', 'AD', 'AM', 'AZ', 'BY', 'BA', 'FO', 'GE', 'GI', 'GL', 'IS', 'KZ', 'KG', 'LI', 'MD', 'ME', 'MK', 'NO', 'RU', 'SM', 'RS', 'CH', 'TJ', 'TR', 'TM', 'UA', 'UZ', 'VA'];

    private static array $worldZone2 = ['AU', 'PW', 'IO', 'CX', 'CC', 'CK', 'FJ', 'PF', 'TF', 'KI', 'MO', 'NR', 'NC', 'NZ', 'PG', 'NU', 'NF', 'LA', 'PN', 'TO', 'TV', 'WS', 'AS', 'SG', 'SB', 'TK'];

    private static array $worldZone3 = ['US'];

    private static array $farEast = ['CN', 'HK', 'MO', 'JP', 'MN', 'KP', 'KR', 'TW', 'BN', 'KH', 'TL', 'ID', 'LA', 'MY', 'MM', 'PH', 'SG', 'TH', 'VN', 'RU'];

    private static array $australasia = ['AU', 'PF', 'NU', 'TO', 'CX', 'KI', 'PG', 'TV', 'CC', 'NR', 'PN', 'VU', 'CK', 'NC', 'SB', 'WF', 'FJ', 'NZ', 'TK', 'WS'];

    private static array $europeNonEu = ['AL', 'AD', 'AM', 'AZ', 'BY', 'BA', 'GE', 'IS', 'LI', 'MD', 'MC', 'ME', 'MK', 'NO', 'RU', 'SM', 'RS', 'CH', 'TR', 'UA', 'GB', 'VA',];

    protected static array $rateYears = [
        '2019' => '2019-03-25',
        '2020' => '2020-03-23',
        '2021' => '2021-01-01',
        '2022' => '2022-04-04',
        '2023' => '2023-04-03',
    ];

    protected static array $internationalDefaultBox = [
        static::LETTER => [
            'length' => 240,
            'width' => 165,
            'height' => 5,
            'weight' => 100,
        ],
        static::LARGE_LETTER => [
            'length' => 353,
            'width' => 250,
            'height' => 25,
            'weight' => 750,
        ],
        static::LONG_PARCEL => [
            'length' => 600,
            'width' => 150,
            'height' => 150,
            'weight' => 2000,
        ],
        static::SQUARE_PARCEL => [
            'length' => 300,
            'width' => 300,
            'height' => 300,
            'weight' => 2000,
        ],
        static::PARCEL => [
            'length' => 450,
            'width' => 225,
            'height' => 225,
            'weight' => 2000,
        ],
        static::MEDIUM_PARCEL => [
            'length' => 610,
            'width' => 460,
            'height' => 460,
            'weight' => 20000,
        ],
    ];


    // Public Methods
    // =========================================================================

    public static function getRate(string $serviceCode, RoyalMail $carrier, Shipment $shipment): ?Rate
    {
        $boxRates = [];

        // Set some variables
        self::$carrier = $carrier;
        self::$shipment = $shipment;
        self::$checkCompensation = $carrier->isCheckCompensation();
        self::$includeVat = $carrier->isIncludeVat();

        // Get the function we should be using rates for
        $methodName = 'get' . StringHelper::toPascalCase($serviceCode) . 'Rates';

        // Find the rates for the destination country
        if (method_exists(self::class, $methodName) && $rateBoxes = self::$methodName($shipment->getTo()->getCountryCode())) {
            foreach ($rateBoxes as $key => &$rateBox) {
                $price = $rateBox['price'] ?? null;

                // Only return a box if it contains a price for zone
                if ($price) {
                    // All pricing in pence
                    $rateBox['price'] /= 100;

                    $boxRates[$key] = $rateBox;
                }
            }
        }

        // Given the box rates, ensure that we only return rates that the supplied shipment can fit in
        return self::getRateFromBoxRates($shipment, $carrier, $boxRates, $serviceCode);
    }


    // Protected Methods
    // =========================================================================

    protected static function getFirstClassRates(string $countryCode): array
    {
        $zone = self::getZone($countryCode);

        if ($zone !== 'UK') {
            return [];
        }

        $bands = [
            '2023' => [
                static::LETTER => [
                    100 => 110,
                ],
                static::LARGE_LETTER => [
                    100 => 160,
                    250 => 225,
                    500 => 295,
                    750 => 330,
                ],
                static::SMALL_PARCEL_WIDE => [
                    1000 => 419,
                    2000 => 419,
                ],
                static::SMALL_PARCEL_DEEP => [
                    1000 => 419,
                    2000 => 419,
                ],
                static::SMALL_PARCEL_BIGGER => [
                    1000 => 419,
                    2000 => 419,
                ],
                static::MEDIUM_PARCEL => [
                    1000 => 629,
                    2000 => 629,
                    5000 => 799,
                    10000 => 799,
                    20000 => 1199,
                ],
            ],
        ];

        $boxes = [
            static::LETTER => [
                'length' => 240,
                'width' => 165,
                'height' => 5,
                'weight' => 100,
            ],
            static::LARGE_LETTER => [
                'length' => 353,
                'width' => 250,
                'height' => 25,
                'weight' => 750,
            ],
            static::SMALL_PARCEL_WIDE => [
                'length' => 450,
                'width' => 350,
                'height' => 80,
                'weight' => 2000,
            ],
            static::SMALL_PARCEL_DEEP => [
                'length' => 350,
                'width' => 250,
                'height' => 160,
                'weight' => 2000,
            ],
            static::SMALL_PARCEL_BIGGER => [
                'length' => 450,
                'width' => 350,
                'height' => 160,
                'weight' => 2000,
            ],
            static::MEDIUM_PARCEL => [
                'length' => 610,
                'width' => 460,
                'height' => 460,
                'weight' => 20000,
            ],
        ];

        return self::getBoxPricing($boxes, $bands, 20);
    }

    protected static function getFirstClassSignedRates(string $countryCode): array
    {
        $zone = self::getZone($countryCode);

        if ($zone !== 'UK') {
            return [];
        }

        $bands = [
            '2023' => [
                static::LETTER => [
                    100 => 110,
                ],
                static::LARGE_LETTER => [
                    100 => 160,
                    250 => 225,
                    500 => 295,
                    750 => 330,
                ],
                static::SMALL_PARCEL_WIDE => [
                    1000 => 419,
                    2000 => 419,
                ],
                static::SMALL_PARCEL_DEEP => [
                    1000 => 419,
                    2000 => 419,
                ],
                static::SMALL_PARCEL_BIGGER => [
                    1000 => 419,
                    2000 => 419,
                ],
                static::MEDIUM_PARCEL => [
                    1000 => 629,
                    2000 => 629,
                    5000 => 799,
                    10000 => 799,
                    20000 => 1199,
                ],
            ],
        ];

        $boxes = [
            static::LETTER => [
                'length' => 240,
                'width' => 165,
                'height' => 5,
                'weight' => 100,
            ],
            static::LARGE_LETTER => [
                'length' => 353,
                'width' => 250,
                'height' => 25,
                'weight' => 750,
            ],
            static::SMALL_PARCEL_WIDE => [
                'length' => 450,
                'width' => 350,
                'height' => 80,
                'weight' => 2000,
            ],
            static::SMALL_PARCEL_DEEP => [
                'length' => 350,
                'width' => 250,
                'height' => 160,
                'weight' => 2000,
            ],
            static::SMALL_PARCEL_BIGGER => [
                'length' => 450,
                'width' => 350,
                'height' => 160,
                'weight' => 2000,
            ],
            static::MEDIUM_PARCEL => [
                'length' => 610,
                'width' => 460,
                'height' => 460,
                'weight' => 20000,
            ],
        ];

        $boxPricing = self::getBoxPricing($boxes, $bands, 50);

        $signedForCost = self::getValueForYear([
            '2019' => 120,
            '2020' => 130,
            '2021' => 140,
        ]);

        $signedForPackageCost = self::getValueForYear([
            '2019' => 100,
            '2022' => 110,
        ]);

        foreach ($boxPricing as $key => &$box) {
            if (str_contains($key, 'letter-')) {
                $additionalCost = $signedForCost;
            } else {
                $additionalCost = $signedForPackageCost;
            }

            if ($additionalCost) {
                $box['price'] += $additionalCost;
            }
        }

        return $boxPricing;
    }

    protected static function getSecondClassRates(string $countryCode): array
    {
        $zone = self::getZone($countryCode);

        if ($zone !== 'UK') {
            return [];
        }

        $bands = [
            '2023' => [
                static::LETTER => [
                    100 => 75,
                ],
                static::LARGE_LETTER => [
                    100 => 115,
                    250 => 185,
                    500 => 240,
                    750 => 270,
                ],
                static::SMALL_PARCEL_WIDE => [
                    1000 => 349,
                    2000 => 349,
                ],
                static::SMALL_PARCEL_DEEP => [
                    1000 => 349,
                    2000 => 349,
                ],
                static::SMALL_PARCEL_BIGGER => [
                    1000 => 349,
                    2000 => 349,
                ],
                static::MEDIUM_PARCEL => [
                    1000 => 549,
                    2000 => 549,
                    5000 => 699,
                    10000 => 699,
                    20000 => 1049,
                ],
            ],
        ];

        $boxes = [
            static::LETTER => [
                'length' => 240,
                'width' => 165,
                'height' => 5,
                'weight' => 100,
            ],
            static::LARGE_LETTER => [
                'length' => 353,
                'width' => 250,
                'height' => 25,
                'weight' => 750,
            ],
            static::SMALL_PARCEL_WIDE => [
                'length' => 450,
                'width' => 350,
                'height' => 80,
                'weight' => 2000,
            ],
            static::SMALL_PARCEL_DEEP => [
                'length' => 350,
                'width' => 250,
                'height' => 160,
                'weight' => 2000,
            ],
            static::SMALL_PARCEL_BIGGER => [
                'length' => 450,
                'width' => 350,
                'height' => 160,
                'weight' => 2000,
            ],
            static::MEDIUM_PARCEL => [
                'length' => 610,
                'width' => 460,
                'height' => 460,
                'weight' => 20000,
            ],
        ];

        return self::getBoxPricing($boxes, $bands, 20);
    }

    protected static function getSecondClassSignedRates(string $countryCode): array
    {
        $zone = self::getZone($countryCode);

        if ($zone !== 'UK') {
            return [];
        }

        $bands = [
            '2023' => [
                static::LETTER => [
                    100 => 75,
                ],
                static::LARGE_LETTER => [
                    100 => 115,
                    250 => 185,
                    500 => 240,
                    750 => 270,
                ],
                static::SMALL_PARCEL_WIDE => [
                    1000 => 349,
                    2000 => 349,
                ],
                static::SMALL_PARCEL_DEEP => [
                    1000 => 349,
                    2000 => 349,
                ],
                static::SMALL_PARCEL_BIGGER => [
                    1000 => 349,
                    2000 => 349,
                ],
                static::MEDIUM_PARCEL => [
                    1000 => 549,
                    2000 => 549,
                    5000 => 699,
                    10000 => 699,
                    20000 => 1049,
                ],
            ],
        ];

        $boxes = [
            static::LETTER => [
                'length' => 240,
                'width' => 165,
                'height' => 5,
                'weight' => 100,
            ],
            static::LARGE_LETTER => [
                'length' => 353,
                'width' => 250,
                'height' => 25,
                'weight' => 750,
            ],
            static::SMALL_PARCEL_WIDE => [
                'length' => 450,
                'width' => 350,
                'height' => 80,
                'weight' => 2000,
            ],
            static::SMALL_PARCEL_DEEP => [
                'length' => 350,
                'width' => 250,
                'height' => 160,
                'weight' => 2000,
            ],
            static::SMALL_PARCEL_BIGGER => [
                'length' => 450,
                'width' => 350,
                'height' => 160,
                'weight' => 2000,
            ],
            static::MEDIUM_PARCEL => [
                'length' => 610,
                'width' => 460,
                'height' => 460,
                'weight' => 20000,
            ],
        ];

        $boxPricing = self::getBoxPricing($boxes, $bands, 50);

        $signedForCost = self::getValueForYear([
            '2019' => 120,
            '2020' => 130,
            '2021' => 140,
        ]);

        $signedForPackageCost = self::getValueForYear([
            '2019' => 100,
            '2022' => 110,
        ]);

        foreach ($boxPricing as $key => &$box) {
            if (str_contains($key, 'letter-')) {
                $additionalCost = $signedForCost;
            } else {
                $additionalCost = $signedForPackageCost;
            }

            if ($additionalCost) {
                $box['price'] += $additionalCost;
            }
        }

        return $boxPricing;
    }

    protected static function getSpecialDelivery9amRates(string $countryCode): array
    {
        $zone = self::getZone($countryCode);

        if ($zone !== 'UK') {
            return [];
        }

        $excluded = [
            'GG', // Guernsey
            'IM', // Isle of Man
            'JE', // Jersey
        ];

        if (in_array($countryCode, $excluded)) {
            return [];
        }

        $bands = [
            '2023' => [
                'packet-50' => [
                    100 => 2395,
                    500 => 2695,
                    1000 => 2995,
                    2000 => 3595,
                ],
                'packet-1000' => [
                    100 => 2615,
                    500 => 2915,
                    1000 => 3215,
                    2000 => 3815,
                ],
                'packet-more' => [
                    100 => 2965,
                    500 => 3265,
                    1000 => 3565,
                    2000 => 4165,
                ],
            ],
        ];

        $boxes = [
            'packet-50' => [
                'length' => 610,
                'width' => 460,
                'height' => 460,
                'weight' => 2000,
            ],
            'packet-1000' => [
                'length' => 610,
                'width' => 460,
                'height' => 460,
                'weight' => 2000,
            ],
            'packet-more' => [
                'length' => 610,
                'width' => 460,
                'height' => 460,
                'weight' => 2000,
            ],
        ];

        $boxPricing = self::getBoxPricing($boxes, $bands);

        foreach ($boxPricing as $key => $box) {
            // 20% VAT
            if (!self::$includeVat) {
                $boxPricing[$key]['price'] = $box['price'] / 1.2;
            }
        }

        return $boxPricing;
    }

    protected static function getSpecialDelivery1pmRates(string $countryCode): array
    {
        $zone = self::getZone($countryCode);

        if ($zone !== 'UK') {
            return [];
        }

        $bands = [
            '2023' => [
                'packet-750' => [
                    100 => 685,
                    500 => 765,
                    1000 => 895,
                    2000 => 1115,
                    10000 => 1545,
                    20000 => 1945,
                ],
                'packet-1000' => [
                    100 => 785,
                    500 => 865,
                    1000 => 995,
                    2000 => 1215,
                    10000 => 1645,
                    20000 => 2045,
                ],
                'packet-2500' => [
                    100 => 985,
                    500 => 1065,
                    1000 => 1195,
                    2000 => 1415,
                    10000 => 1845,
                    20000 => 2245,
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

    protected static function getParcelforceExpress9Rates(string $countryCode): array
    {
        $zone = self::getZone($countryCode);

        if ($zone !== 'UK') {
            return [];
        }

        $bands = [
            '2023' => [
                'packet-200' => [
                    2000 => 4045,
                    5000 => 4045,
                    10000 => 4795,
                    15000 => 5595,
                    20000 => 5595,
                    25000 => 6395,
                    30000 => 6395,
                ],
            ],
        ];

        $boxes = [
            'packet-200' => [
                'length' => 1500,
                'width' => 750,
                'height' => 750,
                'weight' => 30000,
                'itemValue' => 500,
            ],
        ];

        $boxPricing = self::getBoxPricing($boxes, $bands);

        foreach ($boxPricing as $key => $box) {
            // 20% VAT
            if (!self::$includeVat) {
                $boxPricing[$key]['price'] = $box['price'] / 1.2;
            }
        }

        return $boxPricing;
    }

    protected static function getParcelforceExpress10Rates(string $countryCode): array
    {
        $zone = self::getZone($countryCode);

        if ($zone !== 'UK') {
            return [];
        }

        $bands = [
            '2023' => [
                'packet-200' => [
                    2000 => 2045,
                    5000 => 2045,
                    10000 => 2295,
                    15000 => 2595,
                    20000 => 2595,
                    25000 => 2895,
                    30000 => 2895,
                ],
            ],
        ];

        $boxes = [
            'packet-200' => [
                'length' => 1500,
                'width' => 750,
                'height' => 750,
                'weight' => 30000,
            ],
        ];

        $boxPricing = self::getBoxPricing($boxes, $bands);

        foreach ($boxPricing as $key => $box) {
            // 20% VAT
            if (!self::$includeVat) {
                $boxPricing[$key]['price'] = $box['price'] / 1.2;
            }
        }

        return $boxPricing;
    }

    protected static function getParcelforceExpressAmRates(string $countryCode): array
    {
        $zone = self::getZone($countryCode);

        if ($zone !== 'UK') {
            return [];
        }

        $bands = [
            '2023' => [
                'packet-200' => [
                    2000 => 1345,
                    5000 => 1345,
                    10000 => 1595,
                    15000 => 1895,
                    20000 => 1895,
                    25000 => 2195,
                    30000 => 2195,
                ],
            ],
        ];

        $boxes = [
            'packet-200' => [
                'length' => 1500,
                'width' => 750,
                'height' => 750,
                'weight' => 30000,
            ],
        ];

        $boxPricing = self::getBoxPricing($boxes, $bands);

        foreach ($boxPricing as $key => $box) {
            // 20% VAT
            if (!self::$includeVat) {
                $boxPricing[$key]['price'] = $box['price'] / 1.2;
            }
        }

        return $boxPricing;
    }

    protected static function getParcelforceExpress24Rates(string $countryCode): array
    {
        $zone = self::getZone($countryCode);

        if ($zone !== 'UK') {
            return [];
        }

        $bands = [
            '2023' => [
                'packet-100' => [
                    2000 => 1045,
                    5000 => 1045,
                    10000 => 1295,
                    15000 => 1595,
                    20000 => 1595,
                    25000 => 1895,
                    30000 => 1895,
                ],
            ],
        ];

        $boxes = [
            'packet-100' => [
                'length' => 1500,
                'width' => 750,
                'height' => 750,
                'weight' => 30000,
            ],
        ];

        $boxPricing = self::getBoxPricing($boxes, $bands);

        foreach ($boxPricing as $key => $box) {
            // 20% VAT
            if (!self::$includeVat) {
                $boxPricing[$key]['price'] = $box['price'] / 1.2;
            }
        }

        return $boxPricing;
    }

    protected static function getParcelforceExpress48Rates(string $countryCode): array
    {
        $zone = self::getZone($countryCode);

        if ($zone !== 'UK') {
            return [];
        }

        $bands = [
            '2023' => [
                'packet-150' => [
                    2000 => 995,
                    5000 => 995,
                    10000 => 1145,
                    15000 => 1345,
                    20000 => 1345,
                    25000 => 1645,
                    30000 => 1645,
                ],
            ],
        ];

        $boxes = [
            'packet-150' => [
                'length' => 1500,
                'width' => 750,
                'height' => 750,
                'weight' => 30000,
            ],
        ];

        $boxPricing = self::getBoxPricing($boxes, $bands);

        foreach ($boxPricing as $key => $box) {
            // 20% VAT
            if (!self::$includeVat) {
                $boxPricing[$key]['price'] = $box['price'] / 1.2;
            }
        }

        return $boxPricing;
    }

    protected static function getParcelforceExpress48LargeRates(string $countryCode): array
    {
        $zone = self::getZone($countryCode);

        if ($zone !== 'UK') {
            return [];
        }

        $bands = [
            '2023' => [
                'packet-150' => [
                    2000 => 3495,
                    5000 => 3495,
                    10000 => 4145,
                    15000 => 4845,
                    20000 => 4845,
                    25000 => 6645,
                    30000 => 6645,
                ],
            ],
        ];

        $boxes = [
            'packet-150' => [
                'length' => 2500,
                'width' => 1250,
                'height' => 1250,
                'weight' => 30000,
            ],
        ];

        $boxPricing = self::getBoxPricing($boxes, $bands);

        foreach ($boxPricing as $key => $box) {
            // 20% VAT
            if (!self::$includeVat) {
                $boxPricing[$key]['price'] = $box['price'] / 1.2;
            }
        }

        return $boxPricing;
    }

    protected static function getTracked24Rates(string $countryCode): array
    {
        $zone = self::getZone($countryCode);

        if ($zone !== 'UK') {
            return [];
        }

        $bands = [
            '2023' => [
                static::LARGE_LETTER => [
                    750 => 330,
                ],
                static::SMALL_PARCEL_WIDE => [
                    1000 => 445,
                    2000 => 445,
                ],
                static::SMALL_PARCEL_DEEP => [
                    1000 => 445,
                    2000 => 445,
                ],
                static::SMALL_PARCEL_BIGGER => [
                    1000 => 445,
                    2000 => 445,
                ],
                static::MEDIUM_PARCEL => [
                    1000 => 695,
                    2000 => 695,
                    5000 => 795,
                    10000 => 795,
                    20000 => 1295,
                ],
                static::TUBE => [
                    1000 => 695,
                    2000 => 695,
                    5000 => 795,
                    10000 => 795,
                    20000 => 1295,
                ],
            ],
        ];

        $boxes = [
            static::LARGE_LETTER => [
                'length' => 353,
                'width' => 250,
                'height' => 25,
                'weight' => 750,
            ],
            static::SMALL_PARCEL_WIDE => [
                'length' => 450,
                'width' => 350,
                'height' => 80,
                'weight' => 2000,
            ],
            static::SMALL_PARCEL_DEEP => [
                'length' => 350,
                'width' => 250,
                'height' => 160,
                'weight' => 2000,
            ],
            static::SMALL_PARCEL_BIGGER => [
                'length' => 450,
                'width' => 350,
                'height' => 160,
                'weight' => 2000,
            ],
            static::MEDIUM_PARCEL => [
                'length' => 610,
                'width' => 460,
                'height' => 460,
                'weight' => 20000,
            ],
            static::TUBE => [
                'length' => 900,
                'width' => 70,
                'height' => 70,
                'weight' => 2000,
            ],
        ];

        return self::getBoxPricing($boxes, $bands, 100);
    }

    protected static function getTracked48Rates(string $countryCode): array
    {
        $zone = self::getZone($countryCode);

        if ($zone !== 'UK') {
            return [];
        }

        $bands = [
            '2023' => [
                static::LARGE_LETTER => [
                    750 => 270,
                ],
                static::SMALL_PARCEL_WIDE => [
                    1000 => 335,
                    2000 => 335,
                ],
                static::SMALL_PARCEL_DEEP => [
                    1000 => 335,
                    2000 => 335,
                ],
                static::SMALL_PARCEL_BIGGER => [
                    1000 => 335,
                    2000 => 335,
                ],
                static::MEDIUM_PARCEL => [
                    1000 => 535,
                    2000 => 535,
                    5000 => 695,
                    10000 => 695,
                    20000 => 1045,
                ],
                static::TUBE => [
                    1000 => 535,
                    2000 => 535,
                    5000 => 695,
                    10000 => 695,
                    20000 => 1045,
                ],
            ],
        ];

        $boxes = [
            static::LARGE_LETTER => [
                'length' => 353,
                'width' => 250,
                'height' => 25,
                'weight' => 750,
            ],
            static::SMALL_PARCEL_WIDE => [
                'length' => 450,
                'width' => 350,
                'height' => 80,
                'weight' => 2000,
            ],
            static::SMALL_PARCEL_DEEP => [
                'length' => 350,
                'width' => 250,
                'height' => 160,
                'weight' => 2000,
            ],
            static::SMALL_PARCEL_BIGGER => [
                'length' => 450,
                'width' => 350,
                'height' => 160,
                'weight' => 2000,
            ],
            static::MEDIUM_PARCEL => [
                'length' => 610,
                'width' => 460,
                'height' => 460,
                'weight' => 20000,
            ],
            static::TUBE => [
                'length' => 900,
                'width' => 70,
                'height' => 70,
                'weight' => 2000,
            ],
        ];

        return self::getBoxPricing($boxes, $bands, 100);
    }


    protected static function getInternationalStandardRates(string $countryCode): array
    {
        $zone = self::getZone($countryCode);

        if ($zone === 'UK') {
            return [];
        }

        $bands = [
            '2023' => [
                static::LETTER => [
                    10 => [220, 220, 220, 220, 220, 220],
                    20 => [220, 220, 220, 220, 220, 220],
                    100 => [220, 220, 220, 220, 220, 220],
                ],
                static::LARGE_LETTER => [
                    100 => [325, 325, 325, 420, 420, 420],
                    250 => [495, 495, 495, 740, 750, 655],
                    500 => [595, 595, 595, 870, 1055, 900],
                    750 => [695, 695, 695, 1135, 1425, 1180],
                ],
                static::PACKET => [
                    100 => [755, 770, 840, 1000, 1130, 1135],
                    250 => [755, 770, 840, 1135, 1225, 1295],
                    500 => [945, 975, 1035, 1545, 1700, 1860],
                    750 => [1065, 1100, 1175, 1820, 2010, 2135],
                    1000 => [1185, 1215, 1315, 2100, 2335, 2500],
                    1250 => [1285, 1335, 1430, 2340, 2635, 2845],
                    1500 => [1285, 1335, 1545, 2565, 2945, 3120],
                    2000 => [1435, 1485, 1645, 2685, 3115, 3245],
                ],
                static::PRINTED_PAPERS => [
                    100 => [755, 770, 840, 1000, 1130, 1135],
                    250 => [755, 770, 840, 1135, 1225, 1295],
                    500 => [945, 975, 1035, 1545, 1700, 1860],
                    750 => [1065, 1100, 1175, 1820, 2010, 2135],
                    1000 => [1185, 1215, 1315, 2100, 2335, 2500],
                    1250 => [1285, 1335, 1430, 2340, 2635, 2845],
                    1500 => [1285, 1335, 1545, 2565, 2945, 3120],
                    2000 => [1435, 1485, 1645, 2685, 3115, 3245],
                    2250 => [1575, 1625, 1785, 2875, 3350, 3505],
                    2500 => [1715, 1765, 1925, 3065, 3585, 3765],
                    2750 => [1855, 1905, 2065, 3255, 3820, 4025],
                    3000 => [1995, 2045, 2205, 3445, 4055, 4285],
                    3250 => [2135, 2185, 2345, 3635, 4290, 4545],
                    3500 => [2275, 2325, 2485, 3825, 4525, 4805],
                    3750 => [2415, 2465, 2625, 4015, 4760, 5065],
                    4000 => [2555, 2605, 2765, 4205, 4995, 5325],
                    4250 => [2695, 2745, 2905, 4395, 5230, 5585],
                    4500 => [2835, 2885, 3045, 4585, 5465, 5845],
                    4750 => [2975, 3025, 3185, 4775, 5700, 6105],
                    5000 => [3115, 3165, 3325, 4965, 5935, 6365],
                ],
            ],
        ];

        return self::getInternationalBoxPricing($bands, $countryCode, 20);
    }

    protected static function getInternationalTrackedSignedRates(string $countryCode): array
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
            '2023' => [
                static::LETTER => [
                    10 => [740, 740, 740, 740, 740, 740],
                    20 => [740, 740, 740, 740, 740, 740],
                    100 => [740, 740, 740, 740, 740, 740],
                ],
                static::LARGE_LETTER => [
                    100 => [875, 875, 875, 970, 980, 975],
                    250 => [990, 990, 990, 1120, 1225, 1135],
                    500 => [1065, 1065, 1065, 1300, 1480, 1330],
                    750 => [1105, 1105, 1105, 1485, 1760, 1530],
                ],
                static::PACKET => [
                    100 => [1250, 1265, 1390, 1610, 1725, 1675],
                    250 => [1250, 1265, 1390, 1645, 1760, 1835],
                    500 => [1380, 1420, 1545, 2035, 2210, 2375],
                    750 => [1485, 1520, 1660, 2275, 2490, 2575],
                    1000 => [1580, 1610, 1775, 2540, 2805, 2940],
                    1250 => [1635, 1640, 1840, 2740, 3050, 3285],
                    1500 => [1645, 1665, 1900, 2880, 3300, 3560],
                    2000 => [1660, 1710, 1930, 2925, 3405, 3610],
                ],
                static::PRINTED_PAPERS => [
                    100 => [1250, 1265, 1390, 1610, 1725, 1675],
                    250 => [1250, 1265, 1390, 1645, 1760, 1835],
                    500 => [1380, 1420, 1545, 2035, 2210, 2375],
                    750 => [1485, 1520, 1660, 2275, 2490, 2575],
                    1000 => [1580, 1610, 1775, 2540, 2805, 2940],
                    1250 => [1635, 1640, 1840, 2740, 3050, 3285],
                    1500 => [1645, 1665, 1900, 2880, 3300, 3560],
                    2000 => [1660, 1710, 1930, 2925, 3405, 3610],
                    2250 => [1800, 1850, 2070, 3115, 3640, 3870],
                    2500 => [1940, 1990, 2210, 3305, 3875, 4130],
                    2750 => [2080, 2130, 2350, 3495, 4110, 4390],
                    3000 => [2220, 2270, 2490, 3685, 4345, 4650],
                    3250 => [2360, 2410, 2630, 3875, 4580, 4910],
                    3500 => [2500, 2550, 2770, 4065, 4815, 5170],
                    3750 => [2640, 2690, 2910, 4255, 5050, 5430],
                    4000 => [2780, 2830, 3050, 4445, 5285, 5690],
                    4250 => [2920, 2970, 3190, 4635, 5520, 5950],
                    4500 => [3060, 3110, 3330, 4825, 5755, 6210],
                    4750 => [3200, 3250, 3470, 5015, 5990, 6470],
                    5000 => [3340, 3390, 3610, 5205, 6225, 6730],
                ],
            ],
        ];

        return self::getInternationalBoxPricing($bands, $countryCode);
    }

    protected static function getInternationalTrackedRates(string $countryCode): array
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
            '2023' => [
                static::LETTER => [
                    10 => [720, 720, 720, 720, 720, 720],
                    20 => [720, 720, 720, 720, 720, 720],
                    100 => [720, 720, 720, 720, 720, 720],
                ],
                static::LARGE_LETTER => [
                    100 => [865, 865, 865, 960, 965, 965],
                    250 => [955, 955, 955, 1105, 1215, 1125],
                    500 => [1055, 1055, 1055, 1290, 1470, 1320],
                    750 => [1095, 1095, 1095, 1475, 1750, 1520],
                ],
                static::PACKET => [
                    100 => [1095, 1125, 1205, 1385, 1520, 1565],
                    250 => [1095, 1125, 1205, 1385, 1520, 1565],
                    500 => [1215, 1245, 1365, 1790, 1980, 1985],
                    750 => [1305, 1340, 1450, 2035, 2260, 2245],
                    1000 => [1365, 1405, 1520, 2300, 2555, 2515],
                    1250 => [1390, 1445, 1595, 2495, 2835, 2895],
                    1500 => [1390, 1445, 1675, 2635, 3085, 2895],
                    2000 => [1390, 1585, 1745, 2745, 3255, 2895],
                ],
                static::PRINTED_PAPERS => [
                    100 => [1095, 1125, 1205, 1385, 1520, 1565],
                    250 => [1095, 1125, 1205, 1385, 1520, 1565],
                    500 => [1215, 1245, 1365, 1790, 1980, 1985],
                    750 => [1305, 1340, 1450, 2035, 2260, 2245],
                    1000 => [1365, 1405, 1520, 2300, 2555, 2515],
                    1250 => [1390, 1445, 1595, 2495, 2835, 2895],
                    1500 => [1390, 1445, 1675, 2635, 3085, 2895],
                    2000 => [1390, 1585, 1745, 2745, 3255, 2895],
                    2250 => [1530, 1725, 1885, 2935, 3490, 3155],
                    2500 => [1670, 1865, 2025, 3125, 3725, 3415],
                    2750 => [1810, 2005, 2165, 3315, 3960, 3675],
                    3000 => [1950, 2145, 2305, 3505, 4195, 3935],
                    3250 => [2090, 2285, 2445, 3695, 4430, 4195],
                    3500 => [2230, 2425, 2585, 3885, 4665, 4455],
                    3750 => [2370, 2565, 2725, 4075, 4900, 4715],
                    4000 => [2510, 2705, 2865, 4265, 5135, 4975],
                    4250 => [2650, 2845, 3005, 4455, 5370, 5235],
                    4500 => [2790, 2985, 3145, 4645, 5605, 5495],
                    4750 => [2930, 3125, 3285, 4835, 5840, 5755],
                    5000 => [3070, 3265, 3425, 5025, 6075, 6015],
                ],
            ],
        ];

        return self::getInternationalBoxPricing($bands, $countryCode);
    }

    protected static function getInternationalSignedRates(string $countryCode): array
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
            '2023' => [
                static::LETTER => [
                    10 => [740, 740, 740, 740, 740],
                    20 => [740, 740, 740, 740, 740],
                    100 => [740, 740, 740, 740, 740],
                ],
                static::LARGE_LETTER => [
                    100 => [875, 875, 875, 970, 980],
                    250 => [990, 990, 990, 1120, 1225],
                    500 => [1065, 1065, 1065, 1300, 1480],
                    750 => [1105, 1105, 1105, 1485, 1760],
                ],
                static::PACKET => [
                    100 => [1250, 1265, 1390, 1610, 1725],
                    250 => [1250, 1265, 1390, 1645, 1760],
                    500 => [1380, 1420, 1545, 2035, 2210],
                    750 => [1485, 1520, 1660, 2275, 2490],
                    1000 => [1580, 1610, 1775, 2540, 2805],
                    1250 => [1635, 1640, 1840, 2740, 3050],
                    1500 => [1645, 1665, 1900, 2880, 3300],
                    2000 => [1660, 1710, 1930, 2925, 3405],
                ],
                static::PRINTED_PAPERS => [
                    100 => [1250, 1265, 1390, 1610, 1725],
                    250 => [1250, 1265, 1390, 1645, 1760],
                    500 => [1380, 1420, 1545, 2035, 2210],
                    750 => [1485, 1520, 1660, 2275, 2490],
                    1000 => [1580, 1610, 1775, 2540, 2805],
                    1250 => [1635, 1640, 1840, 2740, 3050],
                    1500 => [1645, 1665, 1900, 2880, 3300],
                    2000 => [1660, 1710, 1930, 2925, 3405],
                    2250 => [1800, 1850, 2070, 3115, 3640],
                    2500 => [1940, 1990, 2210, 3305, 3875],
                    2750 => [2080, 2130, 2350, 3495, 4110],
                    3000 => [2220, 2270, 2490, 3685, 4345],
                    3250 => [2360, 2410, 2630, 3875, 4580],
                    3500 => [2500, 2550, 2770, 4065, 4815],
                    3750 => [2640, 2690, 2910, 4255, 5050],
                    4000 => [2780, 2830, 3050, 4445, 5285],
                    4250 => [2920, 2970, 3190, 4635, 5520],
                    4500 => [3060, 3110, 3330, 4825, 5755],
                    4750 => [3200, 3250, 3470, 5015, 5990],
                    5000 => [3340, 3390, 3610, 5205, 6225],
                ],
            ],
        ];

        return self::getInternationalBoxPricing($bands, $countryCode);
    }

    protected static function getInternationalEconomyRates(string $countryCode): array
    {
        $zone = self::getZone($countryCode);

        if ($zone === 'UK') {
            return [];
        }

        $bands = [
            '2023' => [
                static::LETTER => [
                    10 => 200,
                    20 => 200,
                    100 => 200,
                ],
                static::LARGE_LETTER => [
                    100 => 320,
                    250 => 530,
                    500 => 600,
                    750 => 705,
                ],
                static::PACKET => [
                    100 => 675,
                    250 => 675,
                    500 => 940,
                    750 => 1060,
                    1000 => 1180,
                    1250 => 1280,
                    1500 => 1280,
                    1750 => 1430,
                    2000 => 1430,
                ],
                static::PRINTED_PAPERS => [
                    100 => 675,
                    250 => 675,
                    500 => 940,
                    750 => 1060,
                    1000 => 1180,
                    1250 => 1280,
                    1500 => 1280,
                    1750 => 1430,
                    2000 => 1430,
                    2250 => 1595,
                    2500 => 1760,
                    2750 => 1925,
                    3000 => 2090,
                    3250 => 2255,
                    3500 => 2420,
                    3750 => 2585,
                    4000 => 2750,
                    4250 => 2915,
                    4500 => 3080,
                    4750 => 3245,
                    5000 => 3410,
                ],
            ],
        ];

        return self::getBoxPricing(self::$internationalDefaultBox, $bands, 20);
    }

    protected static function getParcelforceIrelandexpressRates(string $countryCode): array
    {
        $zone = self::getZone($countryCode);

        if ($zone === 'UK') {
            return [];
        }

        $bands = [
            '2021' => [
                '5' => [
                    500 => 2754,
                    1000 => 2754,
                    1500 => 2754,
                    2000 => 2754,
                    2500 => 2754,
                    3000 => 2754,
                    3500 => 2754,
                    4000 => 2754,
                    4500 => 2754,
                    5000 => 2754,
                    5500 => 2886,
                    6000 => 3018,
                    6500 => 3150,
                    7000 => 3282,
                    7500 => 3414,
                    8000 => 3546,
                    8500 => 3678,
                    9000 => 3810,
                    9500 => 3942,
                    10000 => 4074,
                    10500 => 4158,
                    11000 => 4242,
                    11500 => 4326,
                    12000 => 4410,
                    12500 => 4494,
                    13000 => 4578,
                    13500 => 4662,
                    14000 => 4746,
                    14500 => 4830,
                    15000 => 4914,
                    15500 => 5022,
                    16000 => 5130,
                    16500 => 5238,
                    17000 => 5346,
                    17500 => 5454,
                    18000 => 5562,
                    18500 => 5670,
                    19000 => 5778,
                    19500 => 5886,
                    20000 => 5994,
                    20500 => 6102,
                    21000 => 6210,
                    21500 => 6318,
                    22000 => 6426,
                    22500 => 6534,
                    23000 => 6642,
                    23500 => 6750,
                    24000 => 6858,
                    24500 => 6966,
                    25000 => 7074,
                    25500 => 7182,
                    26000 => 7290,
                    26500 => 7398,
                    27000 => 7506,
                    27500 => 7614,
                    28000 => 7722,
                    28500 => 7830,
                    29000 => 7938,
                    29500 => 8046,
                    30000 => 8154,
                ],
            ],
        ];

        return self::getParcelforceBoxPricing($bands, $countryCode, [
            'maximumInclusiveCompensation' => 200,
            'maximumTotalCover' => 2500,
        ]);
    }

    protected static function getParcelforceEuropriorityRates(string $countryCode): array
    {
        $zone = self::getZone($countryCode);

        if ($zone === 'UK') {
            return [];
        }

        $bands = [
            '2021' => [
                '4' => [
                    500 => 4248,
                    1000 => 4320,
                    1500 => 4392,
                    2000 => 4464,
                    2500 => 4536,
                    3000 => 4620,
                    3500 => 4704,
                    4000 => 4788,
                    4500 => 4872,
                    5000 => 4956,
                    5500 => 4968,
                    6000 => 4980,
                    6500 => 4992,
                    7000 => 5004,
                    7500 => 5016,
                    8000 => 5028,
                    8500 => 5040,
                    9000 => 5052,
                    9500 => 5064,
                    10000 => 5076,
                    10500 => 5118,
                    11000 => 5160,
                    11500 => 5202,
                    12000 => 5244,
                    12500 => 5286,
                    13000 => 5328,
                    13500 => 5370,
                    14000 => 5412,
                    14500 => 5454,
                    15000 => 5496,
                    15500 => 5520,
                    16000 => 5544,
                    16500 => 5568,
                    17000 => 5592,
                    17500 => 5616,
                    18000 => 5640,
                    18500 => 5664,
                    19000 => 5688,
                    19500 => 5712,
                    20000 => 5736,
                    20500 => 5760,
                    21000 => 5784,
                    21500 => 5808,
                    22000 => 5832,
                    22500 => 5856,
                    23000 => 5880,
                    23500 => 5904,
                    24000 => 5928,
                    24500 => 5952,
                    25000 => 5976,
                    25500 => 6000,
                    26000 => 6024,
                    26500 => 6048,
                    27000 => 6072,
                    27500 => 6096,
                    28000 => 6120,
                    28500 => 6144,
                    29000 => 6168,
                    29500 => 6192,
                    30000 => 6216,
                ],
                '5' => [
                    500 => 4368,
                    1000 => 4470,
                    1500 => 4572,
                    2000 => 4674,
                    2500 => 4776,
                    3000 => 4974,
                    3500 => 5172,
                    4000 => 5370,
                    4500 => 5568,
                    5000 => 5766,
                    5500 => 5898,
                    6000 => 6030,
                    6500 => 6162,
                    7000 => 6294,
                    7500 => 6426,
                    8000 => 6558,
                    8500 => 6690,
                    9000 => 6822,
                    9500 => 6954,
                    10000 => 7086,
                    10500 => 7194,
                    11000 => 7302,
                    11500 => 7410,
                    12000 => 7518,
                    12500 => 7626,
                    13000 => 7734,
                    13500 => 7842,
                    14000 => 7950,
                    14500 => 8058,
                    15000 => 8166,
                    15500 => 8238,
                    16000 => 8310,
                    16500 => 8382,
                    17000 => 8454,
                    17500 => 8526,
                    18000 => 8598,
                    18500 => 8670,
                    19000 => 8742,
                    19500 => 8814,
                    20000 => 8886,
                    20500 => 9078,
                    21000 => 9030,
                    21500 => 9102,
                    22000 => 9174,
                    22500 => 9246,
                    23000 => 9318,
                    23500 => 9390,
                    24000 => 9462,
                    24500 => 9534,
                    25000 => 9606,
                    25500 => 9678,
                    26000 => 9750,
                    26500 => 9822,
                    27000 => 9894,
                    27500 => 9966,
                    28000 => 10038,
                    28500 => 10110,
                    29000 => 10182,
                    29500 => 10254,
                    30000 => 10326,
                ],
                '6' => [
                    500 => 3876,
                    1000 => 4092,
                    1500 => 4308,
                    2000 => 4524,
                    2500 => 4740,
                    3000 => 4938,
                    3500 => 5136,
                    4000 => 5334,
                    4500 => 5532,
                    5000 => 5730,
                    5500 => 5856,
                    6000 => 5982,
                    6500 => 6108,
                    7000 => 6234,
                    7500 => 6360,
                    8000 => 6486,
                    8500 => 6612,
                    9000 => 6738,
                    9500 => 6864,
                    10000 => 6990,
                    10500 => 7116,
                    11000 => 7242,
                    11500 => 7368,
                    12000 => 7494,
                    12500 => 7620,
                    13000 => 7746,
                    13500 => 7872,
                    14000 => 7998,
                    14500 => 8124,
                    15000 => 8250,
                    15500 => 8322,
                    16000 => 8394,
                    16500 => 8466,
                    17000 => 8538,
                    17500 => 8610,
                    18000 => 8682,
                    18500 => 8754,
                    19000 => 8826,
                    19500 => 8898,
                    20000 => 8970,
                    20500 => 9042,
                    21000 => 9114,
                    21500 => 9186,
                    22000 => 9258,
                    22500 => 9330,
                    23000 => 9402,
                    23500 => 9474,
                    24000 => 9546,
                    24500 => 9618,
                    25000 => 9690,
                    25500 => 9762,
                    26000 => 9834,
                    26500 => 9906,
                    27000 => 9978,
                    27500 => 10050,
                    28000 => 10122,
                    28500 => 10194,
                    29000 => 10266,
                    29500 => 10338,
                    30000 => 10410,
                ],
                '7' => [
                    500 => 4254,
                    1000 => 4506,
                    1500 => 4758,
                    2000 => 5010,
                    2500 => 5262,
                    3000 => 5490,
                    3500 => 5718,
                    4000 => 5946,
                    4500 => 6174,
                    5000 => 6402,
                    5500 => 6546,
                    6000 => 6690,
                    6500 => 6834,
                    7000 => 6978,
                    7500 => 7122,
                    8000 => 7266,
                    8500 => 7410,
                    9000 => 7554,
                    9500 => 7698,
                    10000 => 7842,
                    10500 => 7962,
                    11000 => 8082,
                    11500 => 8202,
                    12000 => 8322,
                    12500 => 8442,
                    13000 => 8562,
                    13500 => 8682,
                    14000 => 8802,
                    14500 => 8922,
                    15000 => 9042,
                    15500 => 9108,
                    16000 => 9174,
                    16500 => 9240,
                    17000 => 9306,
                    17500 => 9372,
                    18000 => 9438,
                    18500 => 9504,
                    19000 => 9570,
                    19500 => 9636,
                    20000 => 9702,
                    20500 => 9768,
                    21000 => 9834,
                    21500 => 9900,
                    22000 => 9966,
                    22500 => 10032,
                    23000 => 10098,
                    23500 => 10164,
                    24000 => 10230,
                    24500 => 10296,
                    25000 => 10362,
                    25500 => 10428,
                    26000 => 10494,
                    26500 => 10560,
                    27000 => 10626,
                    27500 => 10692,
                    28000 => 10758,
                    28500 => 10824,
                    29000 => 10890,
                    29500 => 10956,
                    30000 => 11022,
                ],
                '8' => [
                    500 => 4620,
                    1000 => 4860,
                    1500 => 5100,
                    2000 => 5340,
                    2500 => 5580,
                    3000 => 5844,
                    3500 => 6108,
                    4000 => 6372,
                    4500 => 6636,
                    5000 => 6900,
                    5500 => 7074,
                    6000 => 7248,
                    6500 => 7422,
                    7000 => 7596,
                    7500 => 7770,
                    8000 => 7944,
                    8500 => 8118,
                    9000 => 8292,
                    9500 => 8466,
                    10000 => 8640,
                    10500 => 8814,
                    11000 => 8988,
                    11500 => 9162,
                    12000 => 9336,
                    12500 => 9510,
                    13000 => 9684,
                    13500 => 9858,
                    14000 => 10032,
                    14500 => 10206,
                    15000 => 10380,
                    15500 => 10452,
                    16000 => 10524,
                    16500 => 10596,
                    17000 => 10668,
                    17500 => 10740,
                    18000 => 10812,
                    18500 => 10884,
                    19000 => 10956,
                    19500 => 11028,
                    20000 => 11100,
                    20500 => 11172,
                    21000 => 11244,
                    21500 => 11316,
                    22000 => 11388,
                    22500 => 11460,
                    23000 => 11532,
                    23500 => 11604,
                    24000 => 11676,
                    24500 => 11748,
                    25000 => 11820,
                    25500 => 11892,
                    26000 => 11964,
                    26500 => 12036,
                    27000 => 12108,
                    27500 => 12180,
                    28000 => 12252,
                    28500 => 12324,
                    29000 => 12396,
                    29500 => 12468,
                    30000 => 12540,
                ],
                '9' => [
                    500 => 5244,
                    1000 => 5544,
                    1500 => 5844,
                    2000 => 6144,
                    2500 => 6444,
                    3000 => 6732,
                    3500 => 7020,
                    4000 => 7308,
                    4500 => 7596,
                    5000 => 7884,
                    5500 => 8052,
                    6000 => 8220,
                    6500 => 8388,
                    7000 => 8556,
                    7500 => 8724,
                    8000 => 8892,
                    8500 => 9060,
                    9000 => 9228,
                    9500 => 9396,
                    10000 => 9564,
                    10500 => 9714,
                    11000 => 9864,
                    11500 => 10014,
                    12000 => 10164,
                    12500 => 10314,
                    13000 => 10464,
                    13500 => 10614,
                    14000 => 10764,
                    14500 => 10914,
                    15000 => 11064,
                    15500 => 11160,
                    16000 => 11256,
                    16500 => 11352,
                    17000 => 11448,
                    17500 => 11544,
                    18000 => 11640,
                    18500 => 11736,
                    19000 => 11832,
                    19500 => 11928,
                    20000 => 12024,
                    20500 => 12120,
                    21000 => 12216,
                    21500 => 12312,
                    22000 => 12408,
                    22500 => 12504,
                    23000 => 12600,
                    23500 => 12696,
                    24000 => 12792,
                    24500 => 12888,
                    25000 => 12984,
                    25500 => 13080,
                    26000 => 13176,
                    26500 => 13272,
                    27000 => 13368,
                    27500 => 13464,
                    28000 => 13560,
                    28500 => 13656,
                    29000 => 13752,
                    29500 => 13848,
                    30000 => 13944,
                ],
            ],
        ];

        return self::getParcelforceBoxPricing($bands, $countryCode, [
            'maximumInclusiveCompensation' => 100,
            'maximumTotalCover' => 2500,
        ]);
    }

    protected static function getParcelforceGlobaleconomyRates(string $countryCode): array
    {
        $zone = self::getZone($countryCode);

        if ($zone === 'UK') {
            return [];
        }

        $bands = [
            '2021' => [
                '4' => [
                    500 => 4620,
                    1000 => 4746,
                    1500 => 4872,
                    2000 => 4998,
                    2500 => 5124,
                    3000 => 5184,
                    3500 => 5244,
                    4000 => 5304,
                    4500 => 5364,
                    5000 => 5424,
                    5500 => 5460,
                    6000 => 5496,
                    6500 => 5532,
                    7000 => 5568,
                    7500 => 5604,
                    8000 => 5640,
                    8500 => 5676,
                    9000 => 5712,
                    9500 => 5748,
                    10000 => 5784,
                    10500 => 5796,
                    11000 => 5808,
                    11500 => 5820,
                    12000 => 5832,
                    12500 => 5844,
                    13000 => 5856,
                    13500 => 5868,
                    14000 => 5880,
                    14500 => 5892,
                    15000 => 5904,
                    15500 => 5940,
                    16000 => 5976,
                    16500 => 6012,
                    17000 => 6048,
                    17500 => 6084,
                    18000 => 6120,
                    18500 => 6156,
                    19000 => 6192,
                    19500 => 6228,
                    20000 => 6264,
                    20500 => 6300,
                    21000 => 6336,
                    21500 => 6372,
                    22000 => 6408,
                    22500 => 6444,
                    23000 => 6480,
                    23500 => 6516,
                    24000 => 6552,
                    24500 => 6588,
                    25000 => 6624,
                    25500 => 6660,
                    26000 => 6696,
                    26500 => 6732,
                    27000 => 6768,
                    27500 => 6804,
                    28000 => 6840,
                    28500 => 6876,
                    29000 => 6912,
                    29500 => 6948,
                    30000 => 6984,
                ],
                '5' => [
                    500 => 4740,
                    1000 => 4908,
                    1500 => 5076,
                    2000 => 5244,
                    2500 => 5412,
                    3000 => 5598,
                    3500 => 5784,
                    4000 => 5970,
                    4500 => 6156,
                    5000 => 6342,
                    5500 => 6492,
                    6000 => 6642,
                    6500 => 6792,
                    7000 => 6942,
                    7500 => 7092,
                    8000 => 7242,
                    8500 => 7392,
                    9000 => 7542,
                    9500 => 7692,
                    10000 => 7842,
                    10500 => 7950,
                    11000 => 8058,
                    11500 => 8166,
                    12000 => 8274,
                    12500 => 8382,
                    13000 => 8490,
                    13500 => 8598,
                    14000 => 8706,
                    14500 => 8814,
                    15000 => 8922,
                    15500 => 9018,
                    16000 => 9114,
                    16500 => 9210,
                    17000 => 9306,
                    17500 => 9402,
                    18000 => 9498,
                    18500 => 9594,
                    19000 => 9690,
                    19500 => 9786,
                    20000 => 9882,
                    20500 => 9978,
                    21000 => 10074,
                    21500 => 10170,
                    22000 => 10266,
                    22500 => 10362,
                    23000 => 10458,
                    23500 => 10554,
                    24000 => 10650,
                    24500 => 10746,
                    25000 => 10842,
                    25500 => 10938,
                    26000 => 11034,
                    26500 => 11130,
                    27000 => 11226,
                    27500 => 11322,
                    28000 => 11418,
                    28500 => 11514,
                    29000 => 11610,
                    29500 => 11706,
                    30000 => 11802,
                ],
                '6' => [
                    500 => 4368,
                    1000 => 4572,
                    1500 => 4776,
                    2000 => 4980,
                    2500 => 5184,
                    3000 => 5418,
                    3500 => 5652,
                    4000 => 5886,
                    4500 => 6120,
                    5000 => 6354,
                    5500 => 6492,
                    6000 => 6630,
                    6500 => 6768,
                    7000 => 6906,
                    7500 => 7044,
                    8000 => 7182,
                    8500 => 7320,
                    9000 => 7458,
                    9500 => 7596,
                    10000 => 7734,
                    10500 => 7866,
                    11000 => 7998,
                    11500 => 8130,
                    12000 => 8262,
                    12500 => 8394,
                    13000 => 8526,
                    13500 => 8658,
                    14000 => 8790,
                    14500 => 8922,
                    15000 => 9054,
                    15500 => 9138,
                    16000 => 9222,
                    16500 => 9306,
                    17000 => 9390,
                    17500 => 9474,
                    18000 => 9558,
                    18500 => 9642,
                    19000 => 9726,
                    19500 => 9810,
                    20000 => 9894,
                    20500 => 9978,
                    21000 => 10062,
                    21500 => 10146,
                    22000 => 10230,
                    22500 => 10314,
                    23000 => 10398,
                    23500 => 10482,
                    24000 => 10566,
                    24500 => 10650,
                    25000 => 10734,
                    25500 => 10818,
                    26000 => 10902,
                    26500 => 10986,
                    27000 => 11070,
                    27500 => 11154,
                    28000 => 11238,
                    28500 => 11322,
                    29000 => 11406,
                    29500 => 11490,
                    30000 => 11574,
                ],
                '7' => [
                    500 => 4620,
                    1000 => 4920,
                    1500 => 5220,
                    2000 => 5520,
                    2500 => 5820,
                    3000 => 6072,
                    3500 => 6324,
                    4000 => 6576,
                    4500 => 6828,
                    5000 => 7080,
                    5500 => 7248,
                    6000 => 7416,
                    6500 => 7584,
                    7000 => 7752,
                    7500 => 7920,
                    8000 => 8088,
                    8500 => 8256,
                    9000 => 8424,
                    9500 => 8592,
                    10000 => 8760,
                    10500 => 8904,
                    11000 => 9048,
                    11500 => 9192,
                    12000 => 9336,
                    12500 => 9480,
                    13000 => 9624,
                    13500 => 9768,
                    14000 => 9912,
                    14500 => 10056,
                    15000 => 10200,
                    15500 => 10272,
                    16000 => 10344,
                    16500 => 10416,
                    17000 => 10488,
                    17500 => 10560,
                    18000 => 10632,
                    18500 => 10704,
                    19000 => 10776,
                    19500 => 10848,
                    20000 => 10920,
                    20500 => 10992,
                    21000 => 11064,
                    21500 => 11136,
                    22000 => 11208,
                    22500 => 11280,
                    23000 => 11352,
                    23500 => 11424,
                    24000 => 11496,
                    24500 => 11568,
                    25000 => 11640,
                    25500 => 11712,
                    26000 => 11784,
                    26500 => 11856,
                    27000 => 11928,
                    27500 => 12000,
                    28000 => 12072,
                    28500 => 12144,
                    29000 => 12216,
                    29500 => 12288,
                    30000 => 12360,
                ],
                '8' => [
                    500 => 5118,
                    1000 => 5382,
                    1500 => 5646,
                    2000 => 5910,
                    2500 => 6174,
                    3000 => 6474,
                    3500 => 6774,
                    4000 => 7074,
                    4500 => 7374,
                    5000 => 7674,
                    5500 => 7860,
                    6000 => 8046,
                    6500 => 8232,
                    7000 => 8418,
                    7500 => 8604,
                    8000 => 8790,
                    8500 => 8976,
                    9000 => 9162,
                    9500 => 9348,
                    10000 => 9534,
                    10500 => 9726,
                    11000 => 9918,
                    11500 => 10110,
                    12000 => 10302,
                    12500 => 10494,
                    13000 => 10686,
                    13500 => 10878,
                    14000 => 11070,
                    14500 => 11262,
                    15000 => 11454,
                    15500 => 11538,
                    16000 => 11622,
                    16500 => 11706,
                    17000 => 11790,
                    17500 => 11874,
                    18000 => 11958,
                    18500 => 12042,
                    19000 => 12126,
                    19500 => 12210,
                    20000 => 12294,
                    20500 => 12378,
                    21000 => 12462,
                    21500 => 12546,
                    22000 => 12630,
                    22500 => 12714,
                    23000 => 12798,
                    23500 => 12882,
                    24000 => 12966,
                    24500 => 13050,
                    25000 => 13134,
                    25500 => 13218,
                    26000 => 13302,
                    26500 => 13386,
                    27000 => 13470,
                    27500 => 13554,
                    28000 => 13638,
                    28500 => 13722,
                    29000 => 13806,
                    29500 => 13890,
                    30000 => 13974,
                ],
                '9' => [
                    500 => 5742,
                    1000 => 6090,
                    1500 => 6438,
                    2000 => 6786,
                    2500 => 7134,
                    3000 => 7446,
                    3500 => 7758,
                    4000 => 8070,
                    4500 => 8382,
                    5000 => 8694,
                    5500 => 8886,
                    6000 => 9078,
                    6500 => 9270,
                    7000 => 9462,
                    7500 => 9654,
                    8000 => 9846,
                    8500 => 10038,
                    9000 => 10230,
                    9500 => 10422,
                    10000 => 10614,
                    10500 => 10764,
                    11000 => 10914,
                    11500 => 11064,
                    12000 => 11214,
                    12500 => 11364,
                    13000 => 11514,
                    13500 => 11664,
                    14000 => 11814,
                    14500 => 11964,
                    15000 => 12114,
                    15500 => 12222,
                    16000 => 12330,
                    16500 => 12438,
                    17000 => 12546,
                    17500 => 12654,
                    18000 => 12762,
                    18500 => 12870,
                    19000 => 12978,
                    19500 => 13086,
                    20000 => 13194,
                    20500 => 13302,
                    21000 => 13410,
                    21500 => 13518,
                    22000 => 13626,
                    22500 => 13734,
                    23000 => 13842,
                    23500 => 13950,
                    24000 => 14058,
                    24500 => 14166,
                    25000 => 14274,
                    25500 => 14382,
                    26000 => 14490,
                    26500 => 14598,
                    27000 => 14706,
                    27500 => 14814,
                    28000 => 14922,
                    28500 => 15030,
                    29000 => 15138,
                    29500 => 15246,
                    30000 => 15354,
                ],
            ],
        ];

        return self::getParcelforceBoxPricing($bands, $countryCode, [
            'maximumInclusiveCompensation' => 0,
            'maximumTotalCover' => 0,
        ]);
    }

    protected static function getParcelforceGlobalexpressRates(string $countryCode): array
    {
        $zone = self::getZone($countryCode);

        if ($zone === 'UK') {
            return [];
        }

        $bands = [
            '2021' => [
                '4' => [
                    500 => 6000,
                    1000 => 6282,
                    1500 => 6564,
                    2000 => 6846,
                    2500 => 7128,
                    3000 => 7422,
                    3500 => 7716,
                    4000 => 8010,
                    4500 => 8304,
                    5000 => 8598,
                    5500 => 8892,
                    6000 => 9186,
                    6500 => 9480,
                    7000 => 9774,
                    7500 => 10068,
                    8000 => 10362,
                    8500 => 10656,
                    9000 => 10950,
                    9500 => 11244,
                    10000 => 11538,
                    10500 => 11808,
                    11000 => 12078,
                    11500 => 12348,
                    12000 => 12618,
                    12500 => 12888,
                    13000 => 13158,
                    13500 => 13428,
                    14000 => 13698,
                    14500 => 13968,
                    15000 => 14238,
                    15500 => 14508,
                    16000 => 14778,
                    16500 => 15048,
                    17000 => 15318,
                    17500 => 15588,
                    18000 => 15858,
                    18500 => 16128,
                    19000 => 16398,
                    19500 => 16668,
                    20000 => 16938,
                    20500 => 17208,
                    21000 => 17478,
                    21500 => 17748,
                    22000 => 18018,
                    22500 => 18288,
                    23000 => 18558,
                    23500 => 18828,
                    24000 => 19098,
                    24500 => 19368,
                    25000 => 19638,
                    25500 => 19908,
                    26000 => 20178,
                    26500 => 20448,
                    27000 => 20718,
                    27500 => 20988,
                    28000 => 21258,
                    28500 => 21528,
                    29000 => 21798,
                    29500 => 22068,
                    30000 => 22338,
                ],
                '5' => [
                    500 => 5460,
                    1000 => 5742,
                    1500 => 6024,
                    2000 => 6306,
                    2500 => 6588,
                    3000 => 6870,
                    3500 => 7152,
                    4000 => 7434,
                    4500 => 7716,
                    5000 => 7998,
                    5500 => 8190,
                    6000 => 8382,
                    6500 => 8574,
                    7000 => 8766,
                    7500 => 8958,
                    8000 => 9150,
                    8500 => 9342,
                    9000 => 9534,
                    9500 => 9726,
                    10000 => 9918,
                    10500 => 10182,
                    11000 => 10446,
                    11500 => 10710,
                    12000 => 10974,
                    12500 => 11238,
                    13000 => 11502,
                    13500 => 11766,
                    14000 => 12030,
                    14500 => 12294,
                    15000 => 12558,
                    15500 => 12756,
                    16000 => 12954,
                    16500 => 13152,
                    17000 => 13350,
                    17500 => 13548,
                    18000 => 13746,
                    18500 => 13944,
                    19000 => 14142,
                    19500 => 14340,
                    20000 => 14538,
                    20500 => 14736,
                    21000 => 14934,
                    21500 => 15132,
                    22000 => 15330,
                    22500 => 15528,
                    23000 => 15726,
                    23500 => 15924,
                    24000 => 16122,
                    24500 => 16320,
                    25000 => 16518,
                    25500 => 16716,
                    26000 => 16914,
                    26500 => 17112,
                    27000 => 17310,
                    27500 => 17508,
                    28000 => 17706,
                    28500 => 17904,
                    29000 => 18102,
                    29500 => 18300,
                    30000 => 18498,
                ],
                '6' => [
                    500 => 5112,
                    1000 => 5454,
                    1500 => 5796,
                    2000 => 6138,
                    2500 => 6480,
                    3000 => 6756,
                    3500 => 7032,
                    4000 => 7308,
                    4500 => 7584,
                    5000 => 7860,
                    5500 => 8082,
                    6000 => 8304,
                    6500 => 8526,
                    7000 => 8748,
                    7500 => 8970,
                    8000 => 9192,
                    8500 => 9414,
                    9000 => 9636,
                    9500 => 9858,
                    10000 => 10080,
                    10500 => 10302,
                    11000 => 10524,
                    11500 => 10746,
                    12000 => 10968,
                    12500 => 11190,
                    13000 => 11412,
                    13500 => 11634,
                    14000 => 11856,
                    14500 => 12078,
                    15000 => 12300,
                    15500 => 12486,
                    16000 => 12672,
                    16500 => 12858,
                    17000 => 13044,
                    17500 => 13230,
                    18000 => 13416,
                    18500 => 13602,
                    19000 => 13788,
                    19500 => 13974,
                    20000 => 14160,
                    20500 => 14346,
                    21000 => 14532,
                    21500 => 14718,
                    22000 => 14904,
                    22500 => 15090,
                    23000 => 15276,
                    23500 => 15462,
                    24000 => 15648,
                    24500 => 15834,
                    25000 => 16020,
                    25500 => 16206,
                    26000 => 16392,
                    26500 => 16578,
                    27000 => 16764,
                    27500 => 16950,
                    28000 => 17136,
                    28500 => 17322,
                    29000 => 17508,
                    29500 => 17694,
                    30000 => 17880,
                ],
                '7' => [
                    500 => 5160,
                    1000 => 5556,
                    1500 => 5952,
                    2000 => 6348,
                    2500 => 6744,
                    3000 => 7074,
                    3500 => 7404,
                    4000 => 7734,
                    4500 => 8064,
                    5000 => 8394,
                    5500 => 8670,
                    6000 => 8946,
                    6500 => 9222,
                    7000 => 9498,
                    7500 => 9774,
                    8000 => 10050,
                    8500 => 10326,
                    9000 => 10602,
                    9500 => 10878,
                    10000 => 11154,
                    10500 => 11370,
                    11000 => 11586,
                    11500 => 11802,
                    12000 => 12018,
                    12500 => 12234,
                    13000 => 12450,
                    13500 => 12666,
                    14000 => 12882,
                    14500 => 13098,
                    15000 => 13314,
                    15500 => 13518,
                    16000 => 13722,
                    16500 => 13926,
                    17000 => 14130,
                    17500 => 14334,
                    18000 => 14538,
                    18500 => 14742,
                    19000 => 14946,
                    19500 => 15150,
                    20000 => 15354,
                    20500 => 15558,
                    21000 => 15762,
                    21500 => 15966,
                    22000 => 16170,
                    22500 => 16374,
                    23000 => 16578,
                    23500 => 16782,
                    24000 => 16986,
                    24500 => 17190,
                    25000 => 17394,
                    25500 => 17598,
                    26000 => 17802,
                    26500 => 18006,
                    27000 => 18210,
                    27500 => 18414,
                    28000 => 18618,
                    28500 => 18822,
                    29000 => 19026,
                    29500 => 19230,
                    30000 => 19434,
                ],
                '8' => [
                    500 => 5580,
                    1000 => 5982,
                    1500 => 6384,
                    2000 => 6786,
                    2500 => 7188,
                    3000 => 7542,
                    3500 => 7896,
                    4000 => 8250,
                    4500 => 8604,
                    5000 => 8958,
                    5500 => 9300,
                    6000 => 9642,
                    6500 => 9984,
                    7000 => 10326,
                    7500 => 10668,
                    8000 => 11010,
                    8500 => 11352,
                    9000 => 11694,
                    9500 => 12036,
                    10000 => 12378,
                    10500 => 12690,
                    11000 => 13002,
                    11500 => 13314,
                    12000 => 13626,
                    12500 => 13938,
                    13000 => 14250,
                    13500 => 14562,
                    14000 => 14874,
                    14500 => 15186,
                    15000 => 15498,
                    15500 => 15786,
                    16000 => 16074,
                    16500 => 16362,
                    17000 => 16650,
                    17500 => 16938,
                    18000 => 17226,
                    18500 => 17514,
                    19000 => 17802,
                    19500 => 18090,
                    20000 => 18378,
                    20500 => 18666,
                    21000 => 18954,
                    21500 => 19242,
                    22000 => 19530,
                    22500 => 19818,
                    23000 => 20106,
                    23500 => 20394,
                    24000 => 20682,
                    24500 => 20970,
                    25000 => 21258,
                    25500 => 21546,
                    26000 => 21834,
                    26500 => 22122,
                    27000 => 22410,
                    27500 => 22698,
                    28000 => 22986,
                    28500 => 23274,
                    29000 => 23562,
                    29500 => 23850,
                    30000 => 24138,
                ],
                '9' => [
                    500 => 5988,
                    1000 => 6558,
                    1500 => 7128,
                    2000 => 7698,
                    2500 => 8268,
                    3000 => 8802,
                    3500 => 9336,
                    4000 => 9870,
                    4500 => 10404,
                    5000 => 10938,
                    5500 => 11364,
                    6000 => 11790,
                    6500 => 12216,
                    7000 => 12642,
                    7500 => 13068,
                    8000 => 13494,
                    8500 => 13920,
                    9000 => 14346,
                    9500 => 14772,
                    10000 => 15198,
                    10500 => 15582,
                    11000 => 15966,
                    11500 => 16350,
                    12000 => 16734,
                    12500 => 17118,
                    13000 => 17502,
                    13500 => 17886,
                    14000 => 18270,
                    14500 => 18654,
                    15000 => 19038,
                    15500 => 19446,
                    16000 => 19854,
                    16500 => 20262,
                    17000 => 20670,
                    17500 => 21078,
                    18000 => 21486,
                    18500 => 21894,
                    19000 => 22302,
                    19500 => 22710,
                    20000 => 23118,
                    20500 => 23526,
                    21000 => 23934,
                    21500 => 24342,
                    22000 => 24750,
                    22500 => 25158,
                    23000 => 25566,
                    23500 => 25974,
                    24000 => 26382,
                    24500 => 26790,
                    25000 => 27198,
                    25500 => 27606,
                    26000 => 28014,
                    26500 => 28422,
                    27000 => 28830,
                    27500 => 29238,
                    28000 => 29646,
                    28500 => 30054,
                    29000 => 30462,
                    29500 => 30870,
                    30000 => 31278,
                ],
                '9_NON_EU' => [
                    500 => 5988,
                    1000 => 6558,
                    1500 => 7128,
                    2000 => 7698,
                    2500 => 8268,
                    3000 => 8802,
                    3500 => 9336,
                    4000 => 9870,
                    4500 => 10404,
                    5000 => 10938,
                    5500 => 11364,
                    6000 => 11790,
                    6500 => 12216,
                    7000 => 12642,
                    7500 => 13068,
                    8000 => 13494,
                    8500 => 13920,
                    9000 => 14346,
                    9500 => 14772,
                    10000 => 15198,
                    10500 => 15582,
                    11000 => 15966,
                    11500 => 16350,
                    12000 => 16734,
                    12500 => 17118,
                    13000 => 17502,
                    13500 => 17886,
                    14000 => 18270,
                    14500 => 18654,
                    15000 => 19038,
                    15500 => 19446,
                    16000 => 19854,
                    16500 => 20262,
                    17000 => 20670,
                    17500 => 21078,
                    18000 => 21486,
                    18500 => 21894,
                    19000 => 22302,
                    19500 => 22710,
                    20000 => 23118,
                    20500 => 23526,
                    21000 => 23934,
                    21500 => 24342,
                    22000 => 24750,
                    22500 => 25158,
                    23000 => 25566,
                    23500 => 25974,
                    24000 => 26382,
                    24500 => 26790,
                    25000 => 27198,
                    25500 => 27606,
                    26000 => 28014,
                    26500 => 28422,
                    27000 => 28830,
                    27500 => 29238,
                    28000 => 29646,
                    28500 => 30054,
                    29000 => 30462,
                    29500 => 30870,
                    30000 => 31278,
                ],
                '10' => [
                    500 => 6180,
                    1000 => 6780,
                    1500 => 7380,
                    2000 => 7980,
                    2500 => 8580,
                    3000 => 9018,
                    3500 => 9456,
                    4000 => 9894,
                    4500 => 10332,
                    5000 => 10770,
                    5500 => 11220,
                    6000 => 11670,
                    6500 => 12120,
                    7000 => 12570,
                    7500 => 13020,
                    8000 => 13470,
                    8500 => 13920,
                    9000 => 14370,
                    9500 => 14820,
                    10000 => 15270,
                    10500 => 15624,
                    11000 => 15978,
                    11500 => 16332,
                    12000 => 16686,
                    12500 => 17040,
                    13000 => 17394,
                    13500 => 17748,
                    14000 => 18102,
                    14500 => 18456,
                    15000 => 18810,
                    15500 => 19164,
                    16000 => 19518,
                    16500 => 19872,
                    17000 => 20226,
                    17500 => 20580,
                    18000 => 20934,
                    18500 => 21288,
                    19000 => 21642,
                    19500 => 21996,
                    20000 => 22350,
                    20500 => 22704,
                    21000 => 23058,
                    21500 => 23412,
                    22000 => 23766,
                    22500 => 24120,
                    23000 => 24474,
                    23500 => 24828,
                    24000 => 25182,
                    24500 => 25536,
                    25000 => 25890,
                    25500 => 26244,
                    26000 => 26598,
                    26500 => 26952,
                    27000 => 27306,
                    27500 => 27660,
                    28000 => 28014,
                    28500 => 28368,
                    29000 => 28722,
                    29500 => 29076,
                    30000 => 29430,
                ],
                '11' => [
                    500 => 7410,
                    1000 => 8166,
                    1500 => 8922,
                    2000 => 9678,
                    2500 => 10434,
                    3000 => 11112,
                    3500 => 11790,
                    4000 => 12468,
                    4500 => 13146,
                    5000 => 13824,
                    5500 => 14418,
                    6000 => 15012,
                    6500 => 15606,
                    7000 => 16200,
                    7500 => 16794,
                    8000 => 17388,
                    8500 => 17982,
                    9000 => 18576,
                    9500 => 19170,
                    10000 => 19764,
                    10500 => 20352,
                    11000 => 20940,
                    11500 => 21528,
                    12000 => 22116,
                    12500 => 22704,
                    13000 => 23292,
                    13500 => 23880,
                    14000 => 24468,
                    14500 => 25056,
                    15000 => 25644,
                    15500 => 26232,
                    16000 => 26820,
                    16500 => 27408,
                    17000 => 27996,
                    17500 => 28584,
                    18000 => 29172,
                    18500 => 29760,
                    19000 => 30348,
                    19500 => 30936,
                    20000 => 31524,
                    20500 => 32112,
                    21000 => 32700,
                    21500 => 33288,
                    22000 => 33876,
                    22500 => 34464,
                    23000 => 35052,
                    23500 => 35640,
                    24000 => 36228,
                    24500 => 36816,
                    25000 => 37404,
                    25500 => 37992,
                    26000 => 38580,
                    26500 => 39168,
                    27000 => 39756,
                    27500 => 40344,
                    28000 => 40932,
                    28500 => 41520,
                    29000 => 42108,
                    29500 => 42696,
                    30000 => 43284,
                ],
                '12' => [
                    500 => 8400,
                    1000 => 9498,
                    1500 => 10596,
                    2000 => 11694,
                    2500 => 12792,
                    3000 => 13704,
                    3500 => 14616,
                    4000 => 15528,
                    4500 => 16440,
                    5000 => 17352,
                    5500 => 18096,
                    6000 => 18840,
                    6500 => 19584,
                    7000 => 20328,
                    7500 => 21072,
                    8000 => 21816,
                    8500 => 22560,
                    9000 => 23304,
                    9500 => 24048,
                    10000 => 24792,
                    10500 => 25536,
                    11000 => 26280,
                    11500 => 27024,
                    12000 => 27768,
                    12500 => 28512,
                    13000 => 29256,
                    13500 => 30000,
                    14000 => 30744,
                    14500 => 31488,
                    15000 => 32232,
                    15500 => 32976,
                    16000 => 33720,
                    16500 => 34464,
                    17000 => 35208,
                    17500 => 35952,
                    18000 => 36696,
                    18500 => 37440,
                    19000 => 38184,
                    19500 => 38928,
                    20000 => 39672,
                    20500 => 40416,
                    21000 => 41160,
                    21500 => 41904,
                    22000 => 42648,
                    22500 => 43392,
                    23000 => 44136,
                    23500 => 44880,
                    24000 => 45624,
                    24500 => 46368,
                    25000 => 47112,
                    25500 => 47856,
                    26000 => 48600,
                    26500 => 49344,
                    27000 => 50088,
                    27500 => 50832,
                    28000 => 51576,
                    28500 => 52320,
                    29000 => 53064,
                    29500 => 53808,
                    30000 => 54552,
                ],
            ],
        ];

        return self::getParcelforceBoxPricing($bands, $countryCode, [
            'maximumInclusiveCompensation' => 200,
            'maximumTotalCover' => 2500,
        ]);
    }

    protected static function getParcelforceGlobalpriorityRates(string $countryCode): array
    {
        $zone = self::getZone($countryCode);

        if ($zone === 'UK') {
            return [];
        }

        $bands = [
            '2021' => [
                '9_NON_EU' => [
                    500 => 5700,
                    1000 => 6018,
                    1500 => 6336,
                    2000 => 6654,
                    2500 => 6972,
                    3000 => 7242,
                    3500 => 7512,
                    4000 => 7782,
                    4500 => 8052,
                    5000 => 8322,
                    5500 => 8502,
                    6000 => 8682,
                    6500 => 8862,
                    7000 => 9042,
                    7500 => 9222,
                    8000 => 9402,
                    8500 => 9582,
                    9000 => 9762,
                    9500 => 9942,
                    10000 => 10122,
                    10500 => 10302,
                    11000 => 10482,
                    11500 => 10662,
                    12000 => 10842,
                    12500 => 11022,
                    13000 => 11202,
                    13500 => 11382,
                    14000 => 11562,
                    14500 => 11742,
                    15000 => 11922,
                    15500 => 12102,
                    16000 => 12282,
                    16500 => 12462,
                    17000 => 12642,
                    17500 => 12822,
                    18000 => 13002,
                    18500 => 13182,
                    19000 => 13362,
                    19500 => 13542,
                    20000 => 13722,
                    20500 => 13902,
                    21000 => 14082,
                    21500 => 14262,
                    22000 => 14442,
                    22500 => 14622,
                    23000 => 14802,
                    23500 => 14982,
                    24000 => 15162,
                    24500 => 15342,
                    25000 => 15522,
                    25500 => 15702,
                    26000 => 15882,
                    26500 => 16062,
                    27000 => 16242,
                    27500 => 16422,
                    28000 => 16602,
                    28500 => 16782,
                    29000 => 16962,
                    29500 => 17142,
                    30000 => 17322,
                ],
                '10' => [
                    500 => 5880,
                    1000 => 6420,
                    1500 => 6960,
                    2000 => 7500,
                    2500 => 8040,
                    3000 => 8538,
                    3500 => 9036,
                    4000 => 9534,
                    4500 => 10032,
                    5000 => 10530,
                    5500 => 10908,
                    6000 => 11286,
                    6500 => 11664,
                    7000 => 12042,
                    7500 => 12420,
                    8000 => 12798,
                    8500 => 13176,
                    9000 => 13554,
                    9500 => 13932,
                    10000 => 14310,
                    10500 => 14616,
                    11000 => 14922,
                    11500 => 15228,
                    12000 => 15534,
                    12500 => 15840,
                    13000 => 16146,
                    13500 => 16452,
                    14000 => 16758,
                    14500 => 17064,
                    15000 => 17370,
                    15500 => 17676,
                    16000 => 17982,
                    16500 => 18288,
                    17000 => 18594,
                    17500 => 18900,
                    18000 => 19206,
                    18500 => 19512,
                    19000 => 19818,
                    19500 => 20124,
                    20000 => 20430,
                    20500 => 20736,
                    21000 => 21042,
                    21500 => 21348,
                    22000 => 21654,
                    22500 => 21960,
                    23000 => 22266,
                    23500 => 22572,
                    24000 => 22878,
                    24500 => 23184,
                    25000 => 23490,
                    25500 => 23796,
                    26000 => 24102,
                    26500 => 24408,
                    27000 => 24714,
                    27500 => 25020,
                    28000 => 25326,
                    28500 => 25632,
                    29000 => 25938,
                    29500 => 26244,
                    30000 => 26550,
                ],
                '11' => [
                    500 => 6840,
                    1000 => 7476,
                    1500 => 8112,
                    2000 => 8748,
                    2500 => 9384,
                    3000 => 10002,
                    3500 => 10620,
                    4000 => 11238,
                    4500 => 11856,
                    5000 => 12474,
                    5500 => 13038,
                    6000 => 13602,
                    6500 => 14166,
                    7000 => 14730,
                    7500 => 15294,
                    8000 => 15858,
                    8500 => 16422,
                    9000 => 16986,
                    9500 => 17550,
                    10000 => 18114,
                    10500 => 18516,
                    11000 => 18918,
                    11500 => 19320,
                    12000 => 19722,
                    12500 => 20124,
                    13000 => 20526,
                    13500 => 20928,
                    14000 => 21330,
                    14500 => 21732,
                    15000 => 22134,
                    15500 => 22602,
                    16000 => 23070,
                    16500 => 23538,
                    17000 => 24006,
                    17500 => 24474,
                    18000 => 24942,
                    18500 => 25410,
                    19000 => 25878,
                    19500 => 26346,
                    20000 => 26814,
                    20500 => 27282,
                    21000 => 27750,
                    21500 => 28218,
                    22000 => 28686,
                    22500 => 29154,
                    23000 => 29622,
                    23500 => 30090,
                    24000 => 30558,
                    24500 => 31026,
                    25000 => 31494,
                    25500 => 31962,
                    26000 => 32430,
                    26500 => 32898,
                    27000 => 33366,
                    27500 => 33834,
                    28000 => 34302,
                    28500 => 34770,
                    29000 => 35238,
                    29500 => 35706,
                    30000 => 36174,
                ],
                '12' => [
                    500 => 7440,
                    1000 => 8280,
                    1500 => 9120,
                    2000 => 9960,
                    2500 => 10800,
                    3000 => 11640,
                    3500 => 12480,
                    4000 => 13320,
                    4500 => 14160,
                    5000 => 15000,
                    5500 => 15762,
                    6000 => 16524,
                    6500 => 17286,
                    7000 => 18048,
                    7500 => 18810,
                    8000 => 19572,
                    8500 => 20334,
                    9000 => 21096,
                    9500 => 21858,
                    10000 => 22620,
                    10500 => 23286,
                    11000 => 23952,
                    11500 => 24618,
                    12000 => 25284,
                    12500 => 25950,
                    13000 => 26616,
                    13500 => 27282,
                    14000 => 27948,
                    14500 => 28614,
                    15000 => 29280,
                    15500 => 29946,
                    16000 => 30612,
                    16500 => 31278,
                    17000 => 31944,
                    17500 => 32610,
                    18000 => 33276,
                    18500 => 33942,
                    19000 => 34608,
                    19500 => 35274,
                    20000 => 35940,
                    20500 => 36606,
                    21000 => 37272,
                    21500 => 37938,
                    22000 => 38604,
                    22500 => 39270,
                    23000 => 39936,
                    23500 => 40602,
                    24000 => 41268,
                    24500 => 41934,
                    25000 => 42600,
                    25500 => 43266,
                    26000 => 43932,
                    26500 => 44598,
                    27000 => 45264,
                    27500 => 45930,
                    28000 => 46596,
                    28500 => 47262,
                    29000 => 47928,
                    29500 => 48594,
                    30000 => 49260,
                ],
            ],
        ];

        return self::getParcelforceBoxPricing($bands, $countryCode, [
            'maximumInclusiveCompensation' => 100,
            'maximumTotalCover' => 2500,
        ]);
    }

    protected static function getParcelforceGlobalvalueRates(string $countryCode): array
    {
        $zone = self::getZone($countryCode);

        if ($zone === 'UK') {
            return [];
        }

        $bands = [
            '2021' => [
                '10' => [
                    500 => 5586,
                    1000 => 6102,
                    1500 => 6618,
                    2000 => 7134,
                    2500 => 7650,
                    3000 => 8124,
                    3500 => 8598,
                    4000 => 9072,
                    4500 => 9546,
                    5000 => 10020,
                    5500 => 10380,
                    6000 => 10740,
                    6500 => 11100,
                    7000 => 11460,
                    7500 => 11820,
                    8000 => 12180,
                    8500 => 12540,
                    9000 => 12900,
                    9500 => 13260,
                    10000 => 13620,
                    10500 => 13914,
                    11000 => 14208,
                    11500 => 14502,
                    12000 => 14796,
                    12500 => 15090,
                    13000 => 15384,
                    13500 => 15678,
                    14000 => 15972,
                    14500 => 16266,
                    15000 => 16560,
                    15500 => 16854,
                    16000 => 17148,
                    16500 => 17442,
                    17000 => 17736,
                    17500 => 18030,
                    18000 => 18324,
                    18500 => 18618,
                    19000 => 18912,
                    19500 => 19206,
                    20000 => 19500,
                    20500 => 19794,
                    21000 => 20088,
                    21500 => 20382,
                    22000 => 20676,
                    22500 => 20970,
                    23000 => 21264,
                    23500 => 21558,
                    24000 => 21852,
                    24500 => 22146,
                    25000 => 22440,
                    25500 => 22734,
                    26000 => 23028,
                    26500 => 23322,
                    27000 => 23616,
                    27500 => 23910,
                    28000 => 24204,
                    28500 => 24498,
                    29000 => 24792,
                    29500 => 25086,
                    30000 => 25380,
                ],
                '11' => [
                    500 => 6498,
                    1000 => 7104,
                    1500 => 7710,
                    2000 => 8316,
                    2500 => 8922,
                    3000 => 9510,
                    3500 => 10098,
                    4000 => 10686,
                    4500 => 11274,
                    5000 => 11862,
                    5500 => 12402,
                    6000 => 12942,
                    6500 => 13482,
                    7000 => 14022,
                    7500 => 14562,
                    8000 => 15102,
                    8500 => 15642,
                    9000 => 16182,
                    9500 => 16722,
                    10000 => 17262,
                    10500 => 17646,
                    11000 => 18030,
                    11500 => 18414,
                    12000 => 18798,
                    12500 => 19182,
                    13000 => 19566,
                    13500 => 19950,
                    14000 => 20334,
                    14500 => 20718,
                    15000 => 21102,
                    15500 => 21552,
                    16000 => 22002,
                    16500 => 22452,
                    17000 => 22902,
                    17500 => 23352,
                    18000 => 23802,
                    18500 => 24252,
                    19000 => 24702,
                    19500 => 25152,
                    20000 => 25602,
                    20500 => 26052,
                    21000 => 26502,
                    21500 => 26952,
                    22000 => 27402,
                    22500 => 27852,
                    23000 => 28302,
                    23500 => 28752,
                    24000 => 29202,
                    24500 => 29652,
                    25000 => 30102,
                    25500 => 30552,
                    26000 => 31002,
                    26500 => 31452,
                    27000 => 31902,
                    27500 => 32352,
                    28000 => 32802,
                    28500 => 33252,
                    29000 => 33702,
                    29500 => 34152,
                    30000 => 34602,
                ],
                '12' => [
                    500 => 7068,
                    1000 => 7866,
                    1500 => 8664,
                    2000 => 9462,
                    2500 => 10260,
                    3000 => 11058,
                    3500 => 11856,
                    4000 => 12654,
                    4500 => 13452,
                    5000 => 14250,
                    5500 => 14976,
                    6000 => 15702,
                    6500 => 16428,
                    7000 => 17154,
                    7500 => 17880,
                    8000 => 18606,
                    8500 => 19332,
                    9000 => 20058,
                    9500 => 20784,
                    10000 => 21510,
                    10500 => 22146,
                    11000 => 22782,
                    11500 => 23418,
                    12000 => 24054,
                    12500 => 24690,
                    13000 => 25326,
                    13500 => 25962,
                    14000 => 26598,
                    14500 => 27234,
                    15000 => 27870,
                    15500 => 28506,
                    16000 => 29142,
                    16500 => 29778,
                    17000 => 30414,
                    17500 => 31050,
                    18000 => 31686,
                    18500 => 32322,
                    19000 => 32958,
                    19500 => 33594,
                    20000 => 34230,
                    20500 => 34866,
                    21000 => 35502,
                    21500 => 36138,
                    22000 => 36774,
                    22500 => 37410,
                    23000 => 38046,
                    23500 => 38682,
                    24000 => 39318,
                    24500 => 39954,
                    25000 => 40590,
                    25500 => 41226,
                    26000 => 41862,
                    26500 => 42498,
                    27000 => 43134,
                    27500 => 43770,
                    28000 => 44406,
                    28500 => 45042,
                    29000 => 45678,
                    29500 => 46314,
                    30000 => 46950,
                ],
            ],
        ];

        return self::getParcelforceBoxPricing($bands, $countryCode, [
            'maximumInclusiveCompensation' => 100,
            'maximumTotalCover' => 500,
        ]);
    }


    // Private Methods
    // =========================================================================

    private static function getAllEuropeanCountries(): array
    {
        return array_merge(self::$europeZone1, self::$europeZone2, self::$europeZone3);
    }

    private static function getZone(string $countryCode): string
    {
        if ($countryCode === 'GB') {
            return 'UK';
        } else if (in_array($countryCode, self::$europeZone1)) {
            return 'EUR_1';
        } else if (in_array($countryCode, self::$europeZone2)) {
            return 'EUR_2';
        } else if (in_array($countryCode, self::$europeZone3)) {
            return 'EUR_3';
        } else if (in_array($countryCode, self::$euro)) {
            return 'EU';
        } else if (in_array($countryCode, self::$worldZone2)) {
            return '2';
        } else if (in_array($countryCode, self::$worldZone3)) {
            return '3';
        }

        return '1';
    }

    private static function getParcelforceZone(string $countryCode): string
    {
        if (in_array($countryCode, ['JE', 'GG', 'IM'])) {
            return '4';
        } else if ('IE' === $countryCode) {
            return '5';
        } else if (in_array($countryCode, ['BE', 'NL', 'LU'])) {
            return '6';
        } else if (in_array($countryCode, ['FR', 'DE', 'DK'])) {
            return '7';
        } else if (in_array($countryCode, ['IT', 'ES', 'PT', 'GR'])) {
            return '8';
        } else if (in_array($countryCode, self::$euro)) {
            return '9';
        } else if (in_array($countryCode, self::$europeNonEu)) {
            return '9_NON_EU';
        } else if (in_array($countryCode, ['US', 'CA'])) {
            return '10';
        } else if (in_array($countryCode, self::$farEast)) {
            return '11';
        } else if (in_array($countryCode, self::$australasia)) {
            return '11';
        }

        return '12';
    }

    private static function getRateYear(): int|string|null
    {
        // Get the last item as default
        $currentYear = key(array_slice(self::$rateYears, -1, 1, true));

        foreach (self::$rateYears as $year => $start) {
            if (date('Y-m-d') > strtotime($start)) {
                $currentYear = $year;
            }
        }

        return $currentYear;
    }

    private static function getValueForYear($array)
    {
        // Get the pricing as applicable
        $year = self::getRateYear();

        // Is there a rate for this year?
        $value = $array[$year] ?? null;

        // Try and find any previous years
        if (!$value) {
            $value = end($array);
        }

        return $value;
    }

    private static function getBoxPricing(array $boxes, array $bands, int $maxCompensation = 0): array
    {
        // Get the pricing as applicable
        $pricingBand = self::getValueForYear($bands);

        $boxesWithPricing = [];

        // Get pricing for this year and for each box
        foreach ($boxes as $key => $box) {
            $prices = $pricingBand[$key] ?? $pricingBand[static::PACKET] ?? [];

            // For ease-of-use, create multiple boxes for each weight
            foreach ($prices as $weight => $price) {
                $newKey = $key . '-' . $weight;
                $newBox = $box;
                $newBox['weight'] = $weight;
                $newBox['price'] = $price;

                // Check for max compensation
                if (self::$checkCompensation && $maxCompensation && $price > $maxCompensation) {
                    continue;
                }

                $boxesWithPricing[$newKey] = $newBox;
            }
        }

        return $boxesWithPricing;
    }

    private static function getInternationalBoxPricing(array $bands, string $countryCode, int $maxCompensation = 0): array
    {
        $boxes = self::$internationalDefaultBox;

        // Prices will be in international format, so grab the right one.
        // Europe, Zone 1, Zone 2, Zone 3 (previously Zone 1)
        $boxPricing = self::getBoxPricing($boxes, $bands, $maxCompensation);
        $zone = self::getZone($countryCode);

        foreach ($boxPricing as $key => &$box) {
            if ($zone === 'EUR_1') {
                $box['price'] = $box['price'][0];
            } else if ($zone === 'EUR_2') {
                $box['price'] = $box['price'][1];
            } else if ($zone === 'EUR_3' || $zone === 'EU') {
                $box['price'] = $box['price'][2];
            } else if ($zone === '1') {
                $box['price'] = $box['price'][3];
            } else if ($zone === '2') {
                $box['price'] = $box['price'][4];
            } else if ($zone === '3') {
                // Fallback to zone 1 for older prices.
                $box['price'] = $box['price'][5] ?? $box['price'][3];
            } else {
                // No price for this country
                unset($boxPricing[$key]);
            }
        }

        return $boxPricing;
    }

    private static function getParcelforceBoxPricing(array $bands, string $countryCode, array $options = []): array
    {
        $boxesWithPricing = [];
        $maximumTotalCover = $options['maximumTotalCover'] ?? 0;
        $maximumInclusiveCompensation = $options['maximumInclusiveCompensation'] ?? 0;

        $zone = self::getParcelforceZone($countryCode);

        // Get the pricing as applicable
        $pricingBand = self::getValueForYear($bands);

        // Get the pricing band for the zone
        $pricing = $pricingBand[$zone] ?? [];

        if (!$pricing) {
            return [];
        }

        $totalValuedItems = 0;
        $totalActualWeight = 0;
        $totalVolumetricWeight = 0;

        foreach (self::$shipment->getPackages() as $package) {
            $totalValuedItems += $package->getPrice();
            $totalActualWeight += $package->getWeight();
            $totalVolumetricWeight += self::getVolumetricWeight($package->getLength(), $package->getWidth(), $package->getHeight());
        }

        $chargeableWeight = ($totalActualWeight > $totalVolumetricWeight) ? $totalActualWeight : $totalVolumetricWeight;

        foreach ($pricing as $maxWeight => $price) {
            if ($chargeableWeight <= $maxWeight) {
                // Don't return the quote if valued items is greater than maximum total
                // cover of the service.
                if ($maximumTotalCover > 0 && $totalValuedItems > $maximumTotalCover) {
                    return [];
                }

                // Additional compensation cost.
                $price += self::getAdditionalCompensationCost($totalValuedItems, $maximumInclusiveCompensation);

                // Rate includes VAT.
                if (!self::$includeVat) {
                    $price /= 1.2;
                }

                // There are no boxes, so make some large-ish ones. It's weight-based
                $key = 'Weighted-Box-' . $maxWeight;

                $boxesWithPricing[$key] = [
                    'length' => 1000,
                    'width' => 1000,
                    'height' => 1000,
                    'weight' => $maxWeight,
                    'price' => $price,
                ];
            }
        }

        return $boxesWithPricing;
    }

    private static function getVolumetricWeight($l, $w, $h): float|int
    {
        return ($l * $w * $h) / 5000;
    }

    private static function getAdditionalCompensationCost($valuedItem, $maximumInclusiveCompensation): float|int
    {
        // No compensation included for globaleconomy service and if it's under
        // max. inc. compensation there's no extra cost.
        if (!$maximumInclusiveCompensation || $valuedItem <= $maximumInclusiveCompensation) {
            return 0;
        }

        // £1.80 including VAT for the first extra £100 cover. The additional
        // cost is in pence since it will be added before converting back to £.
        $cost = 180;
        $extra = ($valuedItem - $maximumInclusiveCompensation) - 100;

        if (0 >= $extra) {
            return $cost;
        }

        // £4.50 including VAT for every subsequent £100. The additional cost
        // is in pence since it will be added before converting back to £.
        $cost += ceil($extra / 100) * 450;

        return $cost;
    }

}

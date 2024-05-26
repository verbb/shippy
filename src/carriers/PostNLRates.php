<?php
namespace verbb\shippy\carriers;

use verbb\shippy\helpers\StringHelper;
use verbb\shippy\models\Rate;
use verbb\shippy\models\Shipment;
use verbb\shippy\models\StaticRates;

class PostNLRates extends StaticRates
{
    // Constants
    // =========================================================================

    public const ZONE_NL = 1;
    public const ZONE_EU1 = 2;
    public const ZONE_EU2 = 3;
    public const ZONE_EU3 = 4;
    public const ZONE_WORLD = 5;


    // Properties
    // =========================================================================

    // List of countries in EUR1 region.
    private static array $eur1 = ['AT', 'BE', 'DE', 'DK', 'ES', 'FR', 'GB', 'GBR', 'IT', 'LU', 'MC', 'SE'];

    // List of countries in EUR2 region.
    private static array $eur2 = ['AX', 'BG', 'CZ', 'EE', 'FI', 'HR', 'HU', 'IE', 'IM', 'LT', 'LV', 'PL', 'PT', 'RO', 'SI', 'SK', 'XA', 'XM'];

    // List of countries in EUR3 region.
    private static array $eur3 = ['AD', 'AL', 'BA', 'BY', 'CNI', 'CY', 'FO', 'GG', 'GI', 'GL', 'GR', 'IS', 'JE', 'LI', 'MD', 'ME', 'MK', 'MT', 'NO', 'RS', 'SM', 'TR', 'UA', 'VA', 'CH'];

    // Rest of the world.
    private static array $world = [
        'AE', 'AM', 'ANT', 'AQ', 'AR', 'AS', 'AW', 'AZ', 'BB', 'BD', 'BF', 'BI', 'BM', 'BN', 'CG', 'CI', 'CM', 'CN', 'DJ', 'DZ', 'EG', 'FK', 'GE', 'GF', 'GH', 'GM', 'GN', 'GP', 'HK', 'IN', 'IQ', 'JM', 'JP', 'KE', 'KG', 'KP', 'KR', 'KW', 'KZ', 'LK', 'MA', 'MG', 'ML', 'MM', 'MN', 'MO', 'MQ', 'MR', 'MS', 'MU', 'MY', 'NC', 'NE', 'NG', 'NZ', 'PF', 'RE', 'RU', 'RUA', 'SC', 'SG', 'SN', 'SY', 'TD', 'TF', 'TG', 'TH', 'TJ', 'TM', 'TN', 'TW', 'TZ', 'UG', 'UZ', 'VC', 'WS', 'XS', 'XZ', 'YE', 'ZW', 'AF', 'AG', 'AI', 'AO', 'AU', 'BH', 'BJ', 'BO', 'BQ', 'BR', 'BS', 'BT', 'BV', 'BW', 'BZ', 'CA', 'CC', 'CD', 'CF', 'CK', 'CL', 'CO', 'CR', 'CU', 'CV', 'CW', 'CX', 'DM', 'DO', 'EC', 'EH', 'ER', 'ET', 'FJ', 'FM', 'GA', 'GD', 'GQ', 'GS', 'GT', 'GU', 'GW', 'GY', 'HM', 'HN', 'HT', 'ID', 'IL', 'IR', 'JO', 'KH', 'KI', 'KM', 'KN', 'KY', 'LA', 'LB', 'LC', 'LR', 'LS', 'LY', 'MH', 'MP', 'MV', 'MW', 'MX', 'MZ', 'NA', 'NF', 'NI', 'NP', 'NR', 'NU', 'OM', 'PA', 'PC', 'PE', 'PG', 'PH', 'PK', 'PM', 'PN', 'PR', 'PS', 'PW', 'PY', 'QA', 'RW', 'SA', 'SB', 'SD', 'SH', 'SHST', 'SJ', 'SL', 'SO', 'SR', 'SS', 'ST', 'SV', 'SX', 'SZ', 'TC', 'TK', 'TL', 'TO', 'TT', 'TV', 'UM', 'US', 'UY', 'VE', 'VI', 'VN', 'VU', 'WF', 'X1', 'XL', 'YT', 'ZA', 'ZM',
    ];


    // Public Methods
    // =========================================================================

    public static function getRate(string $serviceCode, PostNL $carrier, Shipment $shipment): ?Rate
    {
        $boxRates = [];

        // Get the zone for the country, which defines which pricing we should use.
        $zone = self::getZone($shipment->getTo()->getCountryCode());

        // Get a prefix for the country. Rates can vary
        $prefix = self::getPrefix($shipment->getTo()->getCountryCode());

        // Get the function we should be using rates for
        $methodName = 'get' . StringHelper::toPascalCase($prefix) . StringHelper::toPascalCase($serviceCode) . 'Rates';

        // Find the rates for the destination country
        if (method_exists(self::class, $methodName) && $rateBoxes = self::$methodName()) {
            foreach ($rateBoxes as $key => &$rateBox) {
                $price = $rateBox['price'][$zone] ?? null;

                if ($price) {
                    // All pricing in cents
                    $rateBox['price'] = $price / 100;

                    $boxRates[$key] = $rateBox;
                }
            }
        }

        // Given the box rates, ensure that we only return rates that the supplied shipment can fit in
        return self::getRateFromBoxRates($shipment, $carrier, $boxRates, $serviceCode);
    }

    public static function getDomesticBriefRates(): array
    {
        return [
            'brief-20g' => [
                'length' => 380,
                'width' => 265,
                'height' => 32,
                'weight' => 20,
                'price' => [
                    self::ZONE_NL => 83,
                ],
            ],
            'brief-50g' => [
                'length' => 380,
                'width' => 265,
                'height' => 32,
                'weight' => 50,
                'price' => [
                    self::ZONE_NL => 166,
                ],
            ],
            'brief-100g' => [
                'length' => 380,
                'width' => 265,
                'height' => 32,
                'weight' => 100,
                'price' => [
                    self::ZONE_NL => 249,
                ],
            ],
            'brief-250g' => [
                'length' => 380,
                'width' => 265,
                'height' => 32,
                'weight' => 250,
                'price' => [
                    self::ZONE_NL => 332,
                ],
            ],
            'brief-2kg' => [
                'length' => 380,
                'width' => 265,
                'height' => 32,
                'weight' => 2000,
                'price' => [
                    self::ZONE_NL => 415,
                ],
            ],
        ];
    }

    public static function getDomesticBrievenbuspakjeRates(): array
    {
        return [
            'brievenbuspakje-2kg' => [
                'length' => 380,
                'width' => 265,
                'height' => 32,
                'weight' => 2000,
                'price' => [
                    self::ZONE_NL => 425,
                ],
            ],
        ];
    }

    public static function getDomesticPakketNoTrackAndTraceRates(): array
    {
        return [

        ];
    }

    public static function getDomesticPakketRates(): array
    {
        return [
            'pakket-2kg' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 2000,
                'price' => [
                    self::ZONE_NL => 695,
                ],
            ],
            'pakket-5kg' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 5000,
                'price' => [
                    self::ZONE_NL => 695,
                ],
            ],
            'pakket-10kg' => [
                'length' => 1760,
                'width' => 780,
                'height' => 580,
                'weight' => 10000,
                'price' => [
                    self::ZONE_NL => 695,
                ],
            ],
            'pakket-20kg' => [
                'length' => 1760,
                'width' => 780,
                'height' => 580,
                'weight' => 20000,
                'price' => [
                    self::ZONE_NL => 1325,
                ],
            ],
            'pakket-30kg' => [
                'length' => 1760,
                'width' => 780,
                'height' => 580,
                'weight' => 30000,
                'price' => [
                    self::ZONE_NL => 1325,
                ],
            ],
        ];
    }

    public static function getDomesticAangetekendRates(): array
    {
        return [
            'pakket-2kg' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 2000,
                'price' => [
                    self::ZONE_NL => 860,
                ],
            ],
            'pakket-5kg' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 5000,
                'price' => [
                    self::ZONE_NL => 860,
                ],
            ],
            'pakket-10kg' => [
                'length' => 1760,
                'width' => 780,
                'height' => 580,
                'weight' => 10000,
                'price' => [
                    self::ZONE_NL => 860,
                ],
            ],
            'pakket-20kg' => [
                'length' => 1760,
                'width' => 780,
                'height' => 580,
                'weight' => 20000,
                'price' => [
                    self::ZONE_NL => 1490,
                ],
            ],
            'pakket-30kg' => [
                'length' => 1760,
                'width' => 780,
                'height' => 580,
                'weight' => 30000,
                'price' => [
                    self::ZONE_NL => 1490,
                ],
            ],
        ];
    }

    public static function getDomesticVerzekerserviceRates(): array
    {
        return [
            'pakket-2kg' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 2000,
                'price' => [
                    self::ZONE_NL => 1445,
                ],
            ],
            'pakket-5kg' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 5000,
                'price' => [
                    self::ZONE_NL => 1445,
                ],
            ],
            'pakket-10kg' => [
                'length' => 1760,
                'width' => 780,
                'height' => 580,
                'weight' => 10000,
                'price' => [
                    self::ZONE_NL => 1445,
                ],
            ],
            'pakket-20kg' => [
                'length' => 1760,
                'width' => 780,
                'height' => 580,
                'weight' => 20000,
                'price' => [
                    self::ZONE_NL => 2075,
                ],
            ],
            'pakket-30kg' => [
                'length' => 1760,
                'width' => 780,
                'height' => 580,
                'weight' => 30000,
                'price' => [
                    self::ZONE_NL => 2075,
                ],
            ],
        ];
    }

    public static function getDomesticBetaalserviceRates(): array
    {
        return [
            'pakket-2kg' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 2000,
                'price' => [
                    self::ZONE_NL => 1835,
                ],
            ],
            'pakket-5kg' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 5000,
                'price' => [
                    self::ZONE_NL => 1835,
                ],
            ],
            'pakket-10kg' => [
                'length' => 1760,
                'width' => 780,
                'height' => 580,
                'weight' => 10000,
                'price' => [
                    self::ZONE_NL => 1835,
                ],
            ],
            'pakket-20kg' => [
                'length' => 1760,
                'width' => 780,
                'height' => 580,
                'weight' => 20000,
                'price' => [
                    self::ZONE_NL => 2320,
                ],
            ],
            'pakket-30kg' => [
                'length' => 1760,
                'width' => 780,
                'height' => 580,
                'weight' => 30000,
                'price' => [
                    self::ZONE_NL => 2320,
                ],
            ],
        ];
    }

    public static function getInternationalBriefRates(): array
    {
        return [
            'brief-20g' => [
                'length' => 380,
                'width' => 265,
                'height' => 32,
                'weight' => 20,
                'price' => [
                    self::ZONE_NL => false,
                    self::ZONE_EU1 => 140,
                    self::ZONE_EU2 => 140,
                    self::ZONE_EU3 => false,
                    self::ZONE_WORLD => 140,
                ],
            ],
            'brief-50g' => [
                'length' => 380,
                'width' => 265,
                'height' => 32,
                'weight' => 50,
                'price' => [
                    self::ZONE_NL => false,
                    self::ZONE_EU1 => 280,
                    self::ZONE_EU2 => 280,
                    self::ZONE_EU3 => false,
                    self::ZONE_WORLD => 280,
                ],
            ],
            'brief-100g' => [
                'length' => 380,
                'width' => 265,
                'height' => 32,
                'weight' => 100,
                'price' => [
                    self::ZONE_NL => false,
                    self::ZONE_EU1 => 420,
                    self::ZONE_EU2 => 420,
                    self::ZONE_EU3 => false,
                    self::ZONE_WORLD => 420,
                ],
            ],
            'brief-250g' => [
                'length' => 380,
                'width' => 265,
                'height' => 32,
                'weight' => 250,
                'price' => [
                    self::ZONE_NL => false,
                    self::ZONE_EU1 => 840,
                    self::ZONE_EU2 => 840,
                    self::ZONE_EU3 => false,
                    self::ZONE_WORLD => 840,
                ],
            ],
            'brief-2000g' => [
                'length' => 380,
                'width' => 265,
                'height' => 32,
                'weight' => 2000,
                'price' => [
                    self::ZONE_NL => false,
                    self::ZONE_EU1 => 980,
                    self::ZONE_EU2 => 1260,
                    self::ZONE_EU3 => false,
                    self::ZONE_WORLD => 1540,
                ],
            ],
        ];
    }

    public static function getInternationalBrievenbuspakjeRates(): array
    {
        return [

        ];
    }

    public static function getInternationalPakketNoTrackAndTraceRates(): array
    {
        return [
            'pakket-2kg' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 2000,
                'price' => [
                    self::ZONE_NL => false,
                    self::ZONE_EU1 => 980,
                    self::ZONE_EU2 => 1260,
                    self::ZONE_EU3 => false,
                    self::ZONE_WORLD => 1820,
                ],
            ],
        ];
    }

    public static function getInternationalPakketRates(): array
    {
        return [
            'pakket-2kg' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 2000,
                'price' => [
                    self::ZONE_NL => false,
                    self::ZONE_EU1 => 1300,
                    self::ZONE_EU2 => 1850,
                    self::ZONE_EU3 => false,
                    self::ZONE_WORLD => 2430,
                ],
            ],
            'pakket-5kg' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 5000,
                'price' => [
                    self::ZONE_NL => false,
                    self::ZONE_EU1 => 1950,
                    self::ZONE_EU2 => 2500,
                    self::ZONE_EU3 => false,
                    self::ZONE_WORLD => 3430,
                ],
            ],
            'pakket-10kg' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 10000,
                'price' => [
                    self::ZONE_NL => false,
                    self::ZONE_EU1 => 2500,
                    self::ZONE_EU2 => 3100,
                    self::ZONE_EU3 => false,
                    self::ZONE_WORLD => 5830,
                ],
            ],
            'pakket-20kg' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 20000,
                'price' => [
                    self::ZONE_NL => false,
                    self::ZONE_EU1 => 3400,
                    self::ZONE_EU2 => 4000,
                    self::ZONE_EU3 => false,
                    self::ZONE_WORLD => 10530,
                ],
            ],
            'pakket-30kg' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 30000,
                'price' => [
                    self::ZONE_NL => false,
                    self::ZONE_EU1 => 4500,
                    self::ZONE_EU2 => 5500,
                    self::ZONE_EU3 => false,
                    self::ZONE_WORLD => false,
                ],
            ],
        ];
    }

    public static function getInternationalAangetekendRates(): array
    {
        return [
            'pakket-2kg' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 2000,
                'price' => [
                    self::ZONE_NL => false,
                    self::ZONE_EU1 => 1550,
                    self::ZONE_EU2 => 2100,
                    self::ZONE_EU3 => false,
                    self::ZONE_WORLD => 2680,
                ],
            ],
            'pakket-5kg' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 5000,
                'price' => [
                    self::ZONE_NL => false,
                    self::ZONE_EU1 => 2200,
                    self::ZONE_EU2 => 2750,
                    self::ZONE_EU3 => false,
                    self::ZONE_WORLD => 3680,
                ],
            ],
            'pakket-10kg' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 10000,
                'price' => [
                    self::ZONE_NL => false,
                    self::ZONE_EU1 => 2750,
                    self::ZONE_EU2 => 3350,
                    self::ZONE_EU3 => false,
                    self::ZONE_WORLD => 6080,
                ],
            ],
            'pakket-20kg' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 20000,
                'price' => [
                    self::ZONE_NL => false,
                    self::ZONE_EU1 => 3650,
                    self::ZONE_EU2 => 4250,
                    self::ZONE_EU3 => false,
                    self::ZONE_WORLD => 10780,
                ],
            ],
            'pakket-30kg' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 30000,
                'price' => [
                    self::ZONE_NL => false,
                    self::ZONE_EU1 => 4750,
                    self::ZONE_EU2 => 5750,
                    self::ZONE_EU3 => false,
                    self::ZONE_WORLD => false,
                ],
            ],
        ];
    }

    public static function getInternationalVerzekerserviceRates(): array
    {
        return [
            'pakket-2kg' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 2000,
                'price' => [
                    self::ZONE_NL => false,
                    self::ZONE_EU1 => 2300,
                    self::ZONE_EU2 => 2850,
                    self::ZONE_EU3 => false,
                    self::ZONE_WORLD => 3430,
                ],
            ],
            'pakket-5kg' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 5000,
                'price' => [
                    self::ZONE_NL => false,
                    self::ZONE_EU1 => 2950,
                    self::ZONE_EU2 => 3500,
                    self::ZONE_EU3 => false,
                    self::ZONE_WORLD => 4430,
                ],
            ],
            'pakket-10kg' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 10000,
                'price' => [
                    self::ZONE_NL => false,
                    self::ZONE_EU1 => 3500,
                    self::ZONE_EU2 => 4100,
                    self::ZONE_EU3 => false,
                    self::ZONE_WORLD => 6830,
                ],
            ],
            'pakket-20kg' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 20000,
                'price' => [
                    self::ZONE_NL => false,
                    self::ZONE_EU1 => 4400,
                    self::ZONE_EU2 => 5000,
                    self::ZONE_EU3 => false,
                    self::ZONE_WORLD => 11530,
                ],
            ],
            'pakket-30kg' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 30000,
                'price' => [
                    self::ZONE_NL => false,
                    self::ZONE_EU1 => 5500,
                    self::ZONE_EU2 => 6500,
                    self::ZONE_EU3 => false,
                    self::ZONE_WORLD => false,
                ],
            ],
        ];
    }

    public static function getInternationalBetaalserviceRates(): array
    {
        return [

        ];
    }


    // Private Methods
    // =========================================================================

    private static function getZone($country): int
    {
        if ($country === 'NL') {
            return self::ZONE_NL;
        } else if (in_array($country, self::$eur1)) {
            return self::ZONE_EU1;
        } else if (in_array($country, self::$eur2)) {
            return self::ZONE_EU2;
        } else if (in_array($country, self::$eur3)) {
            return self::ZONE_EU3;
        } else if (in_array($country, self::$world)) {
            return self::ZONE_WORLD;
        }

        return 0;
    }

    private static function getPrefix($country): string
    {
        if ($country === 'NL') {
            return 'domestic';
        }

        return 'international';
    }

}
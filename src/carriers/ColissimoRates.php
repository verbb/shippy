<?php
namespace verbb\shippy\carriers;

use verbb\shippy\helpers\StringHelper;
use verbb\shippy\models\Rate;
use verbb\shippy\models\Shipment;
use verbb\shippy\models\StaticRates;

class ColissimoRates extends StaticRates
{
    // Constants
    // =========================================================================

    public const ZONE_FR = 1;
    public const ZONE_DOM = 2;
    public const ZONE_TOM = 3;
    public const ZONE_INTERNATIONAL_A = 4;
    public const ZONE_INTERNATIONAL_B = 5;
    public const ZONE_INTERNATIONAL_C = 6;


    // Properties
    // =========================================================================

    // France, Andorra, Monaco
    private static array $france = ['FR', 'AD', 'MC'];

    // DOM
    private static array $DOM = ['GP', 'MQ', 'GY', 'RE', 'YT'];

    // TOM
    private static array $TOM = ['PM', 'BL', 'MF', 'WF', 'PF', 'TF', 'NC'];

    // International Zone A: Europe, Switzerland
    private static array $internationalZoneA = ['AT', 'BE', 'BG', 'CY', 'HR', 'DK', 'ES', 'EE', 'FI', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT', 'CZ', 'RO', 'GB', 'SK', 'SI', 'SE', 'CH', 'VA'];

    // International Zone B: Eastern Europe (except Russia and European Union), Norway, Maghreb
    private static array $internationalZoneB = ['AL', 'AM', 'AZ', 'BY', 'BA', 'GE', 'IS', 'LI', 'MK', 'MD', 'ME', 'RS', 'TR', 'UA', 'NO', 'DZ', 'LY', 'MO', 'MR', 'TN'];


    // Public Methods
    // =========================================================================

    public static function getRate(string $serviceCode, Colissimo $carrier, Shipment $shipment): ?Rate
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

    public static function getFrFranceRates(): array
    {
        return [
            'pack-250' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 250,
                'price' => [
                    self::ZONE_FR => 495,
                ],
            ],
            'pack-500' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 500,
                'price' => [
                    self::ZONE_FR => 615,
                ],
            ],
            'pack-750' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 750,
                'price' => [
                    self::ZONE_FR => 700,
                ],
            ],
            'pack-1000' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 1000,
                'price' => [
                    self::ZONE_FR => 765,
                ],
            ],
            'pack-2000' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 2000,
                'price' => [
                    self::ZONE_FR => 865,
                ],
            ],
            'pack-5000' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 5000,
                'price' => [
                    self::ZONE_FR => 1315,
                ],
            ],
            'pack-10000' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 10000,
                'price' => [
                    self::ZONE_FR => 1920,
                ],
            ],
            'pack-30000' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 30000,
                'price' => [
                    self::ZONE_FR => 2730,
                ],
            ],
        ];
    }

    public static function getFrEmballageFranceRates(): array
    {
        return [
            'bubble-bag-XS' => [
                'length' => 180,
                'width' => 230,
                'height' => 20,
                'weight' => 1000,
                'price' => [
                    self::ZONE_FR => 1000,
                ],
            ],
            'bubble-bag-S' => [
                'length' => 290,
                'width' => 330,
                'height' => 20,
                'weight' => 3000,
                'price' => [
                    self::ZONE_FR => 1000,
                ],
            ],
            'cardboard-sleeve-XS' => [
                'length' => 220,
                'width' => 140,
                'height' => 50,
                'weight' => 1000,
                'price' => [
                    self::ZONE_FR => 1000,
                ],
            ],
            'cardboard-sleeve-S' => [
                'length' => 335,
                'width' => 215,
                'height' => 60,
                'weight' => 3000,
                'price' => [
                    self::ZONE_FR => 1000,
                ],
            ],
            'box-S' => [
                'length' => 280,
                'width' => 210,
                'height' => 20,
                'weight' => 1000,
                'price' => [
                    self::ZONE_FR => 895,
                ],
            ],
            'box-M' => [
                'length' => 230,
                'width' => 130,
                'height' => 100,
                'weight' => 3000,
                'price' => [
                    self::ZONE_FR => 800,
                ],
            ],
            'box-L' => [
                'length' => 315,
                'width' => 210,
                'height' => 157,
                'weight' => 5000,
                'price' => [
                    self::ZONE_FR => 1200,
                ],
            ],
            'CD' => [
                'length' => 217,
                'width' => 140,
                'height' => 60,
                'weight' => 1000,
                'price' => [
                    self::ZONE_FR => 790,
                ],
            ],
            '1-Bottle' => [
                'length' => 390,
                'width' => 168,
                'height' => 104,
                'weight' => 2000,
                'price' => [
                    self::ZONE_FR => 1110,
                ],
            ],
            '2-Bottles' => [
                'length' => 390,
                'width' => 297,
                'height' => 106,
                'weight' => 5000,
                'price' => [
                    self::ZONE_FR => 1360,
                ],
            ],
            '3-Bottles' => [
                'length' => 390,
                'width' => 425,
                'height' => 106,
                'weight' => 7000,
                'price' => [
                    self::ZONE_FR => 1460,
                ],
            ],
        ];
    }

    public static function getDomEconomiqueOutremerRates(): array
    {
        return [
            'pack-500' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 500,
                'price' => [
                    self::ZONE_DOM => 880,
                ],
            ],
            'pack-1000' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 1000,
                'price' => [
                    self::ZONE_DOM => 1150,
                ],
            ],
            'pack-2000' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 2000,
                'price' => [
                    self::ZONE_DOM => 1400,
                ],
            ],
            'pack-5000' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 5000,
                'price' => [
                    self::ZONE_DOM => 2500,
                ],
            ],
            'pack-10000' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 10000,
                'price' => [
                    self::ZONE_DOM => 3500,
                ],
            ],
            'pack-20000' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 30000,
                'price' => [
                    self::ZONE_DOM => 6500,
                ],
            ],
            'pack-30000' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 30000,
                'price' => [
                    self::ZONE_DOM => 9000,
                ],
            ],
        ];
    }

    public static function getDomOutremerRates(): array
    {
        return [
            'pack-500' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 500,
                'price' => [
                    self::ZONE_DOM => 930,
                ],
            ],
            'pack-1000' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 1000,
                'price' => [
                    self::ZONE_DOM => 1410,
                ],
            ],
            'pack-2000' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 2000,
                'price' => [
                    self::ZONE_DOM => 1920,
                ],
            ],
            'pack-5000' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 5000,
                'price' => [
                    self::ZONE_DOM => 2890,
                ],
            ],
            'pack-10000' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 10000,
                'price' => [
                    self::ZONE_DOM => 4640,
                ],
            ],
            'pack-30000' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 30000,
                'price' => [
                    self::ZONE_DOM => 10360,
                ],
            ],
        ];
    }

    public static function getEmballageInternationalRates(): array
    {
        return [
            'pack-500' => [
                'length' => 500,
                'width' => 250,
                'height' => 250,
                'weight' => 500,
                'price' => [
                    self::ZONE_INTERNATIONAL_B => 1620,
                    self::ZONE_INTERNATIONAL_C => 2370,
                ],
            ],
            'pack-1000' => [
                'length' => 500,
                'width' => 250,
                'height' => 250,
                'weight' => 1000,
                'price' => [
                    self::ZONE_INTERNATIONAL_B => 1935,
                    self::ZONE_INTERNATIONAL_C => 2630,
                ],
            ],
            'pack-2000' => [
                'length' => 500,
                'width' => 250,
                'height' => 250,
                'weight' => 2000,
                'price' => [
                    self::ZONE_INTERNATIONAL_B => 2105,
                    self::ZONE_INTERNATIONAL_C => 3610,
                ],
            ],
            'pack-5000' => [
                'length' => 500,
                'width' => 250,
                'height' => 250,
                'weight' => 5000,
                'price' => [
                    self::ZONE_INTERNATIONAL_B => 2700,
                    self::ZONE_INTERNATIONAL_C => 5300,
                ],
            ],
            'pack-10000' => [
                'length' => 500,
                'width' => 250,
                'height' => 250,
                'weight' => 10000,
                'price' => [
                    self::ZONE_INTERNATIONAL_B => 4500,
                    self::ZONE_INTERNATIONAL_C => 10000,
                ],
            ],
            'pack-20000' => [
                'length' => 500,
                'width' => 250,
                'height' => 250,
                'weight' => 10000,
                'price' => [
                    self::ZONE_INTERNATIONAL_B => 7000,
                    self::ZONE_INTERNATIONAL_C => 16000,
                ],
            ],
        ];
    }

    public static function getEuropeRates(): array
    {
        return [
            'pack-500' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 500,
                'price' => [
                    self::ZONE_INTERNATIONAL_A => 1230,
                ],
            ],
            'pack-1000' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 1000,
                'price' => [
                    self::ZONE_INTERNATIONAL_A => 1505,
                ],
            ],
            'pack-2000' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 2000,
                'price' => [
                    self::ZONE_INTERNATIONAL_A => 1680,
                ],
            ],
            'pack-5000' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 5000,
                'price' => [
                    self::ZONE_INTERNATIONAL_A => 2150,
                ],
            ],
            'pack-10000' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 10000,
                'price' => [
                    self::ZONE_INTERNATIONAL_A => 3550,
                ],
            ],
            'pack-30000' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 30000,
                'price' => [
                    self::ZONE_INTERNATIONAL_A => 5900,
                ],
            ],
        ];
    }

    public static function getInternationalRates(): array
    {
        return [
            'pack-500' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 500,
                'price' => [
                    self::ZONE_INTERNATIONAL_B => 1640,
                    self::ZONE_INTERNATIONAL_C => 2400,
                ],
            ],
            'pack-1000' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 1000,
                'price' => [
                    self::ZONE_INTERNATIONAL_B => 1960,
                    self::ZONE_INTERNATIONAL_C => 2670,
                ],
            ],
            'pack-2000' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 2000,
                'price' => [
                    self::ZONE_INTERNATIONAL_B => 2140,
                    self::ZONE_INTERNATIONAL_C => 3670,
                ],
            ],
            'pack-5000' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 5000,
                'price' => [
                    self::ZONE_INTERNATIONAL_B => 2750,
                    self::ZONE_INTERNATIONAL_C => 5370,
                ],
            ],
            'pack-10000' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 10000,
                'price' => [
                    self::ZONE_INTERNATIONAL_B => 4550,
                    self::ZONE_INTERNATIONAL_C => 10150,
                ],
            ],
            'pack-20000' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 10000,
                'price' => [
                    self::ZONE_INTERNATIONAL_B => 7100,
                    self::ZONE_INTERNATIONAL_C => 16200,
                ],
            ],
        ];
    }

    public static function getTomEconomiqueOutremerRates(): array
    {
        return [
            'pack-500' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 500,
                'price' => [
                    self::ZONE_TOM => 1080,
                ],
            ],
            'pack-1000' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 1000,
                'price' => [
                    self::ZONE_TOM => 1630,
                ],
            ],
            'pack-2000' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 2000,
                'price' => [
                    self::ZONE_TOM => 2900,
                ],
            ],
            'pack-5000' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 5000,
                'price' => [
                    self::ZONE_TOM => 4800,
                ],
            ],
            'pack-10000' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 10000,
                'price' => [
                    self::ZONE_TOM => 9450,
                ],
            ],
            'pack-30000' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 30000,
                'price' => [
                    self::ZONE_TOM => 24800,
                ],
            ],
        ];
    }

    public static function getTomOutremerRates(): array
    {
        return [
            'pack-500' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 500,
                'price' => [
                    self::ZONE_TOM => 1120,
                ],
            ],
            'pack-1000' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 1000,
                'price' => [
                    self::ZONE_TOM => 1680,
                ],
            ],
            'pack-2000' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 2000,
                'price' => [
                    self::ZONE_TOM => 2960,
                ],
            ],
            'pack-5000' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 5000,
                'price' => [
                    self::ZONE_TOM => 4960,
                ],
            ],
            'pack-10000' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 10000,
                'price' => [
                    self::ZONE_TOM => 9660,
                ],
            ],
            'pack-30000' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 30000,
                'price' => [
                    self::ZONE_TOM => 25000,
                ],
            ],
        ];
    }


    // Private Methods
    // =========================================================================

    private static function getZone($country): int
    {
        if (in_array($country, self::$france)) {
            return self::ZONE_FR;
        } else if (in_array($country, self::$DOM)) {
            return self::ZONE_DOM;
        } else if (in_array($country, self::$TOM)) {
            return self::ZONE_TOM;
        } else if (in_array($country, self::$internationalZoneA)) {
            return self::ZONE_INTERNATIONAL_A;
        } else if (in_array($country, self::$internationalZoneB)) {
            return self::ZONE_INTERNATIONAL_B;
        }

        return self::ZONE_INTERNATIONAL_C;
    }

    private static function getPrefix($country): string
    {
        if (in_array($country, self::$france)) {
            return 'fr';
        } else if (in_array($country, self::$DOM)) {
            return 'dom';
        } else if (in_array($country, self::$TOM)) {
            return 'tom';
        }

        return '';
    }

}

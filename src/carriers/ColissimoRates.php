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

        // Resolve the class dynamically
        $className = 'verbb\\shippy\\rates\\colissimo\\' . StringHelper::toPascalCase($prefix) . StringHelper::toPascalCase($serviceCode);

        if (class_exists($className)) {
            $rateClass = new $className();

            // Call the generic `getRates` method on the resolved class
            foreach ($rateClass::getRates() as $key => &$rateBox) {
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


    // Protected Methods
    // =========================================================================

    protected static function getZone($country): int
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

    protected static function getPrefix($country): string
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

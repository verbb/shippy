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

        // Resolve the class dynamically
        $className = 'verbb\\shippy\\rates\\postnl\\' . StringHelper::toPascalCase($prefix) . StringHelper::toPascalCase($serviceCode);

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

    protected static function getPrefix($country): string
    {
        if ($country === 'NL') {
            return 'domestic';
        }

        return 'international';
    }

}
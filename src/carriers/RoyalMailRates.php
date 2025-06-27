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

    public const LETTER = 'letter';
    public const LARGE_LETTER = 'large-letter';
    public const SMALL_PARCEL_WIDE = 'small-parcel-wide';
    public const SMALL_PARCEL_DEEP = 'small-parcel-deep';
    public const SMALL_PARCEL_BIGGER = 'small-parcel-bigger';
    public const TUBE = 'tube';
    public const MEDIUM_PARCEL = 'medium-parcel';
    public const LONG_PARCEL = 'long-parcel';
    public const SQUARE_PARCEL = 'square-parcel';
    public const PACKET = 'packet';
    public const PARCEL = 'parcel';
    public const PRINTED_PAPERS = 'printed-papers';


    // Properties
    // =========================================================================

    public static CarrierInterface $carrier;
    public static Shipment $shipment;
    public static bool $checkCompensation = false;
    public static bool $includeVat = false;
    public static string $ratesType = 'online';

    protected static array $euro = ['AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'GR', 'HR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK'];

    protected static array $europeZone1 = ['IE', 'DE', 'DK', 'FR', 'MC'];
    protected static array $europeZone2 = ['AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'EE', 'FI', 'GR', 'HU', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE'];
    protected static array $europeZone3 = ['AL', 'AD', 'AM', 'AZ', 'BY', 'BA', 'FO', 'GE', 'GI', 'GL', 'IS', 'KZ', 'KG', 'LI', 'MD', 'ME', 'MK', 'NO', 'RU', 'SM', 'RS', 'CH', 'TJ', 'TR', 'TM', 'UA', 'UZ', 'VA'];

    protected static array $worldZone2 = ['AU', 'PW', 'IO', 'CX', 'CC', 'CK', 'FJ', 'PF', 'TF', 'KI', 'MO', 'NR', 'NC', 'NZ', 'PG', 'NU', 'NF', 'LA', 'PN', 'TO', 'TV', 'WS', 'AS', 'SG', 'SB', 'TK'];

    protected static array $worldZone3 = ['US'];

    protected static array $farEast = ['CN', 'HK', 'MO', 'JP', 'MN', 'KP', 'KR', 'TW', 'BN', 'KH', 'TL', 'ID', 'LA', 'MY', 'MM', 'PH', 'SG', 'TH', 'VN', 'RU'];

    protected static array $australasia = ['AU', 'PF', 'NU', 'TO', 'CX', 'KI', 'PG', 'TV', 'CC', 'NR', 'PN', 'VU', 'CK', 'NC', 'SB', 'WF', 'FJ', 'NZ', 'TK', 'WS'];

    protected static array $europeNonEu = ['AL', 'AD', 'AM', 'AZ', 'BY', 'BA', 'GE', 'IS', 'LI', 'MD', 'MC', 'ME', 'MK', 'NO', 'RU', 'SM', 'RS', 'CH', 'TR', 'UA', 'GB', 'VA',];

    protected static array $rateYears = [
        '2019' => '2019-03-25',
        '2020' => '2020-03-23',
        '2021' => '2021-01-01',
        '2022' => '2022-04-04',
        '2023' => '2023-04-03',
        '2024' => '2024-07-01',
        '2024' => '2024-10-07',
        '2025' => '2025-04-07',
    ];

    protected static array $internationalDefaultBox = [
        self::LETTER => [
            'length' => 240,
            'width' => 165,
            'height' => 5,
            'weight' => 100,
        ],
        self::LARGE_LETTER => [
            'length' => 353,
            'width' => 250,
            'height' => 25,
            'weight' => 750,
        ],
        self::LONG_PARCEL => [
            'length' => 600,
            'width' => 150,
            'height' => 150,
            'weight' => 2000,
        ],
        self::SQUARE_PARCEL => [
            'length' => 300,
            'width' => 300,
            'height' => 300,
            'weight' => 2000,
        ],
        self::PARCEL => [
            'length' => 450,
            'width' => 225,
            'height' => 225,
            'weight' => 2000,
        ],
        self::MEDIUM_PARCEL => [
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
        self::$ratesType = $carrier->getRatesType();

        // Resolve the class dynamically
        $className = 'verbb\\shippy\\rates\\royalmail\\' . self::$ratesType . '\\' . StringHelper::toPascalCase($serviceCode);

        if (class_exists($className)) {
            $rateClass = new $className();

            // Call the generic `getRates` method on the resolved class
            if ($rateBoxes = $rateClass::getRates($shipment->getTo()->getCountryCode())) {
                foreach ($rateBoxes as $key => &$rateBox) {
                    $price = $rateBox['price'] ?? null;

                    if ($price) {
                        // All pricing in pence
                        $rateBox['price'] /= 100;
                        $boxRates[$key] = $rateBox;
                    }
                }
            }
        }

        // Given the box rates, ensure that we only return rates that the supplied shipment can fit in
        return self::getRateFromBoxRates($shipment, $carrier, $boxRates, $serviceCode);
    }


    // Protected Methods
    // =========================================================================

    protected static function getAllEuropeanCountries(): array
    {
        return array_merge(self::$europeZone1, self::$europeZone2, self::$europeZone3);
    }

    protected static function getZone(string $countryCode): string
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

    protected static function getParcelforceZone(string $countryCode): string
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

    protected static function getRateYear(): int|string|null
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

    protected static function getValueForYear($array)
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

    protected static function getBoxPricing(array $boxes, array $bands, int $maxCompensation = 0): array
    {
        // Get the pricing as applicable
        $pricingBand = self::getValueForYear($bands);

        $boxesWithPricing = [];

        // Get pricing for this year and for each box
        foreach ($boxes as $key => $box) {
            $prices = $pricingBand[$key] ?? $pricingBand[self::PACKET] ?? [];

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

    protected static function getInternationalBoxPricing(array $bands, string $countryCode, int $maxCompensation = 0): array
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

    protected static function getInternationalTrackedBoxPricing(array $bands, string $countryCode, int $maxCompensation = 0): array
    {
        $boxes = self::$internationalDefaultBox;

        $boxPricing = self::getBoxPricing($boxes, $bands, $maxCompensation);
        $zone = self::getZone($countryCode);

        foreach ($boxPricing as $key => &$box) {
            if ($zone === 'IE') {
                $box['price'] = $box['price'][0];
            } else if ($zone === 'FR') {
                $box['price'] = $box['price'][1];
            } else if ($zone === 'DE') {
                $box['price'] = $box['price'][2];
            } else if ($zone === 'ES') {
                $box['price'] = $box['price'][3];
            } else if ($zone === 'IT') {
                $box['price'] = $box['price'][4];
            } else if ($zone === 'CA') {
                $box['price'] = $box['price'][5];
            } else if ($zone === 'CN') {
                $box['price'] = $box['price'][6];
            } else if ($zone === 'JP') {
                $box['price'] = $box['price'][7];
            } else if ($zone === 'AU') {
                $box['price'] = $box['price'][8];
            } else if ($zone === 'NZ') {
                $box['price'] = $box['price'][9];
            } else if ($zone === 'HK') {
                $box['price'] = $box['price'][10];
            } else if ($zone === 'EUR_1') {
                $box['price'] = $box['price'][11];
            } else if ($zone === 'EUR_2') {
                $box['price'] = $box['price'][12];
            } else if ($zone === 'EUR_3' || $zone === 'EU') {
                $box['price'] = $box['price'][13];
            } else if ($zone === '1') {
                $box['price'] = $box['price'][14];
            } else if ($zone === '2') {
                $box['price'] = $box['price'][15];
            } else if ($zone === '3') {
                // Fallback to zone 1 for older prices.
                $box['price'] = $box['price'][16] ?? $box['price'][14];
            } else {
                // No price for this country
                unset($boxPricing[$key]);
            }
        }

        return $boxPricing;
    }

    protected static function getParcelforceBoxPricing(array $bands, string $countryCode, array $options = []): array
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

    protected static function getVolumetricWeight($l, $w, $h): float|int
    {
        return ($l * $w * $h) / 5000;
    }

    protected static function getAdditionalCompensationCost($valuedItem, $maximumInclusiveCompensation): float|int
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

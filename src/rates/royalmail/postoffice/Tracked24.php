<?php
namespace verbb\shippy\rates\royalmail\postoffice;

use verbb\shippy\carriers\RoyalMailRates;

class Tracked24 extends RoyalMailRates
{
    // Static Methods
    // =========================================================================

    public static function getRates(string $countryCode): array
    {
        $zone = self::getZone($countryCode);

        if ($zone !== 'UK') {
            return [];
        }

        $bands = [
            '2024' => [
                self::LARGE_LETTER => [
                    750 => 360,
                ],
                self::SMALL_PARCEL_WIDE => [
                    2000 => 499,
                ],
                self::SMALL_PARCEL_DEEP => [
                    2000 => 499,
                ],
                self::SMALL_PARCEL_BIGGER => [
                    2000 => 499,
                ],
                self::MEDIUM_PARCEL => [
                    2000 => 729,
                    10000 => 899,
                    20000 => 1349,
                ],
                self::TUBE => [
                    2000 => 729,
                    10000 => 899,
                    20000 => 1349,
                ],
            ],
            '2025' => [
                self::LARGE_LETTER => [
                    750 => 370,
                ],
                self::SMALL_PARCEL_WIDE => [
                    2000 => 515,
                ],
                self::SMALL_PARCEL_DEEP => [
                    2000 => 515,
                ],
                self::SMALL_PARCEL_BIGGER => [
                    2000 => 515,
                ],
                self::MEDIUM_PARCEL => [
                    2000 => 745,
                    10000 => 929,
                    20000 => 1399,
                ],
                self::TUBE => [
                    2000 => 745,
                    10000 => 929,
                    20000 => 1399,
                ],
            ],
        ];

        $boxes = [
            self::LARGE_LETTER => [
                'length' => 353,
                'width' => 250,
                'height' => 25,
                'weight' => 750,
            ],
            self::SMALL_PARCEL_WIDE => [
                'length' => 450,
                'width' => 350,
                'height' => 160,
                'weight' => 2000,
            ],
            self::SMALL_PARCEL_DEEP => [
                'length' => 350,
                'width' => 250,
                'height' => 160,
                'weight' => 2000,
            ],
            self::SMALL_PARCEL_BIGGER => [
                'length' => 450,
                'width' => 350,
                'height' => 160,
                'weight' => 2000,
            ],
            self::MEDIUM_PARCEL => [
                'length' => 610,
                'width' => 460,
                'height' => 460,
                'weight' => 20000,
            ],
            self::TUBE => [
                'length' => 900,
                'width' => 70,
                'height' => 70,
                'weight' => 2000,
            ],
        ];

        return self::getBoxPricing($boxes, $bands, 100);
    }
}

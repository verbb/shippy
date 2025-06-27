<?php
namespace verbb\shippy\rates\royalmail\postoffice;

use verbb\shippy\carriers\RoyalMailRates;

class FirstClass extends RoyalMailRates
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
                self::LETTER => [
                    100 => 165,
                ],
                self::LARGE_LETTER => [
                    100 => 260,
                    750 => 350,
                ],
                self::SMALL_PARCEL_WIDE => [
                    2000 => 479,
                ],
                self::SMALL_PARCEL_DEEP => [
                    2000 => 479,
                ],
                self::SMALL_PARCEL_BIGGER => [
                    2000 => 479,
                ],
                self::MEDIUM_PARCEL => [
                    2000 => 699,
                    10000 => 869,
                    20000 => 1319,
                ],
            ],
            '2025' => [
                self::LETTER => [
                    100 => 170,
                ],
                self::LARGE_LETTER => [
                    100 => 315,
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
                    2000 => 719,
                    10000 => 899,
                    20000 => 1369,
                ],
            ],
        ];

        $boxes = [
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

        return self::getBoxPricing($boxes, $bands, 20);
    }
}

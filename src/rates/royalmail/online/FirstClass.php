<?php
namespace verbb\shippy\rates\royalmail\online;

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
                    100 => 250,
                    750 => 330,
                ],
                self::SMALL_PARCEL_WIDE => [
                    2000 => 409,
                ],
                self::SMALL_PARCEL_DEEP => [
                    2000 => 409,
                ],
                self::SMALL_PARCEL_BIGGER => [
                    2000 => 409,
                ],
                self::MEDIUM_PARCEL => [
                    2000 => 569,
                    10000 => 739,
                    20000 => 1189,
                ],
            ],
            '2025' => [
                self::LETTER => [
                    100 => 170,
                ],
                self::LARGE_LETTER => [
                    100 => 305,
                    750 => 330,
                ],
                self::SMALL_PARCEL_WIDE => [
                    2000 => 419,
                ],
                self::SMALL_PARCEL_DEEP => [
                    2000 => 419,
                ],
                self::SMALL_PARCEL_BIGGER => [
                    2000 => 419,
                ],
                self::MEDIUM_PARCEL => [
                    2000 => 585,
                    10000 => 765,
                    20000 => 1235,
                ],
            ],
            '2025-10' => [
                self::LETTER => [
                    100 => 170,
                ],
                self::LARGE_LETTER => [
                    100 => 305,
                    250 => 330,
                    500 => 330,
                    750 => 330,
                ],
                self::SMALL_PARCEL_WIDE => [
                    2000 => 440,
                ],
                self::SMALL_PARCEL_DEEP => [
                    2000 => 440,
                ],
                self::SMALL_PARCEL_BIGGER => [
                    2000 => 440,
                ],
                self::MEDIUM_PARCEL => [
                    2000 => 615,
                    10000 => 805,
                    20000 => 1310,
                ],
            ],
            '2026' => [
                self::LETTER => [
                    100 => 180,
                ],
                self::LARGE_LETTER => [
                    100 => 320,
                    250 => 330,
                    500 => 330,
                    750 => 330,
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
                    2000 => 735,
                    10000 => 935,
                    20000 => 1475,
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

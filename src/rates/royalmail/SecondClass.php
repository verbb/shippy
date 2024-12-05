<?php
namespace verbb\shippy\rates\royalmail;

use verbb\shippy\carriers\RoyalMailRates;

class SecondClass extends RoyalMailRates
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
                    100 => 85,
                ],
                self::LARGE_LETTER => [
                    100 => 155,
                    250 => 210,
                    500 => 250,
                    750 => 270,
                ],
                self::SMALL_PARCEL_WIDE => [
                    2000 => 369,
                ],
                self::SMALL_PARCEL_DEEP => [
                    2000 => 369,
                ],
                self::SMALL_PARCEL_BIGGER => [
                    2000 => 369,
                ],
                self::MEDIUM_PARCEL => [
                    1000 => 589,
                    2000 => 589,
                    5000 => 739,
                    10000 => 739,
                    20000 => 1099,
                ],
            ],
            '2024-10' => [
                self::LETTER => [
                    100 => 85,
                ],
                self::LARGE_LETTER => [
                    100 => 155,
                    250 => 210,
                    500 => 250,
                    750 => 270,
                ],
                self::SMALL_PARCEL_WIDE => [
                    2000 => 375,
                ],
                self::SMALL_PARCEL_DEEP => [
                    2000 => 375,
                ],
                self::SMALL_PARCEL_BIGGER => [
                    2000 => 375,
                ],
                self::MEDIUM_PARCEL => [
                    1000 => 615,
                    2000 => 615,
                    5000 => 765,
                    10000 => 765,
                    20000 => 1155,
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
                'height' => 80,
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
        ];

        return self::getBoxPricing($boxes, $bands, 20);
    }
}

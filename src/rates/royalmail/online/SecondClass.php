<?php
namespace verbb\shippy\rates\royalmail\online;

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
                    250 => 190,
                    500 => 230,
                    750 => 250,
                ],
                self::SMALL_PARCEL_WIDE => [
                    2000 => 325,
                ],
                self::SMALL_PARCEL_DEEP => [
                    2000 => 325,
                ],
                self::SMALL_PARCEL_BIGGER => [
                    2000 => 325,
                ],
                self::MEDIUM_PARCEL => [
                    2000 => 485,
                    10000 => 635,
                    20000 => 1025,
                ],
            ],
            '2025' => [
                self::LETTER => [
                    100 => 87,
                ],
                self::LARGE_LETTER => [
                    100 => 155,
                    250 => 180,
                    500 => 220,
                    750 => 250,
                ],
                self::SMALL_PARCEL_WIDE => [
                    2000 => 335,
                ],
                self::SMALL_PARCEL_DEEP => [
                    2000 => 335,
                ],
                self::SMALL_PARCEL_BIGGER => [
                    2000 => 335,
                ],
                self::MEDIUM_PARCEL => [
                    2000 => 495,
                    10000 => 655,
                    20000 => 1055,
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

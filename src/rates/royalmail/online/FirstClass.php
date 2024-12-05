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
                    100 => 135,
                ],
                self::LARGE_LETTER => [
                    100 => 210,
                    250 => 270,
                    500 => 330,
                    750 => 330,
                ],
                self::SMALL_PARCEL_WIDE => [
                    1000 => 399,
                    2000 => 399,
                ],
                self::SMALL_PARCEL_DEEP => [
                    1000 => 399,
                    2000 => 399,
                ],
                self::SMALL_PARCEL_BIGGER => [
                    1000 => 399,
                    2000 => 399,
                ],
                self::MEDIUM_PARCEL => [
                    1000 => 549,
                    2000 => 549,
                    5000 => 719,
                    10000 => 719,
                    20000 => 1129,
                ],
            ],
            '2024-10' => [
                self::LETTER => [
                    100 => 165,
                ],
                self::LARGE_LETTER => [
                    100 => 250,
                    250 => 330,
                    500 => 330,
                    750 => 330,
                ],
                self::SMALL_PARCEL_WIDE => [
                    1000 => 409,
                    2000 => 409,
                ],
                self::SMALL_PARCEL_DEEP => [
                    1000 => 409,
                    2000 => 409,
                ],
                self::SMALL_PARCEL_BIGGER => [
                    1000 => 409,
                    2000 => 409,
                ],
                self::MEDIUM_PARCEL => [
                    1000 => 569,
                    2000 => 569,
                    5000 => 739,
                    10000 => 739,
                    20000 => 1189,
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

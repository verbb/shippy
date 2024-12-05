<?php
namespace verbb\shippy\rates\royalmail\postoffice;

use verbb\shippy\carriers\RoyalMailRates;

class Tracked48 extends RoyalMailRates
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
                    750 => 270,
                ],
                self::SMALL_PARCEL_WIDE => [
                    2000 => 389,
                ],
                self::SMALL_PARCEL_DEEP => [
                    2000 => 389,
                ],
                self::SMALL_PARCEL_BIGGER => [
                    2000 => 389,
                ],
                self::MEDIUM_PARCEL => [
                    2000 => 629,
                    10000 => 779,
                    20000 => 1139,
                ],
                self::TUBE => [
                    2000 => 629,
                    10000 => 779,
                    20000 => 1139,
                ],
            ],
            '2024-10' => [
                self::LARGE_LETTER => [
                    750 => 280,
                ],
                self::SMALL_PARCEL_WIDE => [
                    2000 => 395,
                ],
                self::SMALL_PARCEL_DEEP => [
                    2000 => 395,
                ],
                self::SMALL_PARCEL_BIGGER => [
                    2000 => 395,
                ],
                self::MEDIUM_PARCEL => [
                    2000 => 645,
                    10000 => 795,
                    20000 => 1185,
                ],
                self::TUBE => [
                    2000 => 645,
                    10000 => 795,
                    20000 => 1185,
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

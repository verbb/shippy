<?php
namespace verbb\shippy\rates\royalmail\online;

use verbb\shippy\carriers\RoyalMailRates;

class SecondClassSigned extends RoyalMailRates
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
                    1000 => 319,
                    2000 => 319,
                ],
                self::SMALL_PARCEL_DEEP => [
                    1000 => 319,
                    2000 => 319,
                ],
                self::SMALL_PARCEL_BIGGER => [
                    1000 => 319,
                    2000 => 319,
                ],
                self::MEDIUM_PARCEL => [
                    1000 => 469,
                    2000 => 469,
                    5000 => 619,
                    10000 => 619,
                    20000 => 979,
                ],
            ],
            '2024-10' => [
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
                    1000 => 325,
                    2000 => 325,
                ],
                self::SMALL_PARCEL_DEEP => [
                    1000 => 325,
                    2000 => 325,
                ],
                self::SMALL_PARCEL_BIGGER => [
                    1000 => 325,
                    2000 => 325,
                ],
                self::MEDIUM_PARCEL => [
                    1000 => 485,
                    2000 => 485,
                    5000 => 635,
                    10000 => 635,
                    20000 => 1025,
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

        $boxPricing = self::getBoxPricing($boxes, $bands, 50);

        $signedForCost = self::getValueForYear([
            '2024' => 170,
        ]);

        $signedForPackageCost = self::getValueForYear([
            '2024' => 140,
        ]);

        foreach ($boxPricing as $key => &$box) {
            if (str_contains($key, 'letter-')) {
                $additionalCost = $signedForCost;
            } else {
                $additionalCost = $signedForPackageCost;
            }

            if ($additionalCost) {
                $box['price'] += $additionalCost;
            }
        }

        return $boxPricing;
    }
}

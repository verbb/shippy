<?php
namespace verbb\shippy\rates\royalmail;

use verbb\shippy\carriers\RoyalMailRates;

class FirstClassSigned extends RoyalMailRates
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
                    250 => 290,
                    500 => 350,
                    750 => 350,
                ],
                self::SMALL_PARCEL_WIDE => [
                    2000 => 459,
                ],
                self::SMALL_PARCEL_DEEP => [
                    2000 => 459,
                ],
                self::SMALL_PARCEL_BIGGER => [
                    2000 => 459,
                ],
                self::MEDIUM_PARCEL => [
                    2000 => 669,
                    5000 => 839,
                    10000 => 839,
                    20000 => 1249,
                ],
            ],
            '2024-10' => [
                self::LETTER => [
                    100 => 165,
                ],
                self::LARGE_LETTER => [
                    100 => 260,
                    250 => 350,
                    500 => 350,
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
                    5000 => 869,
                    10000 => 869,
                    20000 => 1319,
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
            '2024-10' => 170,
        ]);

        $signedForPackageCost = self::getValueForYear([
            '2024' => 140,
            '2024-10' => 140,
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

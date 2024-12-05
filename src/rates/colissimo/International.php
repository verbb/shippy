<?php
namespace verbb\shippy\rates\colissimo;

use verbb\shippy\carriers\ColissimoRates;

class International extends ColissimoRates
{
    // Static Methods
    // =========================================================================

    public static function getRates(): array
    {
        return [
            'pack-500' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 500,
                'price' => [
                    self::ZONE_INTERNATIONAL_B => 1640,
                    self::ZONE_INTERNATIONAL_C => 2400,
                ],
            ],
            'pack-1000' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 1000,
                'price' => [
                    self::ZONE_INTERNATIONAL_B => 1960,
                    self::ZONE_INTERNATIONAL_C => 2670,
                ],
            ],
            'pack-2000' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 2000,
                'price' => [
                    self::ZONE_INTERNATIONAL_B => 2140,
                    self::ZONE_INTERNATIONAL_C => 3670,
                ],
            ],
            'pack-5000' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 5000,
                'price' => [
                    self::ZONE_INTERNATIONAL_B => 2750,
                    self::ZONE_INTERNATIONAL_C => 5370,
                ],
            ],
            'pack-10000' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 10000,
                'price' => [
                    self::ZONE_INTERNATIONAL_B => 4550,
                    self::ZONE_INTERNATIONAL_C => 10150,
                ],
            ],
            'pack-20000' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 10000,
                'price' => [
                    self::ZONE_INTERNATIONAL_B => 7100,
                    self::ZONE_INTERNATIONAL_C => 16200,
                ],
            ],
        ];
    }
}

<?php
namespace verbb\shippy\rates\colissimo;

use verbb\shippy\carriers\ColissimoRates;

class Europe extends ColissimoRates
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
                    self::ZONE_INTERNATIONAL_A => 1230,
                ],
            ],
            'pack-1000' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 1000,
                'price' => [
                    self::ZONE_INTERNATIONAL_A => 1505,
                ],
            ],
            'pack-2000' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 2000,
                'price' => [
                    self::ZONE_INTERNATIONAL_A => 1680,
                ],
            ],
            'pack-5000' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 5000,
                'price' => [
                    self::ZONE_INTERNATIONAL_A => 2150,
                ],
            ],
            'pack-10000' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 10000,
                'price' => [
                    self::ZONE_INTERNATIONAL_A => 3550,
                ],
            ],
            'pack-30000' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 30000,
                'price' => [
                    self::ZONE_INTERNATIONAL_A => 5900,
                ],
            ],
        ];
    }
}

<?php
namespace verbb\shippy\rates\colissimo;

use verbb\shippy\carriers\ColissimoRates;

class TomOutremer extends ColissimoRates
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
                    self::ZONE_TOM => 1120,
                ],
            ],
            'pack-1000' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 1000,
                'price' => [
                    self::ZONE_TOM => 1680,
                ],
            ],
            'pack-2000' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 2000,
                'price' => [
                    self::ZONE_TOM => 2960,
                ],
            ],
            'pack-5000' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 5000,
                'price' => [
                    self::ZONE_TOM => 4960,
                ],
            ],
            'pack-10000' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 10000,
                'price' => [
                    self::ZONE_TOM => 9660,
                ],
            ],
            'pack-30000' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 30000,
                'price' => [
                    self::ZONE_TOM => 25000,
                ],
            ],
        ];
    }
}

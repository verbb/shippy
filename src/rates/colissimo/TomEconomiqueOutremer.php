<?php
namespace verbb\shippy\rates\colissimo;

use verbb\shippy\carriers\ColissimoRates;

class TomEconomiqueOutremer extends ColissimoRates
{
    // Static Methods
    // =========================================================================

    public static function getRates(): array
    {
        return [
            'pack-500' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 500,
                'price' => [
                    self::ZONE_TOM => 1080,
                ],
            ],
            'pack-1000' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 1000,
                'price' => [
                    self::ZONE_TOM => 1630,
                ],
            ],
            'pack-2000' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 2000,
                'price' => [
                    self::ZONE_TOM => 2900,
                ],
            ],
            'pack-5000' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 5000,
                'price' => [
                    self::ZONE_TOM => 4800,
                ],
            ],
            'pack-10000' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 10000,
                'price' => [
                    self::ZONE_TOM => 9450,
                ],
            ],
            'pack-30000' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 30000,
                'price' => [
                    self::ZONE_TOM => 24800,
                ],
            ],
        ];
    }
}

<?php
namespace verbb\shippy\rates\colissimo;

use verbb\shippy\carriers\ColissimoRates;

class FrFrance extends ColissimoRates
{
    // Static Methods
    // =========================================================================

    public static function getRates(): array
    {
        return [
            'pack-250' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 250,
                'price' => [
                    self::ZONE_FR => 495,
                ],
            ],
            'pack-500' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 500,
                'price' => [
                    self::ZONE_FR => 615,
                ],
            ],
            'pack-750' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 750,
                'price' => [
                    self::ZONE_FR => 700,
                ],
            ],
            'pack-1000' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 1000,
                'price' => [
                    self::ZONE_FR => 765,
                ],
            ],
            'pack-2000' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 2000,
                'price' => [
                    self::ZONE_FR => 865,
                ],
            ],
            'pack-5000' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 5000,
                'price' => [
                    self::ZONE_FR => 1315,
                ],
            ],
            'pack-10000' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 10000,
                'price' => [
                    self::ZONE_FR => 1920,
                ],
            ],
            'pack-30000' => [
                'length' => 1000,
                'width' => 990,
                'height' => 990,
                'weight' => 30000,
                'price' => [
                    self::ZONE_FR => 2730,
                ],
            ],
        ];
    }
}

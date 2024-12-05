<?php
namespace verbb\shippy\rates\colissimo;

use verbb\shippy\carriers\ColissimoRates;

class DomOutremer extends ColissimoRates
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
                    self::ZONE_DOM => 930,
                ],
            ],
            'pack-1000' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 1000,
                'price' => [
                    self::ZONE_DOM => 1410,
                ],
            ],
            'pack-2000' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 2000,
                'price' => [
                    self::ZONE_DOM => 1920,
                ],
            ],
            'pack-5000' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 5000,
                'price' => [
                    self::ZONE_DOM => 2890,
                ],
            ],
            'pack-10000' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 10000,
                'price' => [
                    self::ZONE_DOM => 4640,
                ],
            ],
            'pack-30000' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 30000,
                'price' => [
                    self::ZONE_DOM => 10360,
                ],
            ],
        ];
    }
}

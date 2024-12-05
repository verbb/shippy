<?php
namespace verbb\shippy\rates\colissimo;

use verbb\shippy\carriers\ColissimoRates;

class FrEmballageFrance extends ColissimoRates
{
    // Static Methods
    // =========================================================================

    public static function getRates(): array
    {
        return [
            'bubble-bag-XS' => [
                'length' => 180,
                'width' => 230,
                'height' => 20,
                'weight' => 1000,
                'price' => [
                    self::ZONE_FR => 1000,
                ],
            ],
            'bubble-bag-S' => [
                'length' => 290,
                'width' => 330,
                'height' => 20,
                'weight' => 3000,
                'price' => [
                    self::ZONE_FR => 1000,
                ],
            ],
            'cardboard-sleeve-XS' => [
                'length' => 220,
                'width' => 140,
                'height' => 50,
                'weight' => 1000,
                'price' => [
                    self::ZONE_FR => 1000,
                ],
            ],
            'cardboard-sleeve-S' => [
                'length' => 335,
                'width' => 215,
                'height' => 60,
                'weight' => 3000,
                'price' => [
                    self::ZONE_FR => 1000,
                ],
            ],
            'box-S' => [
                'length' => 280,
                'width' => 210,
                'height' => 20,
                'weight' => 1000,
                'price' => [
                    self::ZONE_FR => 895,
                ],
            ],
            'box-M' => [
                'length' => 230,
                'width' => 130,
                'height' => 100,
                'weight' => 3000,
                'price' => [
                    self::ZONE_FR => 800,
                ],
            ],
            'box-L' => [
                'length' => 315,
                'width' => 210,
                'height' => 157,
                'weight' => 5000,
                'price' => [
                    self::ZONE_FR => 1200,
                ],
            ],
            'CD' => [
                'length' => 217,
                'width' => 140,
                'height' => 60,
                'weight' => 1000,
                'price' => [
                    self::ZONE_FR => 790,
                ],
            ],
            '1-Bottle' => [
                'length' => 390,
                'width' => 168,
                'height' => 104,
                'weight' => 2000,
                'price' => [
                    self::ZONE_FR => 1110,
                ],
            ],
            '2-Bottles' => [
                'length' => 390,
                'width' => 297,
                'height' => 106,
                'weight' => 5000,
                'price' => [
                    self::ZONE_FR => 1360,
                ],
            ],
            '3-Bottles' => [
                'length' => 390,
                'width' => 425,
                'height' => 106,
                'weight' => 7000,
                'price' => [
                    self::ZONE_FR => 1460,
                ],
            ],
        ];
    }
}

<?php
namespace verbb\shippy\rates\colissimo;

use verbb\shippy\carriers\ColissimoRates;

class EmballageInternational extends ColissimoRates
{
    // Static Methods
    // =========================================================================

    public static function getRates(): array
    {
        return [
            'pack-500' => [
                'length' => 500,
                'width' => 250,
                'height' => 250,
                'weight' => 500,
                'price' => [
                    self::ZONE_INTERNATIONAL_B => 1620,
                    self::ZONE_INTERNATIONAL_C => 2370,
                ],
            ],
            'pack-1000' => [
                'length' => 500,
                'width' => 250,
                'height' => 250,
                'weight' => 1000,
                'price' => [
                    self::ZONE_INTERNATIONAL_B => 1935,
                    self::ZONE_INTERNATIONAL_C => 2630,
                ],
            ],
            'pack-2000' => [
                'length' => 500,
                'width' => 250,
                'height' => 250,
                'weight' => 2000,
                'price' => [
                    self::ZONE_INTERNATIONAL_B => 2105,
                    self::ZONE_INTERNATIONAL_C => 3610,
                ],
            ],
            'pack-5000' => [
                'length' => 500,
                'width' => 250,
                'height' => 250,
                'weight' => 5000,
                'price' => [
                    self::ZONE_INTERNATIONAL_B => 2700,
                    self::ZONE_INTERNATIONAL_C => 5300,
                ],
            ],
            'pack-10000' => [
                'length' => 500,
                'width' => 250,
                'height' => 250,
                'weight' => 10000,
                'price' => [
                    self::ZONE_INTERNATIONAL_B => 4500,
                    self::ZONE_INTERNATIONAL_C => 10000,
                ],
            ],
            'pack-20000' => [
                'length' => 500,
                'width' => 250,
                'height' => 250,
                'weight' => 10000,
                'price' => [
                    self::ZONE_INTERNATIONAL_B => 7000,
                    self::ZONE_INTERNATIONAL_C => 16000,
                ],
            ],
        ];
    }
}

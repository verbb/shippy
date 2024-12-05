<?php
namespace verbb\shippy\rates\postnl;

use verbb\shippy\carriers\PostNlRates;

class InternationalBrief extends PostNLRates
{
    // Static Methods
    // =========================================================================

    public static function getRates(): array
    {
        return [
            'brief-20g' => [
                'length' => 380,
                'width' => 265,
                'height' => 32,
                'weight' => 20,
                'price' => [
                    self::ZONE_NL => false,
                    self::ZONE_EU1 => 140,
                    self::ZONE_EU2 => 140,
                    self::ZONE_EU3 => false,
                    self::ZONE_WORLD => 140,
                ],
            ],
            'brief-50g' => [
                'length' => 380,
                'width' => 265,
                'height' => 32,
                'weight' => 50,
                'price' => [
                    self::ZONE_NL => false,
                    self::ZONE_EU1 => 280,
                    self::ZONE_EU2 => 280,
                    self::ZONE_EU3 => false,
                    self::ZONE_WORLD => 280,
                ],
            ],
            'brief-100g' => [
                'length' => 380,
                'width' => 265,
                'height' => 32,
                'weight' => 100,
                'price' => [
                    self::ZONE_NL => false,
                    self::ZONE_EU1 => 420,
                    self::ZONE_EU2 => 420,
                    self::ZONE_EU3 => false,
                    self::ZONE_WORLD => 420,
                ],
            ],
            'brief-250g' => [
                'length' => 380,
                'width' => 265,
                'height' => 32,
                'weight' => 250,
                'price' => [
                    self::ZONE_NL => false,
                    self::ZONE_EU1 => 840,
                    self::ZONE_EU2 => 840,
                    self::ZONE_EU3 => false,
                    self::ZONE_WORLD => 840,
                ],
            ],
            'brief-2000g' => [
                'length' => 380,
                'width' => 265,
                'height' => 32,
                'weight' => 2000,
                'price' => [
                    self::ZONE_NL => false,
                    self::ZONE_EU1 => 980,
                    self::ZONE_EU2 => 1260,
                    self::ZONE_EU3 => false,
                    self::ZONE_WORLD => 1540,
                ],
            ],
        ];
    }
}

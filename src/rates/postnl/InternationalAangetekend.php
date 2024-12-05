<?php
namespace verbb\shippy\rates\postnl;

use verbb\shippy\carriers\PostNlRates;

class InternationalAangetekend extends PostNLRates
{
    // Static Methods
    // =========================================================================

    public static function getRates(): array
    {
        return [
            'pakket-2kg' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 2000,
                'price' => [
                    self::ZONE_NL => false,
                    self::ZONE_EU1 => 1550,
                    self::ZONE_EU2 => 2100,
                    self::ZONE_EU3 => false,
                    self::ZONE_WORLD => 2680,
                ],
            ],
            'pakket-5kg' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 5000,
                'price' => [
                    self::ZONE_NL => false,
                    self::ZONE_EU1 => 2200,
                    self::ZONE_EU2 => 2750,
                    self::ZONE_EU3 => false,
                    self::ZONE_WORLD => 3680,
                ],
            ],
            'pakket-10kg' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 10000,
                'price' => [
                    self::ZONE_NL => false,
                    self::ZONE_EU1 => 2750,
                    self::ZONE_EU2 => 3350,
                    self::ZONE_EU3 => false,
                    self::ZONE_WORLD => 6080,
                ],
            ],
            'pakket-20kg' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 20000,
                'price' => [
                    self::ZONE_NL => false,
                    self::ZONE_EU1 => 3650,
                    self::ZONE_EU2 => 4250,
                    self::ZONE_EU3 => false,
                    self::ZONE_WORLD => 10780,
                ],
            ],
            'pakket-30kg' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 30000,
                'price' => [
                    self::ZONE_NL => false,
                    self::ZONE_EU1 => 4750,
                    self::ZONE_EU2 => 5750,
                    self::ZONE_EU3 => false,
                    self::ZONE_WORLD => false,
                ],
            ],
        ];
    }
}

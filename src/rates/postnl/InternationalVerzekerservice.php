<?php
namespace verbb\shippy\rates\postnl;

use verbb\shippy\carriers\PostNlRates;

class InternationalVerzekerservice extends PostNLRates
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
                    self::ZONE_EU1 => 2300,
                    self::ZONE_EU2 => 2850,
                    self::ZONE_EU3 => false,
                    self::ZONE_WORLD => 3430,
                ],
            ],
            'pakket-5kg' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 5000,
                'price' => [
                    self::ZONE_NL => false,
                    self::ZONE_EU1 => 2950,
                    self::ZONE_EU2 => 3500,
                    self::ZONE_EU3 => false,
                    self::ZONE_WORLD => 4430,
                ],
            ],
            'pakket-10kg' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 10000,
                'price' => [
                    self::ZONE_NL => false,
                    self::ZONE_EU1 => 3500,
                    self::ZONE_EU2 => 4100,
                    self::ZONE_EU3 => false,
                    self::ZONE_WORLD => 6830,
                ],
            ],
            'pakket-20kg' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 20000,
                'price' => [
                    self::ZONE_NL => false,
                    self::ZONE_EU1 => 4400,
                    self::ZONE_EU2 => 5000,
                    self::ZONE_EU3 => false,
                    self::ZONE_WORLD => 11530,
                ],
            ],
            'pakket-30kg' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 30000,
                'price' => [
                    self::ZONE_NL => false,
                    self::ZONE_EU1 => 5500,
                    self::ZONE_EU2 => 6500,
                    self::ZONE_EU3 => false,
                    self::ZONE_WORLD => false,
                ],
            ],
        ];
    }
}

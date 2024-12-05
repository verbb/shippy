<?php
namespace verbb\shippy\rates\postnl;

use verbb\shippy\carriers\PostNlRates;

class InternationalPakket extends PostNLRates
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
                    self::ZONE_EU1 => 1300,
                    self::ZONE_EU2 => 1850,
                    self::ZONE_EU3 => false,
                    self::ZONE_WORLD => 2430,
                ],
            ],
            'pakket-5kg' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 5000,
                'price' => [
                    self::ZONE_NL => false,
                    self::ZONE_EU1 => 1950,
                    self::ZONE_EU2 => 2500,
                    self::ZONE_EU3 => false,
                    self::ZONE_WORLD => 3430,
                ],
            ],
            'pakket-10kg' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 10000,
                'price' => [
                    self::ZONE_NL => false,
                    self::ZONE_EU1 => 2500,
                    self::ZONE_EU2 => 3100,
                    self::ZONE_EU3 => false,
                    self::ZONE_WORLD => 5830,
                ],
            ],
            'pakket-20kg' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 20000,
                'price' => [
                    self::ZONE_NL => false,
                    self::ZONE_EU1 => 3400,
                    self::ZONE_EU2 => 4000,
                    self::ZONE_EU3 => false,
                    self::ZONE_WORLD => 10530,
                ],
            ],
            'pakket-30kg' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 30000,
                'price' => [
                    self::ZONE_NL => false,
                    self::ZONE_EU1 => 4500,
                    self::ZONE_EU2 => 5500,
                    self::ZONE_EU3 => false,
                    self::ZONE_WORLD => false,
                ],
            ],
        ];
    }
}

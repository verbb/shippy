<?php
namespace verbb\shippy\rates\postnl;

use verbb\shippy\carriers\PostNlRates;

class InternationalPakketNoTrackAndTrace extends PostNLRates
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
                    self::ZONE_EU1 => 980,
                    self::ZONE_EU2 => 1260,
                    self::ZONE_EU3 => false,
                    self::ZONE_WORLD => 1820,
                ],
            ],
        ];
    }
}

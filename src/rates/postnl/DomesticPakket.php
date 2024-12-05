<?php
namespace verbb\shippy\rates\postnl;

use verbb\shippy\carriers\PostNlRates;

class DomesticPakket extends PostNLRates
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
                    self::ZONE_NL => 695,
                ],
            ],
            'pakket-5kg' => [
                'length' => 1000,
                'width' => 500,
                'height' => 500,
                'weight' => 5000,
                'price' => [
                    self::ZONE_NL => 695,
                ],
            ],
            'pakket-10kg' => [
                'length' => 1760,
                'width' => 780,
                'height' => 580,
                'weight' => 10000,
                'price' => [
                    self::ZONE_NL => 695,
                ],
            ],
            'pakket-20kg' => [
                'length' => 1760,
                'width' => 780,
                'height' => 580,
                'weight' => 20000,
                'price' => [
                    self::ZONE_NL => 1325,
                ],
            ],
            'pakket-30kg' => [
                'length' => 1760,
                'width' => 780,
                'height' => 580,
                'weight' => 30000,
                'price' => [
                    self::ZONE_NL => 1325,
                ],
            ],
        ];
    }
}

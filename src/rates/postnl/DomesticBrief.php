<?php
namespace verbb\shippy\rates\postnl;

use verbb\shippy\carriers\PostNlRates;

class DomesticBrief extends PostNLRates
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
                    self::ZONE_NL => 83,
                ],
            ],
            'brief-50g' => [
                'length' => 380,
                'width' => 265,
                'height' => 32,
                'weight' => 50,
                'price' => [
                    self::ZONE_NL => 166,
                ],
            ],
            'brief-100g' => [
                'length' => 380,
                'width' => 265,
                'height' => 32,
                'weight' => 100,
                'price' => [
                    self::ZONE_NL => 249,
                ],
            ],
            'brief-250g' => [
                'length' => 380,
                'width' => 265,
                'height' => 32,
                'weight' => 250,
                'price' => [
                    self::ZONE_NL => 332,
                ],
            ],
            'brief-2kg' => [
                'length' => 380,
                'width' => 265,
                'height' => 32,
                'weight' => 2000,
                'price' => [
                    self::ZONE_NL => 415,
                ],
            ],
        ];
    }
}

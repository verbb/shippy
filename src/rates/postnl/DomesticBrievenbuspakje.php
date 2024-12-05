<?php
namespace verbb\shippy\rates\postnl;

use verbb\shippy\carriers\PostNlRates;

class DomesticBrievenbuspakje extends PostNLRates
{
    // Static Methods
    // =========================================================================

    public static function getRates(): array
    {
        return [
            'brievenbuspakje-2kg' => [
                'length' => 380,
                'width' => 265,
                'height' => 32,
                'weight' => 2000,
                'price' => [
                    self::ZONE_NL => 425,
                ],
            ],
        ];
    }
}

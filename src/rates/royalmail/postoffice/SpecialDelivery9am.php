<?php
namespace verbb\shippy\rates\royalmail\postoffice;

use verbb\shippy\carriers\RoyalMailRates;

class SpecialDelivery9am extends RoyalMailRates
{
    // Static Methods
    // =========================================================================

    public static function getRates(string $countryCode): array
    {
        $zone = self::getZone($countryCode);

        if ($zone !== 'UK') {
            return [];
        }

        $excluded = [
            'GG', // Guernsey
            'IM', // Isle of Man
            'JE', // Jersey
        ];

        if (in_array($countryCode, $excluded)) {
            return [];
        }

        $bands = [
            '2024' => [
                'packet-50' => [
                    100 => 3195,
                    500 => 3695,
                    1000 => 4195,
                    2000 => 5295,
                ],
                'packet-1000' => [
                    100 => 3895,
                    500 => 4395,
                    1000 => 4895,
                    2000 => 5995,
                ],
                'packet-2500' => [
                    100 => 4695,
                    500 => 5195,
                    1000 => 5695,
                    2000 => 6795,
                ],
            ],
            '2025' => [
                'packet-50' => [
                    100 => 3895,
                    500 => 4395,
                    1000 => 4895,
                    2000 => 6095,
                ],
                'packet-1000' => [
                    100 => 4595,
                    500 => 5095,
                    1000 => 5595,
                    2000 => 6795,
                ],
                'packet-2500' => [
                    100 => 5395,
                    500 => 5895,
                    1000 => 6395,
                    2000 => 7595,
                ],
            ],
        ];

        $boxes = [
            'packet-50' => [
                'length' => 610,
                'width' => 460,
                'height' => 460,
                'weight' => 2000,
            ],
            'packet-1000' => [
                'length' => 610,
                'width' => 460,
                'height' => 460,
                'weight' => 2000,
            ],
            'packet-2500' => [
                'length' => 610,
                'width' => 460,
                'height' => 460,
                'weight' => 2000,
            ],
        ];

        $boxPricing = self::getBoxPricing($boxes, $bands);

        foreach ($boxPricing as $key => $box) {
            // 20% VAT
            if (!self::$includeVat) {
                $boxPricing[$key]['price'] = $box['price'] / 1.2;
            }
        }

        return $boxPricing;
    }
}

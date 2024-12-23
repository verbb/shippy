<?php
namespace verbb\shippy\rates\royalmail\postoffice;

use verbb\shippy\carriers\RoyalMailRates;

class ParcelforceIrelandexpress extends RoyalMailRates
{
    // Static Methods
    // =========================================================================

    public static function getRates(string $countryCode): array
    {
        $zone = self::getZone($countryCode);

        if ($zone === 'UK') {
            return [];
        }

        $bands = [
            '2021' => [
                '5' => [
                    500 => 2754,
                    1000 => 2754,
                    1500 => 2754,
                    2000 => 2754,
                    2500 => 2754,
                    3000 => 2754,
                    3500 => 2754,
                    4000 => 2754,
                    4500 => 2754,
                    5000 => 2754,
                    5500 => 2886,
                    6000 => 3018,
                    6500 => 3150,
                    7000 => 3282,
                    7500 => 3414,
                    8000 => 3546,
                    8500 => 3678,
                    9000 => 3810,
                    9500 => 3942,
                    10000 => 4074,
                    10500 => 4158,
                    11000 => 4242,
                    11500 => 4326,
                    12000 => 4410,
                    12500 => 4494,
                    13000 => 4578,
                    13500 => 4662,
                    14000 => 4746,
                    14500 => 4830,
                    15000 => 4914,
                    15500 => 5022,
                    16000 => 5130,
                    16500 => 5238,
                    17000 => 5346,
                    17500 => 5454,
                    18000 => 5562,
                    18500 => 5670,
                    19000 => 5778,
                    19500 => 5886,
                    20000 => 5994,
                    20500 => 6102,
                    21000 => 6210,
                    21500 => 6318,
                    22000 => 6426,
                    22500 => 6534,
                    23000 => 6642,
                    23500 => 6750,
                    24000 => 6858,
                    24500 => 6966,
                    25000 => 7074,
                    25500 => 7182,
                    26000 => 7290,
                    26500 => 7398,
                    27000 => 7506,
                    27500 => 7614,
                    28000 => 7722,
                    28500 => 7830,
                    29000 => 7938,
                    29500 => 8046,
                    30000 => 8154,
                ],
            ],
        ];

        return self::getParcelforceBoxPricing($bands, $countryCode, [
            'maximumInclusiveCompensation' => 200,
            'maximumTotalCover' => 2500,
        ]);
    }
}

<?php
namespace verbb\shippy\rates\royalmail\postoffice;

use verbb\shippy\carriers\RoyalMailRates;

class ParcelforceGlobalexpress extends RoyalMailRates
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
                '4' => [
                    500 => 6000,
                    1000 => 6282,
                    1500 => 6564,
                    2000 => 6846,
                    2500 => 7128,
                    3000 => 7422,
                    3500 => 7716,
                    4000 => 8010,
                    4500 => 8304,
                    5000 => 8598,
                    5500 => 8892,
                    6000 => 9186,
                    6500 => 9480,
                    7000 => 9774,
                    7500 => 10068,
                    8000 => 10362,
                    8500 => 10656,
                    9000 => 10950,
                    9500 => 11244,
                    10000 => 11538,
                    10500 => 11808,
                    11000 => 12078,
                    11500 => 12348,
                    12000 => 12618,
                    12500 => 12888,
                    13000 => 13158,
                    13500 => 13428,
                    14000 => 13698,
                    14500 => 13968,
                    15000 => 14238,
                    15500 => 14508,
                    16000 => 14778,
                    16500 => 15048,
                    17000 => 15318,
                    17500 => 15588,
                    18000 => 15858,
                    18500 => 16128,
                    19000 => 16398,
                    19500 => 16668,
                    20000 => 16938,
                    20500 => 17208,
                    21000 => 17478,
                    21500 => 17748,
                    22000 => 18018,
                    22500 => 18288,
                    23000 => 18558,
                    23500 => 18828,
                    24000 => 19098,
                    24500 => 19368,
                    25000 => 19638,
                    25500 => 19908,
                    26000 => 20178,
                    26500 => 20448,
                    27000 => 20718,
                    27500 => 20988,
                    28000 => 21258,
                    28500 => 21528,
                    29000 => 21798,
                    29500 => 22068,
                    30000 => 22338,
                ],
                '5' => [
                    500 => 5460,
                    1000 => 5742,
                    1500 => 6024,
                    2000 => 6306,
                    2500 => 6588,
                    3000 => 6870,
                    3500 => 7152,
                    4000 => 7434,
                    4500 => 7716,
                    5000 => 7998,
                    5500 => 8190,
                    6000 => 8382,
                    6500 => 8574,
                    7000 => 8766,
                    7500 => 8958,
                    8000 => 9150,
                    8500 => 9342,
                    9000 => 9534,
                    9500 => 9726,
                    10000 => 9918,
                    10500 => 10182,
                    11000 => 10446,
                    11500 => 10710,
                    12000 => 10974,
                    12500 => 11238,
                    13000 => 11502,
                    13500 => 11766,
                    14000 => 12030,
                    14500 => 12294,
                    15000 => 12558,
                    15500 => 12756,
                    16000 => 12954,
                    16500 => 13152,
                    17000 => 13350,
                    17500 => 13548,
                    18000 => 13746,
                    18500 => 13944,
                    19000 => 14142,
                    19500 => 14340,
                    20000 => 14538,
                    20500 => 14736,
                    21000 => 14934,
                    21500 => 15132,
                    22000 => 15330,
                    22500 => 15528,
                    23000 => 15726,
                    23500 => 15924,
                    24000 => 16122,
                    24500 => 16320,
                    25000 => 16518,
                    25500 => 16716,
                    26000 => 16914,
                    26500 => 17112,
                    27000 => 17310,
                    27500 => 17508,
                    28000 => 17706,
                    28500 => 17904,
                    29000 => 18102,
                    29500 => 18300,
                    30000 => 18498,
                ],
                '6' => [
                    500 => 5112,
                    1000 => 5454,
                    1500 => 5796,
                    2000 => 6138,
                    2500 => 6480,
                    3000 => 6756,
                    3500 => 7032,
                    4000 => 7308,
                    4500 => 7584,
                    5000 => 7860,
                    5500 => 8082,
                    6000 => 8304,
                    6500 => 8526,
                    7000 => 8748,
                    7500 => 8970,
                    8000 => 9192,
                    8500 => 9414,
                    9000 => 9636,
                    9500 => 9858,
                    10000 => 10080,
                    10500 => 10302,
                    11000 => 10524,
                    11500 => 10746,
                    12000 => 10968,
                    12500 => 11190,
                    13000 => 11412,
                    13500 => 11634,
                    14000 => 11856,
                    14500 => 12078,
                    15000 => 12300,
                    15500 => 12486,
                    16000 => 12672,
                    16500 => 12858,
                    17000 => 13044,
                    17500 => 13230,
                    18000 => 13416,
                    18500 => 13602,
                    19000 => 13788,
                    19500 => 13974,
                    20000 => 14160,
                    20500 => 14346,
                    21000 => 14532,
                    21500 => 14718,
                    22000 => 14904,
                    22500 => 15090,
                    23000 => 15276,
                    23500 => 15462,
                    24000 => 15648,
                    24500 => 15834,
                    25000 => 16020,
                    25500 => 16206,
                    26000 => 16392,
                    26500 => 16578,
                    27000 => 16764,
                    27500 => 16950,
                    28000 => 17136,
                    28500 => 17322,
                    29000 => 17508,
                    29500 => 17694,
                    30000 => 17880,
                ],
                '7' => [
                    500 => 5160,
                    1000 => 5556,
                    1500 => 5952,
                    2000 => 6348,
                    2500 => 6744,
                    3000 => 7074,
                    3500 => 7404,
                    4000 => 7734,
                    4500 => 8064,
                    5000 => 8394,
                    5500 => 8670,
                    6000 => 8946,
                    6500 => 9222,
                    7000 => 9498,
                    7500 => 9774,
                    8000 => 10050,
                    8500 => 10326,
                    9000 => 10602,
                    9500 => 10878,
                    10000 => 11154,
                    10500 => 11370,
                    11000 => 11586,
                    11500 => 11802,
                    12000 => 12018,
                    12500 => 12234,
                    13000 => 12450,
                    13500 => 12666,
                    14000 => 12882,
                    14500 => 13098,
                    15000 => 13314,
                    15500 => 13518,
                    16000 => 13722,
                    16500 => 13926,
                    17000 => 14130,
                    17500 => 14334,
                    18000 => 14538,
                    18500 => 14742,
                    19000 => 14946,
                    19500 => 15150,
                    20000 => 15354,
                    20500 => 15558,
                    21000 => 15762,
                    21500 => 15966,
                    22000 => 16170,
                    22500 => 16374,
                    23000 => 16578,
                    23500 => 16782,
                    24000 => 16986,
                    24500 => 17190,
                    25000 => 17394,
                    25500 => 17598,
                    26000 => 17802,
                    26500 => 18006,
                    27000 => 18210,
                    27500 => 18414,
                    28000 => 18618,
                    28500 => 18822,
                    29000 => 19026,
                    29500 => 19230,
                    30000 => 19434,
                ],
                '8' => [
                    500 => 5580,
                    1000 => 5982,
                    1500 => 6384,
                    2000 => 6786,
                    2500 => 7188,
                    3000 => 7542,
                    3500 => 7896,
                    4000 => 8250,
                    4500 => 8604,
                    5000 => 8958,
                    5500 => 9300,
                    6000 => 9642,
                    6500 => 9984,
                    7000 => 10326,
                    7500 => 10668,
                    8000 => 11010,
                    8500 => 11352,
                    9000 => 11694,
                    9500 => 12036,
                    10000 => 12378,
                    10500 => 12690,
                    11000 => 13002,
                    11500 => 13314,
                    12000 => 13626,
                    12500 => 13938,
                    13000 => 14250,
                    13500 => 14562,
                    14000 => 14874,
                    14500 => 15186,
                    15000 => 15498,
                    15500 => 15786,
                    16000 => 16074,
                    16500 => 16362,
                    17000 => 16650,
                    17500 => 16938,
                    18000 => 17226,
                    18500 => 17514,
                    19000 => 17802,
                    19500 => 18090,
                    20000 => 18378,
                    20500 => 18666,
                    21000 => 18954,
                    21500 => 19242,
                    22000 => 19530,
                    22500 => 19818,
                    23000 => 20106,
                    23500 => 20394,
                    24000 => 20682,
                    24500 => 20970,
                    25000 => 21258,
                    25500 => 21546,
                    26000 => 21834,
                    26500 => 22122,
                    27000 => 22410,
                    27500 => 22698,
                    28000 => 22986,
                    28500 => 23274,
                    29000 => 23562,
                    29500 => 23850,
                    30000 => 24138,
                ],
                '9' => [
                    500 => 5988,
                    1000 => 6558,
                    1500 => 7128,
                    2000 => 7698,
                    2500 => 8268,
                    3000 => 8802,
                    3500 => 9336,
                    4000 => 9870,
                    4500 => 10404,
                    5000 => 10938,
                    5500 => 11364,
                    6000 => 11790,
                    6500 => 12216,
                    7000 => 12642,
                    7500 => 13068,
                    8000 => 13494,
                    8500 => 13920,
                    9000 => 14346,
                    9500 => 14772,
                    10000 => 15198,
                    10500 => 15582,
                    11000 => 15966,
                    11500 => 16350,
                    12000 => 16734,
                    12500 => 17118,
                    13000 => 17502,
                    13500 => 17886,
                    14000 => 18270,
                    14500 => 18654,
                    15000 => 19038,
                    15500 => 19446,
                    16000 => 19854,
                    16500 => 20262,
                    17000 => 20670,
                    17500 => 21078,
                    18000 => 21486,
                    18500 => 21894,
                    19000 => 22302,
                    19500 => 22710,
                    20000 => 23118,
                    20500 => 23526,
                    21000 => 23934,
                    21500 => 24342,
                    22000 => 24750,
                    22500 => 25158,
                    23000 => 25566,
                    23500 => 25974,
                    24000 => 26382,
                    24500 => 26790,
                    25000 => 27198,
                    25500 => 27606,
                    26000 => 28014,
                    26500 => 28422,
                    27000 => 28830,
                    27500 => 29238,
                    28000 => 29646,
                    28500 => 30054,
                    29000 => 30462,
                    29500 => 30870,
                    30000 => 31278,
                ],
                '9_NON_EU' => [
                    500 => 5988,
                    1000 => 6558,
                    1500 => 7128,
                    2000 => 7698,
                    2500 => 8268,
                    3000 => 8802,
                    3500 => 9336,
                    4000 => 9870,
                    4500 => 10404,
                    5000 => 10938,
                    5500 => 11364,
                    6000 => 11790,
                    6500 => 12216,
                    7000 => 12642,
                    7500 => 13068,
                    8000 => 13494,
                    8500 => 13920,
                    9000 => 14346,
                    9500 => 14772,
                    10000 => 15198,
                    10500 => 15582,
                    11000 => 15966,
                    11500 => 16350,
                    12000 => 16734,
                    12500 => 17118,
                    13000 => 17502,
                    13500 => 17886,
                    14000 => 18270,
                    14500 => 18654,
                    15000 => 19038,
                    15500 => 19446,
                    16000 => 19854,
                    16500 => 20262,
                    17000 => 20670,
                    17500 => 21078,
                    18000 => 21486,
                    18500 => 21894,
                    19000 => 22302,
                    19500 => 22710,
                    20000 => 23118,
                    20500 => 23526,
                    21000 => 23934,
                    21500 => 24342,
                    22000 => 24750,
                    22500 => 25158,
                    23000 => 25566,
                    23500 => 25974,
                    24000 => 26382,
                    24500 => 26790,
                    25000 => 27198,
                    25500 => 27606,
                    26000 => 28014,
                    26500 => 28422,
                    27000 => 28830,
                    27500 => 29238,
                    28000 => 29646,
                    28500 => 30054,
                    29000 => 30462,
                    29500 => 30870,
                    30000 => 31278,
                ],
                '10' => [
                    500 => 6180,
                    1000 => 6780,
                    1500 => 7380,
                    2000 => 7980,
                    2500 => 8580,
                    3000 => 9018,
                    3500 => 9456,
                    4000 => 9894,
                    4500 => 10332,
                    5000 => 10770,
                    5500 => 11220,
                    6000 => 11670,
                    6500 => 12120,
                    7000 => 12570,
                    7500 => 13020,
                    8000 => 13470,
                    8500 => 13920,
                    9000 => 14370,
                    9500 => 14820,
                    10000 => 15270,
                    10500 => 15624,
                    11000 => 15978,
                    11500 => 16332,
                    12000 => 16686,
                    12500 => 17040,
                    13000 => 17394,
                    13500 => 17748,
                    14000 => 18102,
                    14500 => 18456,
                    15000 => 18810,
                    15500 => 19164,
                    16000 => 19518,
                    16500 => 19872,
                    17000 => 20226,
                    17500 => 20580,
                    18000 => 20934,
                    18500 => 21288,
                    19000 => 21642,
                    19500 => 21996,
                    20000 => 22350,
                    20500 => 22704,
                    21000 => 23058,
                    21500 => 23412,
                    22000 => 23766,
                    22500 => 24120,
                    23000 => 24474,
                    23500 => 24828,
                    24000 => 25182,
                    24500 => 25536,
                    25000 => 25890,
                    25500 => 26244,
                    26000 => 26598,
                    26500 => 26952,
                    27000 => 27306,
                    27500 => 27660,
                    28000 => 28014,
                    28500 => 28368,
                    29000 => 28722,
                    29500 => 29076,
                    30000 => 29430,
                ],
                '11' => [
                    500 => 7410,
                    1000 => 8166,
                    1500 => 8922,
                    2000 => 9678,
                    2500 => 10434,
                    3000 => 11112,
                    3500 => 11790,
                    4000 => 12468,
                    4500 => 13146,
                    5000 => 13824,
                    5500 => 14418,
                    6000 => 15012,
                    6500 => 15606,
                    7000 => 16200,
                    7500 => 16794,
                    8000 => 17388,
                    8500 => 17982,
                    9000 => 18576,
                    9500 => 19170,
                    10000 => 19764,
                    10500 => 20352,
                    11000 => 20940,
                    11500 => 21528,
                    12000 => 22116,
                    12500 => 22704,
                    13000 => 23292,
                    13500 => 23880,
                    14000 => 24468,
                    14500 => 25056,
                    15000 => 25644,
                    15500 => 26232,
                    16000 => 26820,
                    16500 => 27408,
                    17000 => 27996,
                    17500 => 28584,
                    18000 => 29172,
                    18500 => 29760,
                    19000 => 30348,
                    19500 => 30936,
                    20000 => 31524,
                    20500 => 32112,
                    21000 => 32700,
                    21500 => 33288,
                    22000 => 33876,
                    22500 => 34464,
                    23000 => 35052,
                    23500 => 35640,
                    24000 => 36228,
                    24500 => 36816,
                    25000 => 37404,
                    25500 => 37992,
                    26000 => 38580,
                    26500 => 39168,
                    27000 => 39756,
                    27500 => 40344,
                    28000 => 40932,
                    28500 => 41520,
                    29000 => 42108,
                    29500 => 42696,
                    30000 => 43284,
                ],
                '12' => [
                    500 => 8400,
                    1000 => 9498,
                    1500 => 10596,
                    2000 => 11694,
                    2500 => 12792,
                    3000 => 13704,
                    3500 => 14616,
                    4000 => 15528,
                    4500 => 16440,
                    5000 => 17352,
                    5500 => 18096,
                    6000 => 18840,
                    6500 => 19584,
                    7000 => 20328,
                    7500 => 21072,
                    8000 => 21816,
                    8500 => 22560,
                    9000 => 23304,
                    9500 => 24048,
                    10000 => 24792,
                    10500 => 25536,
                    11000 => 26280,
                    11500 => 27024,
                    12000 => 27768,
                    12500 => 28512,
                    13000 => 29256,
                    13500 => 30000,
                    14000 => 30744,
                    14500 => 31488,
                    15000 => 32232,
                    15500 => 32976,
                    16000 => 33720,
                    16500 => 34464,
                    17000 => 35208,
                    17500 => 35952,
                    18000 => 36696,
                    18500 => 37440,
                    19000 => 38184,
                    19500 => 38928,
                    20000 => 39672,
                    20500 => 40416,
                    21000 => 41160,
                    21500 => 41904,
                    22000 => 42648,
                    22500 => 43392,
                    23000 => 44136,
                    23500 => 44880,
                    24000 => 45624,
                    24500 => 46368,
                    25000 => 47112,
                    25500 => 47856,
                    26000 => 48600,
                    26500 => 49344,
                    27000 => 50088,
                    27500 => 50832,
                    28000 => 51576,
                    28500 => 52320,
                    29000 => 53064,
                    29500 => 53808,
                    30000 => 54552,
                ],
            ],
        ];

        return self::getParcelforceBoxPricing($bands, $countryCode, [
            'maximumInclusiveCompensation' => 200,
            'maximumTotalCover' => 2500,
        ]);
    }
}

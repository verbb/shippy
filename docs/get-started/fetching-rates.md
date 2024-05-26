# Fetching Rates
Fetching rates from a carrier involves a multi-step process:

1. Create a [Shipment](docs:models/shipment) model that stores the from/to address for rates.
1. Create one or more [Package](docs:models/package) models to represent the things you want to ship.
1. Add one or more [Carrier](docs:models/carrier) models to fetch rates for.

With that in mind, let's take a look at an example.

## How to Fetch Rates
In this example, we want to send a single package and get rates for UPS and Fedex. The [Shipment](docs:models/shipment) will fetch rates from both carriers and combine them sorted by cheapest rate to most expensive.

```php
use verbb\shippy\carriers\FedEx;
use verbb\shippy\carriers\UPS;
use verbb\shippy\models\Address;
use verbb\shippy\models\Package;
use verbb\shippy\models\Shipment;

// Create a shipment to set the from/to address details
$shipment = new Shipment([
    'from' => new Address([
        'street1' => 'One Infinite Loop',
        'city' => 'Cupertino',
        'stateProvince' => 'CA',
        'postalCode' => '95014',
        'countryCode' => 'US',
    ]),

    'to' => new Address([
        'street1' => '1600 Amphitheatre Parkway',
        'city' => 'Mountain View',
        'stateProvince' => 'CA',
        'postalCode' => '94043',
        'countryCode' => 'US',
    ]),
]);

// Create a single package (or multiple) to represent what we're sending
$shipment->addPackage(new Package([
    'length' => 300,
    'width' => 100,
    'height' => 80,
    'weight' => 2000,
    'price' => 20,

    // Instruct the package on the units we're providing values for
    'dimensionUnit' => 'mm',
    'weightUnit' => 'g',
]));

// Add the carrier(s) we wish to fetch rates for
$shipment->addCarrier(new UPS([
    'isProduction' => false,
    'clientId' => '•••••••••••••••••••••••••••••••••••',
    'clientSecret' => '•••••••••••••••••••••••••••••••••••',
    'accountNumber' => '••••••',
]));

$shipment->addCarrier(new FedEx([
    'isProduction' => false,
    'clientId' => '•••••••••••••••••••••••••••••••••••',
    'clientSecret' => '•••••••••••••••••••••••••••••••••••',
    'accountNumber' => '••••••',
]));

// Fetch the rates and print the response
$rateResponse = $shipment->getRates();

echo '<pre>';
print_r($rateResponse);
echo '</pre>';
```

The above will return a [RateResponse](docs:models/rate-response) model, which will look similar to the following:

```html
verbb\shippy\models\RateResponse Object
(
    [rates] => Array
        (
            [0] => verbb\shippy\models\Rate Object
                (
                    [serviceName] => FedEx Ground
                    [serviceCode] => FEDEX_GROUND
                    [rate] => 13.81
                    [currency] => USD
                    [deliveryDays] => 
                    [deliveryDate] => DateTime Object
                        (
                            [date] => 2023-08-22 23:59:00.000000
                            [timezone_type] => 3
                            [timezone] => UTC
                        )

                    [deliveryDateGuaranteed] => 
                )

            [1] => verbb\shippy\models\Rate Object
                (
                    [serviceName] => FedEx Express Saver
                    [serviceCode] => FEDEX_EXPRESS_SAVER
                    [rate] => 27.61
                    [currency] => USD
                    [deliveryDays] => 
                    [deliveryDate] => DateTime Object
                        (
                            [date] => 2023-08-23 17:00:00.000000
                            [timezone_type] => 3
                            [timezone] => UTC
                        )

                    [deliveryDateGuaranteed] => 
                )

        )

    [response] => Array
        (
            [UPS] => Array
                (
                    [response] => Array
                        (
                            ...
                        )

                )

            [FedEx] => Array
                (
                    [transactionId] => 2b029081-c892-40ef-8da7-8d200a5213fc
                    [output] => Array
                        (
                            [rateReplyDetails] => Array
                                (
                                    ...
                                )
                        )

                )

        )

    [errors] => Array
        (
        )
)
```

The [RateResponse](docs:models/rate-response) model contains the `rates` we're after, the raw `response` from the carrier's API (if we need it) and any `errors` encountered.

Looping through `rates` is a collection of [Rate](docs:models/rate) models with the service name, code, delivery information and the all-important `rate` cost. These are normalised into a consistent object so you can better deal with them in your code.

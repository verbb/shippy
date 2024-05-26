# Creating Labels
Creating labels from a carrier involves a multi-step process:

1. Create a [Shipment](docs:models/shipment) model that stores the from/to address for rates.
1. Create one or more [Package](docs:models/package) models to represent the things you want to ship.
1. Add one or more [Carrier](docs:models/carrier) models to fetch rates for.
1. Select the [Rate](docs:models/rate) that you want to use to create a shipment for.
1. Generate [Label](docs:models/label) models.

With that in mind, let's take a look at an example.

## How to Create Labels
In this example, we want to send a single package and get rates for UPS. The [Shipment](docs:models/shipment) will fetch rates from both carriers and combine them sorted by cheapest rate to most expensive. We'll then take the first rate and use that to create a shipment label.

```php
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

// Fetch the rates and print the response
$rateResponse = $shipment->getRates();

// Fetch just the first rate (your own logic would handle picking the one you want)
$rate = $rateResponse->getRates()[0] ?? null;

if ($rate) {
    // Crate the labels for the rate
    $labelResponse = $shipment->getLabels($rate);

    echo '<pre>';
    print_r($labelResponse);
    echo '</pre>';
}
```

The above will return a [LabelResponse](docs:models/label-response) model, which will look similar to the following:

```html
verbb\shippy\models\LabelResponse Object
(
    [labels] => Array
        (
            [0] => verbb\shippy\models\Label Object
                (
                    [rate] => verbb\shippy\models\Rate Object
                        (
                            [serviceName] => Express Post
                            [serviceCode] => EXPRESS_POST
                            [rate] => 14.25
                            [currency] => 
                            [deliveryDays] => 
                            [deliveryDate] => 
                            [deliveryDateGuaranteed] => 
                        )

                    [trackingNumber] => ••••••••••••
                    [labelId] => 9b4afc22-26af-48e6-be54-46b19a588d9e
                    [labelData] => JVBERi0xLjQKJeLjz9MKMiAwIG9iago8PC9GaWx0ZXI...
                )

        )

    [response] => Array
        (
            [labels] => Array
                (
                    [0] => Array
                        (
                            // ...
                        )

                )

        )

    [errors] => Array
        (
        )

)
```

The [LabelResponse](docs:models/label-response) model contains the `labels` we're after, the raw `response` from the carrier's API (if we need it) and any `errors` encountered.

Looping through `labels` is a collection of [Label](docs:models/label) models with the tracking number and the all-important `labelData` value, representing the base64-encoded string of the label image. These are normalised into a consistent object so you can better deal with them in your code.

## How to Create Labels with a Rate
If you already know the `serviceCode` for a rate, you can skip fetching the rates altogether.

```php
use verbb\shippy\carriers\UPS;
use verbb\shippy\models\Package;
use verbb\shippy\models\Rate;
use verbb\shippy\models\Shipment;

$shipment = new Shipment([
    // ...
]);

$shipment->addPackage(new Package([
    // ...
]));

$carrier = new UPS([
    // ...
]);

$shipment->addCarrier($carrier);

$rate = new Rate([
    'carrier' => $carrier,
    'serviceCode' => 'EXPRESS_POST',
]);

$labelResponse = $shipment->getLabels($rate);
```

The difference here is constructing a [Rate](docs:models/rate) model manually. The only requirement is to set the `carrier` and `serviceCode` values, which are passed to the carrier API.

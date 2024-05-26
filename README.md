<p align="center"><img src="https://verbb.imgix.net/plugins/shippy/shippy-icon.svg" width="100" height="100" alt="Cloner icon"></p>
<h1 align="center">Shippy</h1>

Shippy is a framework agnostic, multi-carrier shipping library for PHP. Its aim is to provide a consistent API around the many different shipping carriers for shipment rates, tracking, labels and more. It's free to use and doesn't require a subscription to SaaS providers.

## Install
You can install the package via composer:

```shell
composer require verbb/shippy
```

## Usage
As a quick example, use the following to fetch rates from UPS and FedEx.

```php
use verbb\shippy\carriers\FedEx;
use verbb\shippy\carriers\UPS;
use verbb\shippy\models\Address;
use verbb\shippy\models\Package;
use verbb\shippy\models\Shipment;

// Create a shipment to set the from/to address details
$shipment = new Shipment([
    // You can supply config arrays for quick setting.
    'from' => new Address([
        'street1' => 'One Infinite Loop',
        'city' => 'Cupertino',
        'stateProvince' => 'CA',
        'postalCode' => '95014',
        'countryCode' => 'US',
    ]),
]);

// You can use traditional setters if you prefer
$toAddress = new Address();
$toAddress->setStreet1('1600 Amphitheatre Parkway');
$toAddress->setCity('Mountain View');
$toAddress->setStateProvince('CA');
$toAddress->setPostalCode('94043');
$toAddress->setCountryCode('US');
$shipment->setTo($toAddress);

// Create a package (or more) to represent what we're sending
// You can use fluent syntax if you prefer
$package = new Package()
    ->setLength(300)
    ->setWidth(100)
    ->setHeight(80)
    ->setWeight(2000)
    ->setDimensionUnit('mm')
    ->setWeightUnit('g');

$shipment->addPackage($package);

// Finally, add the carrier(s) we wish to fetch rates for. With multiple carriers, rates will be
// returned across all, sorted by cheapest to most expensive
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

Be sure to check out the full [documentation](https://verbb.io/packages/shippy).

## Supported Carriers
Shippy supports the following carriers and features.

| Carrier | Rates | Tracking | Labels |
| ------------------- | :---: | :---: | :---: |
| Aramex              | ☑️ | ☑️ |    |
| Aramex Australia    | ☑️ | ☑️ | ☑️ |
| Australia Post      | ☑️ | ☑️ | ☑️ |
| Bring               | ☑️ | ☑️ | ☑️ |
| Canada Post         | ☑️ | ☑️ | ☑️ |
| Colissimo           | ☑️ |    |    |
| DHL Express         | ☑️ | ☑️ | ☑️ |
| Fastway             | ☑️ | ☑️ |    |
| FedEx               | ☑️ | ☑️ | ☑️ |
| FedEx Freight       | ☑️ | ☑️ | ☑️ |
| Interparcel         | ☑️ | ☑️ | ☑️ |
| New Zealand Post    | ☑️ | ☑️ | ☑️ |
| PostNL              | ☑️ |    |    |
| Royal Mail          | ☑️ | ☑️ | ☑️ |
| Sendle              | ☑️ | ☑️ | ☑️ |
| TNT Australia       | ☑️ |    |    |
| UPS                 | ☑️ | ☑️ | ☑️ |
| UPS Freight         | ☑️ | ☑️ | ☑️ |
| USPS                | ☑️ | ☑️ | ☑️ |

### New Carriers
We'd love to grow the package to support as many carriers as we can. You can either:
- Request a [new carrier support](https://github.com/verbb/shippy/issues), and we'll build it! We might need your API credentials to verify everything.
- Use the [documentation](https://verbb.io/packages/shippy) to create your own (and we can list it here as a community carrier).
- [Get in touch](mailto:support@verbb.io) to arrange priority, sponsored development. 

## Documentation
Visit the [Shippy](https://verbb.io/packages/shippy) documentation.

## Support
For all feature requests, bugs and questions, [create a Github issue](https://github.com/verbb/shippy/issues) here.

Shippy is actively maintained via [Postie](https://verbb.io/craft-plugins/postie), a commercial [Craft CMS](https://craftcms.com/) plugin.

## Sponsor
Shippy is MIT licensed, meaning it will always be free and open source – we love free stuff! If you'd like to show your support for the package, [Sponsor](https://github.com/sponsors/verbb) development. We'd _highly_ encourage this if you use this package for commercial purposes.

<h2></h2>

<a href="https://verbb.io" target="_blank">
    <img width="100" src="https://verbb.io/assets/img/verbb-pill.svg">
</a>

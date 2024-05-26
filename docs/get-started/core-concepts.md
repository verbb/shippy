# Core Concepts

## A unified API
Shipping carriers come in all shapes and sizes. Some use JSON, XML or even SOAP for data. Some have simple authentication, others use OAuth. But every carrier will require the payloads sent to their APIs to be formatted in a specific way — likewise, their responses back will be unique.

The goal of Shippy is to provide a unified API that abstracts away having to deal with multiple carrier APIs, or their API's themselves as they change with time. To achieve this, Shippy provides a range of classes to represent some of the consistent data that are common across tasks like fetching rates and printing labels.

## Models
Shippy supplies several object classes to abstract things. Most notable are:

### Rates
- [Address](docs:models/address)
- [Shipment](docs:models/shipment)
- [Package](docs:models/package)
- [Rate](docs:models/rate)

### Tracking
- [Tracking](docs:models/tracking)

### Labels
- [Shipment](docs:models/shipment)
- [Label](docs:models/label)

See the [Models](docs:models) section for a full list of models.

Any Shippy model can be initialized in the following ways — depending on your preference. In all cases, you're free to mix and match, which might be beneficial depending on the logic of your app.

### Array-based Syntax
Classes can be created by passing an array into the constructor. For example:

```php
use verbb\shippy\models\Address;

new Address([
    'street1' => 'One Infinite Loop',
    'city' => 'Cupertino',
    'stateProvince' => 'CA',
    'postalCode' => '95014',
    'countryCode' => 'US',
]);
```

### Fluent-based Syntax
Classes can be created, and then configured by chaining setter methods. For example:

```php
use verbb\shippy\models\Address;

$toAddress = new Address()
    ->setStreet1('One Infinite Loop')
    ->setCity('Cupertino')
    ->setStateProvince('CA')
    ->setPostalCode('95014')
    ->setCountryCode('US');
```

### Method-based Syntax
Classes can be created using the "usual" approach with non-chained setter methods. For example:

```php
use verbb\shippy\models\Address;

$toAddress = new Address();
$toAddress->setStreet1('One Infinite Loop');
$toAddress->setCity('Cupertino');
$toAddress->setStateProvince('CA');
$toAddress->setPostalCode('95014');
$toAddress->setCountryCode('US');
```

### Hybrid-based Syntax
You're able to mix and match these syntaxes as you see fit.

```php
use verbb\shippy\models\Package;

$package = new Package([
    'length' => 300,
    'width' => 100,
    'height' => 80,
    'weight' => 2000,
]);

if ($isPriced) {
    $package->setPrice(20);
}

if ($isMetric) {
    $package
        ->setDimensionUnit('mm')
        ->setWeightUnit('g');
}
```

## Packages
[Package](docs:models/package) models define the items we want to fetch rates for or generate labels for. They have defined dimensions and weight values.

Alongside these values, we also define the units used. Shippy will handle all the converting for the carrier.

For example, USPS as a carrier uses pounds (`lbs`) and inches (`in`) for units, and their API requires values to be sent in those units. But you might like to define your packages in kilograms (`kg`) and millimetres (`mm`). Just be sure to set the appropriate units for what you _provide_ and Shippy and the carrier implementation will take care of the rest.

```php
use verbb\shippy\models\Package;

$package = new Package([
    'length' => 3000,
    'width' => 1000,
    'height' => 800,
    'weight' => 2,
    'dimensionUnit' => 'mm',
    'weightUnit' => 'kg',
]);
```
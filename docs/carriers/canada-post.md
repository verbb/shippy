# Canada Post
Shippy provides the following feature support for Canada Post.

- Rates
- Tracking
- Labels

## API Credentials
To use Canada Post, you'll need to connect to their API. 

1. Go to <a href="https://www.canadapost.ca/cpo/mc/business/productsservices/developers/services/gettingstarted.jsf" target="_blank">Canada Post Developers Centre</a> and register for API access.
1. Copy the **Customer Number** from Canada Post as the `customerNumber` with the Shippy carrier.
1. Copy the **Username** from Canada Post as the `username` with the Shippy carrier.
1. Copy the **Password** from Canada Post as the `password` with the Shippy carrier.
1. Copy the **Contract ID** from Canada Post as the `contractId` with the Shippy carrier.

To create labels, you'll be required to supply a few more details.

```php
use verbb\shippy\carriers\Canada Post;

new Canada Post([
    'isProduction' => false,
    'username' => '••••••••••••••••',
    'password' => '••••••••••••••••',
    'customerNumber' => '•••••••••••••••••••••••••••••••••••',

    // Required for labels
    'contractId' => '•••••••••••••••••••••••••••••••••••',
]);
```

## Label Formats
Canada Post creates labels as PDFs by default. You can request ZPL II output by passing its print preferences to the shipment's `getLabels()` method.

```php
$labelResponse = $shipment->getLabels($rate, [
    'encoding' => 'ZPL',
    'outputFormat' => '4x6',
]);
```

Canada Post supports `PDF` and `ZPL` encodings, with `8.5x11` and `4x6` output formats. ZPL requires the `4x6` output format, which Shippy selects automatically if `outputFormat` is omitted.

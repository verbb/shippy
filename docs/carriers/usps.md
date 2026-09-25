# USPS
Shippy provides the following feature support for USPS.

- Rates
- Tracking
- Labels

## API Credentials
To use USPS, you'll need to connect to their API. 

1. Go to <a href="https://developer.usps.com/apis" target="_blank">USPS</a> and login to your account.
1. From the **Apps** section, follow the prompts to create a new app.
1. Copy the **Consumer Key** from USPS as the `clientId` with the Shippy carrier.
1. Copy the **Consumer Secret** from USPS as the `clientSecret` with the Shippy carrier.

To create labels, you'll be required to supply a few more details.

```php
use verbb\shippy\carriers\USPS;

new USPS([
    'isProduction' => false,
    'clientId' => '••••••••••••••••',
    'clientSecret' => '••••••••••••••••',
    'accountNumber' => '••••••••••••••••',

    // Required for labels
    'customerRegistrationId' => '••••••••••••••••',
    'mailerId' => '••••••••••••••••',
]);
```

## Label Formats
USPS creates labels as PDFs by default. You can request another output type by passing `labelFormat` to the shipment's `getLabels()` method.

```php
$labelResponse = $shipment->getLabels($rate, [
    'labelFormat' => 'ZPL203DPI',
    'labelType' => '4X6LABEL',
]);
```

USPS supports raster, vector, and printer-command formats including `PDF`, `TIFF`, `JPG`, `SVG`, `ZPL203DPI`, and `ZPL300DPI`. Availability can depend on the label endpoint, mail class, and USPS account permissions.

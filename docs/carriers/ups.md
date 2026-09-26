# UPS
Shippy provides the following feature support for UPS.

- Rates
- Tracking
- Labels

## API Credentials
To use UPS, you'll need to connect to their API. 

1. Go to <a href="https://developer.ups.com" target="_blank">UPS</a> and login to your account.
1. From the **Apps** section, follow the prompts to create a new app.
1. Copy the **Client ID** from UPS as the `username` with the Shippy carrier.
1. Copy the **Client Secret** from UPS as the `apiKey` with the Shippy carrier.

To create labels, you'll be required to supply a few more details.

```php
use verbb\shippy\carriers\UPS;

new UPS([
    'isProduction' => false,
    'clientId' => '••••••••••••••••',
    'clientSecret' => '••••••••••••••••',

    // Required for labels
    'accountNumber' => '••••••••••••••••',
]);
```

## Label Formats
UPS creates labels as GIF images by default. You can request another output type with the carrier-independent label options. Shippy selects 4 × 6 stock for printer-command formats automatically.

```php
$labelResponse = $shipment->getLabels($rate, [
    'format' => 'zpl',
]);
```

UPS supports `GIF`, `ZPL`, `EPL`, and `SPL`. UPS uses the `EPL` code for EPL2 output.

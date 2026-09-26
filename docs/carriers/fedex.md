# FedEx
Shippy provides the following feature support for FedEx.

- Rates
- Tracking
- Labels

## API Credentials
To use FedEx, you'll need to connect to their API. 

1. Go to <a href="https://developer.fedex.com/api/en-us/home.html" target="_blank">FedEx</a> and login to your account.
1. Follow the <a href="https://developer.fedex.com/api/en-us/get-started.html" target="_blank">Get Started</a> guide to create a **Project**.
1. Copy the **API Key** from FedEx as the `clientId` with the Shippy carrier.
1. Copy the **Secret Key** from FedEx as the `clientSecret` with the Shippy carrier.
1. Copy the **Shipping Account** from FedEx as the `accountNumber` with the Shippy carrier.

```php
use verbb\shippy\carriers\FedEx;

new FedEx([
    'isProduction' => false,
    'clientId' => '••••••••••••••••',
    'clientSecret' => '•••••••••••••••••••••••••••••••••••',
    'accountNumber' => '••••••••••',
]);
```

## Label Formats
FedEx creates labels as PDFs by default. You can request another output type with the carrier-independent label options. Shippy selects a 4 × 6 stock type for thermal formats automatically.

```php
$labelResponse = $shipment->getLabels($rate, [
    'format' => 'zpl',
]);
```

FedEx supports `PDF`, `PNG`, `ZPLII`, and `EPL2`. Available label stock types and format combinations can depend on the selected service.

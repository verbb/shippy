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

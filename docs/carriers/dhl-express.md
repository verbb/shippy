# DHL Express
Shippy provides the following feature support for DHL Express.

- Rates
- Tracking
- Labels

## API Credentials
To use DHL Express, you'll need to connect to their API. 

1. Go to <a href="https://developer.dhl.com/api-catalog/" target="_blank">DHL Express</a> and login to your account.
1. From the **Apps** section, follow the prompts to create a new app.
1. From the list of available APIs, select (as appropriate)
    - **DHL Express - MyDHL API**
    - **Shipment Tracking - Unified**
1. Copy the **API Key** from DHL Express as the `clientId` with the Shippy carrier.
1. Copy the **Username** from DHL Express as the `username` with the Shippy carrier.
1. Copy the **Password** from DHL Express as the `password` with the Shippy carrier.
1. Copy the **Account Number** from DHL Express as the `accountNumber` with the Shippy carrier.

```php
use verbb\shippy\carriers\DHLExpress;

new DHLExpress([
    'isProduction' => false,
    'clientId' => '•••••••••••••••••••••••••••••••••••',
    'username' => '••••••••••••••••',
    'password' => '••••••••••••••••',
    'accountNumber' => '••••••••••••••••',
]);
```

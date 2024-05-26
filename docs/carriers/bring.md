# Bring
Shippy provides the following feature support for Bring.

- Rates
- Tracking
- Labels

## API Credentials
To use Bring, you'll need to connect to their API. 

1. Go to <a href="https://www.mybring.com/" target="_blank">Bring</a> and login to your account.
1. From the **Dashboard** visit the **Settings and API** page and generate your API keys.
1. Copy the **Username** from Bring as the `username` with the Shippy carrier.
1. Copy the **API Key** from Bring as the `apiKey` with the Shippy carrier.

To create labels, you'll be required to supply a few more details.

```php
use verbb\shippy\carriers\Bring;

new Bring([
    'isProduction' => false,
    'username' => '••••••••••••••••',
    'apiKey' => '•••••••••••••••••••••••••••••••••••',

    // Required for labels
    'clientUrl' => 'https://verbb.io',
    'customerNumber' => '••••••••••',
]);
```

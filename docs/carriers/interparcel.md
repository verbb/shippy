# Interparcel
Shippy provides the following feature support for Interparcel.

- Rates
- Tracking

## API Credentials
To use Interparcel, you'll need to connect to their API. 

1. Go to <a href="https://au.interparcel.com/business/shipping-tools" target="_blank">Interparcel</a> and request Developer API access.
1. Once approved, you'll receive an email from the Interparcel support team.
1. Copy the **API Key** from Interparcel as the `apiKey` with the Shippy carrier.

```php
use verbb\shippy\carriers\Interparcel;

new Interparcel([
    'isProduction' => false,
    'apiKey' => '•••••••••••••••••••••••••••••••••••',
]);
```

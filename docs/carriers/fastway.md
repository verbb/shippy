# Fastway
Shippy provides the following feature support for Fastway.

- Rates
- Tracking

## API Credentials
To use Fastway, you'll need to connect to their API. 

1. Go to <a href="http://au.api.fastway.org/v2/docs/page/GetAPIKey.html" target="_blank">Fastway Developers Centre</a> and register for API access.
1. Copy the **API Key** from Fastway as the `apiKey` with the Shippy carrier.

```php
use verbb\shippy\carriers\Fastway;

new Fastway([
    'isProduction' => false,
    'apiKey' => '•••••••••••••••••••••••••••••••••••',
]);
```

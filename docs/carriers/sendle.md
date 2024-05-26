# Sendle
Shippy provides the following feature support for Sendle.

- Rates
- Tracking
- Labels

## API Credentials
To use Sendle, you'll need to connect to their API. 

1. Go to <a href="https://www.sendle.com/#signup-form" target="_blank">Sendle</a> and login to your account.
1. You might prefer to create a <a href="https://sandbox.sendle.com/#signup-form" target="_blank">Sandbox Sendle account</a> for testing.
1. From the **Dashboard** visit the **Settings** tab from the sidebar. Click on the **Integrations** tab.
1. Copy the **Sendle ID** from Sendle as the `sendleId` with the Shippy carrier.
1. Copy the **API Key** from Sendle as the `apiKey` with the Shippy carrier.

```php
use verbb\shippy\carriers\Sendle;

new Sendle([
    'isProduction' => false,
    'sendleId' => '••••••••••••••••',
    'apiKey' => '•••••••••••••••••••••••••••••••••••',
]);
```

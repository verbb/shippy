# Royal Mail
Shippy provides the following feature support for Royal Mail.

- Rates
- Tracking
- Labels (via [Click & Drop](https://www.royalmail.com/business/shipping/click-and-drop))

Royal Mail do not offer live rates via their API. Prices are calculated from built-in Royal Mail price tables (currently through April 2026).

## API Credentials
To use Royal Mail tracking, you'll need to connect to their developer API.

1. Go to <a href="https://developer.royalmail.net/api" target="_blank">Royal Mail</a> and login to your account.
1. From the **My Apps** section, follow the prompts to create a new app.
1. Copy the **API Key** from Royal Mail as the `clientId` with the Shippy carrier.
1. Copy the **API Secret** from Royal Mail as the `clientSecret` with the Shippy carrier.

```php
use verbb\shippy\carriers\RoyalMail;

new RoyalMail([
    'isProduction' => false,
    'clientId' => '••••••••••••••••',
    'clientSecret' => '••••••••••••••••',
]);
```

## Click & Drop Labels
To create shipping labels via Click & Drop, set `useClickAndDropLabels` to `true` and provide your Click & Drop API key.

1. Log in to <a href="https://business.parcel.royalmail.com/" target="_blank">Royal Mail Click & Drop</a>.
1. Generate an API key from your Click & Drop account settings.
1. Pass it as `clickAndDropApiKey`.

```php
use verbb\shippy\carriers\RoyalMail;

new RoyalMail([
    'isProduction' => true,
    'clientId' => '••••••••••••••••',
    'clientSecret' => '••••••••••••••••',
    'useClickAndDropLabels' => true,
    'clickAndDropApiKey' => '••••••••••••••••',
]);
```

# Royal Mail
Shippy provides the following feature support for Royal Mail.

- Rates
- Tracking
- Labels

Royal Mail do not offer live rates via their API. Prices according to the [2023 price guide](https://www.royalmail.com/sites/royalmail.com/files/2023-03/royal-mail-our-prices-april-2023-ta.pdf).

## API Credentials
To use Royal Mail, you'll need to connect to their API. 

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

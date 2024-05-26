# Australia Post
Shippy provides the following feature support for Australia Post.

- Rates
- Tracking
- Labels

## API Credentials
To use Australia Post, you'll need to connect to their API. There are two different APIs to pick from, depending on your requirements.

### Postage Assessment Calculator (Rates only)
If you want to just fetch rates for shipments, you can use the Postage Assessment Calculator (PAC) API.

1. Go to <a href="https://developers.auspost.com.au/apis/pacpcs-registration" target="_blank">Australia Post Developers website</a> and register for an API Key.
1. Use the **API Key** from Australia Post as the `apiKey` with the Shippy carrier.

```php
use verbb\shippy\carriers\AustraliaPost;

new AustraliaPost([
    'isProduction' => false,
    'apiKey' => '•••••••••••••••••••••••••••••••••••',
]);
```

### Shipping and Tracking (All)
A more involved API that handles all features. You will be required to have an Australia Post account.

1. Go to <a href="https://developers.auspost.com.au/apis/st-registration" target="_blank">Australia Post Developers website</a> and register for an API Key.
1. Provide your Australia Post (eParcel) account number and complete the registration process.
1. Use the **API Key** from Australia Post as the `apiKey` with the Shippy carrier.
1. Use the **Password** from Australia Post as the `password` with the Shippy carrier.
1. Use the **Account Number** from Australia Post as the `accountNumber` with the Shippy carrier.

```php
use verbb\shippy\carriers\AustraliaPost;

new AustraliaPost([
    'isProduction' => false,
    'apiKey' => '•••••••••••••••••••••••••••••••••••',
    'password' => '•••••••••••••••',
    'accountNumber' => '•••••••••',
]);
```
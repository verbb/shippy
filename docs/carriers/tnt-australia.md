# TNT Australia
Shippy provides the following feature support for TNT Australia.

- Rates

## API Credentials
To use TNT Australia, you'll need to connect to their API. 

1. Go to <a href="https://www.tnt.com/express/en_au/site/shipping-tools.html" target="_blank">TNT Australia Shipping Tools</a> and register for API access.
1. Copy the **Account Number** from TNT Australia as the `accountNumber` with the Shippy carrier.
1. Copy the **Username** from TNT Australia as the `username` with the Shippy carrier.
1. Copy the **Password** from TNT Australia as the `password` with the Shippy carrier.

```php
use verbb\shippy\carriers\TNTAustralia;

new TNTAustralia([
    'isProduction' => false,
    'accountNumber' => '•••••••••••••••••••••••••••••••••••',
    'username' => '••••••••••••••••',
    'password' => '••••••••••••••••',
]);
```

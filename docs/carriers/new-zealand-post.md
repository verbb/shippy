# New Zealand Post
Shippy provides the following feature support for New Zealand Post.

- Rates
- Tracking
- Labels

## API Credentials
To use New Zealand Post, you'll need to connect to their API. 

1. Go to <a href="https://www.nzpost.co.nz/" target="_blank">New Zealand Post</a> and login to your account. Complete the <a href="https://www.nzpost.co.nz/user/developer-centre/register/commercial/shipping" target="_blank">commercial access form</a>.
1. Request API access for an <a href="https://www.nzpost.co.nz/business/developer-centre" target="_blank">application</a>.
1. Once access have been granted, click “add a new application”.
1. Include OAuth 2.0 grant type "Client Credentials Grant".
1. Navigate to <a href="https://anypoint.mulesoft.com/exchange/portals/nz-post-group/applications/
" target="_blank">your application</a>.
1. Copy the **Client ID** from New Zealand Post as the `clientId` with the Shippy carrier.
1. Copy the **Client Secret** from New Zealand Post as the `clientSecret` with the Shippy carrier.

```php
use verbb\shippy\carriers\NewZealandPost;

new NewZealandPost([
    'isProduction' => false,
    'clientId' => '••••••••••••••••',
    'clientSecret' => '•••••••••••••••••••••••••••••••••••',
]);
```

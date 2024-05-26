# HTTP Client
The HTTP Client that Shippy uses is a thin later over [Guzzle](https://docs.guzzlephp.org) and is used to perform API requests and handle API responses. Each carrier should perform requests using this client.

It's also possible to provide your own Guzzle-based HTTP client for Shippy to use.
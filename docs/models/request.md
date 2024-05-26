# Request
A Request is a generic model for making a HTTP request for a given [HTTP Client](docs:models/http-client). Whenever requests need to be made to carrier APIs, it should be through a Request model. As such, it supports native [Guzzle](https://docs.guzzlephp.org/en/stable/request-options.html) requests parameters.

## Properties
Properties are `protected` and can be accessed with their `getPropertyName()` getter method or set via `setPropertyName(value)` setter method.

| Property          | Type              | Description
| ----------------- | ----------------- | --------------------------------- |
| `httpClient`      | `HttpClient`      | The [HTTP Client](docs:models/http-client) the request should use.
| `method`          | `string`          | The HTTP method to use. Default to `POST`.
| `endpoint`        | `string`          | The relative endpoint to request to.
| `payload`         | `array`           | The payload of data to send.

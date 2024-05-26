# Label Response
The Label Response model represents the response from a shipment-creation request.

## Properties
Properties are `protected` and can be accessed with their `getPropertyName()` getter method or set via `setPropertyName(value)` setter method.

| Property              | Type          | Description
| --------------------- | ------------- | --------------------------------- |
| `content`             | `string`      | The body content of the response. Typically a JSON string, but this depends entirely on the carriers API.
| `response`            | `mixed`       | The raw response from the carrier API.
| `statusCode`          | `?int`        | The HTTP status code for the response.
| `errorMessage`        | `string`      | The error message for the response, if applicable.
| `labels`              | `array`       | A collection of [Label](docs:models/label) models.

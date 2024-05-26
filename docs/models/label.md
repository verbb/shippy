# Label
When a [Shipment](docs:models/shipment) is created and lodged, a Label model will be created.

## Properties
Properties are `protected` and can be accessed with their `getPropertyName()` getter method or set via `setPropertyName(value)` setter method.

| Property              | Type                  | Description
| --------------------- | --------------------- | --------------------------------- |
| `carrier`             | `CarrierInterface`    | The carrier associated with the label.
| `rate`                | `Rate`                | The [Rate](docs:models/rate) model associated with the label.
| `trackingNumber`      | `?string`             | The tracking number the label is for.
| `labelId`             | `?string`             | The carrier label ID.
| `labelData`           | `?string`             | The raw data for the label. This is typically a `base64encoded` string representing a PDF, GIF or PNG for the actual label image.
| `labelMime`           | `?string`             | The mime-type for the label data, which denotes the type of file the data is encoded for.
| `response`            | `array`               | The raw response from the carrier for the consignment.


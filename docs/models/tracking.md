# Tracking
A Tracking model represents a singular tracking update from a carrier on a shipment.

## Properties
Properties are `protected` and can be accessed with their `getPropertyName()` getter method or set via `setPropertyName(value)` setter method.

| Property                  | Type                  | Description
| ------------------------- | --------------------- | --------------------------------- |
| `carrier`                 | `CarrierInterface`    | The carrier associated with the tracking.
| `trackingNumber`          | `?string`             | The tracking number for the shipment.
| `status`                  | `?string`             | The carrier status for tracking.
| `statusDetail`            | `?string`             | The carrier status in detail for tracking.
| `estimatedDelivery`       | `?DateTime`           | The estimated delivery date for the shipment.
| `signedBy`                | `?string`             | Who the parcel was signed by upon delivery (carrier support).
| `weight`                  | `?string`             | The weight of the shipment (carrier support).
| `weightUnit`              | `?string`             | The weight unit of the shipment (carrier support).
| `details`                 | `array`               | A collection of [Tracking Detail](docs:models/tracking-detail) models.
| `errors`                  | `array`               | A collection of any errors encountered for tracking.
| `response`                | `array`               | The raw response from the carrier API for the rate response.

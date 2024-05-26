# Rate
A Rate model represents the estimated cost to ship a shipment from an origin address to a destination address.

## Properties
Properties are `protected` and can be accessed with their `getPropertyName()` getter method or set via `setPropertyName(value)` setter method.

| Property                  | Type                  | Description
| ------------------------- | --------------------- | --------------------------------- |
| `carrier`                 | `CarrierInterface`    | The carrier associated with the rate.
| `serviceName`             | `?string`             | The name of the carrier service this rate is for.
| `serviceCode`             | `?string`             | The code or identifier of the carrier service this rate is for.
| `rate`                    | `?string`             | The amount for the rate.
| `currency`                | `?string`             | The currency code for the rate.
| `deliveryDays`            | `?int`                | The number of delivery days estimated (carrier support).
| `deliveryDate`            | `?DateTime`           | The date for estimated delivery (carrier support).
| `deliveryDateGuaranteed`  | `?bool`               | Whether the delivery date is guaranteed (carrier support).
| `response`                | `array`               | The raw response from the carrier API for the rate response.

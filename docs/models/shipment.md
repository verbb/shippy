# Shipment
A Shipment model represents the top-level model that you work with from start to end.

A shipment is assigned a origin and destination [Address](docs:models/address) and one or more [Package](docs:models/package) models. You also assign it one or more Carriers to fetch rates for.

Once rates have been fetched, you can also generate labels for a shipment, which would be lodged with the carrier for pickup and dispatch.

## Properties
Properties are `protected` and can be accessed with their `getPropertyName()` getter method or set via `setPropertyName(value)` setter method.

| Property          | Type              | Description
| ----------------- | ----------------- | --------------------------------- |
| `from`            | `Address`         | The [Address](docs:models/address) model for the origin sender.
| `to`              | `Address`         | The [Address](docs:models/address) model for the destination receiver.
| `currency`        | `string`          | The currency the shipment should be using.
| `packages`        | `array`           | A collection of [Package](docs:models/package) models for the shipment.
| `carriers`        | `array`           | A collection of Carriers to fetch rates for.

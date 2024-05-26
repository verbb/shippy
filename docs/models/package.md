# Package
A Package model is used to represent the "thing" to ship, and is sent to the carrier API. It includes dimensions, weight and units associated with these values. 

A package should represent a box, satchel or parcel with your carrier, and can likely handle multiple items within it. Shippy doesn't handle packing your items into a Package.

## Properties
Properties are `protected` and can be accessed with their `getPropertyName()` getter method or set via `setPropertyName(value)` setter method.

| Property              | Type              | Description
| --------------------- | ----------------- | --------------------------------- |
| `weight`              | `?string`         | The total weight of the package.
| `width`               | `?string`         | The total width of the package.
| `length`              | `?string`         | The total length of the package.
| `height`              | `?string`         | The total height of the package.
| `price`               | `?string`         | The price for the contents of the package, used for insurance and customs.
| `weightUnit`          | `string`          | The weight unit. Default to `g`.
| `dimensionUnit`       | `string`          | The dimension unit. Default to `mm`.
| `packageType`         | `?string`         | For carriers that need a "type".
| `packagePreset`       | `?string`         | For carriers that support a pre-defined package name or service.
| `reference`           | `?string`         | For carriers that support storing a reference against a package.
| `description`         | `?string`         | For carriers that support storing a description against a package.
| `isDocument`          | `bool`            | Whether the package should be classified as documents, for the carriers that support this definition.

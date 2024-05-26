# Address
An Address model is used to represent a location for both the sender and delivery of a shipment. It includes the physical address, name, email and phone.

## Properties
Properties are `protected` and can be accessed with their `getPropertyName()` getter method or set via `setPropertyName(value)` setter method.

| Property              | Type          | Description
| --------------------- | ------------- | --------------------------------- |
| `firstName`           | `?string`     | The first name of the sender or receiver.
| `lastName`            | `?string`     | The last name of the sender or receiver.
| `companyName`         | `?string`     | The company name of the sender or receiver.
| `email`               | `?string`     | The email of the sender or receiver.
| `phone`               | `?string`     | The phone number of the sender or receiver.
| `street1`             | `?string`     | The street address (line 1) of the sender or receiver.
| `street2`             | `?string`     | The street address (line 2) of the sender or receiver.
| `street3`             | `?string`     | The street address (line 3) of the sender or receiver.
| `city`                | `?string`     | The city of the sender or receiver.
| `postalCode`          | `?string`     | The postal or zip code of the sender or receiver.
| `countryCode`         | `?string`     | The country code of the sender or receiver.
| `stateProvince`       | `?string`     | The state or province of the sender or receiver.
| `isResidential`       | `bool`        | Whether this address is considered residential. Some providers require this definition.

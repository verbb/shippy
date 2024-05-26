# Tracking Detail
A Tracking Detail model provides further detail on an individual [Tracking](docs:models/tracking) update.

## Properties
Properties are `protected` and can be accessed with their `getPropertyName()` getter method or set via `setPropertyName(value)` setter method.

| Property              | Type              | Description
| --------------------- | ----------------- | --------------------------------- |
| `description`         | `?string`         | The description of the tracking update.
| `date`                | `?DateTime`       | The date for this tracking update.
| `location`            | `?string`         | The location marked for this tracking update.
| `status`              | `?string`         | The status code for this tracking update.
| `statusDetail`        | `?string`         | The status in detail for this tracking update.

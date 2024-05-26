## Tracking Status
Fetching the tracking status of a shipment doesn't require a [Shipment](docs:models/shipment) model. Instead, you can pick the carrier the shipment is for, and call `getTrackingStatus()` directly.

```php
use verbb\shippy\carriers\AustraliaPost;

$carrier = new AustraliaPost([
    'apiKey' => '•••••••••••••••••••••••••••••••••••',
]);

$trackingResponse = $carrier->getTrackingStatus(['•••••••••••••', '•••••••••••••']);

echo '<pre>';
print_r($trackingResponse);
echo '</pre>';
```

The above will return a [TrackingResponse](docs:models/tracking-response) model, which will look similar to the following:

```html
verbb\shippy\models\TrackingResponse Object
(
    [tracking] => Array
        (
            [0] => verbb\shippy\models\Tracking Object
                (
                    [trackingNumber] => •••••••••••••
                    [status] => delivered
                    [statusDetail] => 
                    [estimatedDelivery] => 
                    [trackingUrl] => https://auspost.com.au/mypost/beta/track/details/•••••••••••••
                    [signedBy] => Peter Sherman
                    [weight] => 1.56
                    [weightUnit] => kg
                    [details] => Array
                        (
                            [0] => verbb\shippy\models\TrackingDetail Object
                                (
                                    [description] => Delivered
                                    [date] => DateTime Object
                                        (
                                            [date] => 2023-08-20 00:09:00.000000
                                            [timezone_type] => 3
                                            [timezone] => UTC
                                        )

                                    [location] => 
                                    [status] => 
                                    [statusDetail] => 
                                )

                            [1] => verbb\shippy\models\TrackingDetail Object
                                (
                                    [description] => In Transit
                                    [date] => DateTime Object
                                        (
                                            [date] => 2023-08-20 00:10:00.000000
                                            [timezone_type] => 3
                                            [timezone] => UTC
                                        )

                                    [location] => 
                                    [status] => 
                                    [statusDetail] => 
                                )

                        )

                    [errors] => Array
                        (
                        )

                )

            [1] => verbb\shippy\models\Tracking Object
                (
                    [trackingNumber] => •••••••••••••
                    [status] => not_found
                    [statusDetail] => 
                    [estimatedDelivery] => 
                    [trackingUrl] => 
                    [signedBy] => 
                    [weight] => 
                    [weightUnit] => 
                    [details] => Array
                        (
                        )

                    [errors] => Array
                        (
                        )

                )

        )

    [response] => Array
        (
            [tracking_results] => Array
                (
                    ...
                )
        )

    [errors] => Array
        (
        )

)
```

The [TrackingResponse](docs:models/tracking-response) model contains the `rates` we're after, the raw `response` from the carrier's API (if we need it) and any `errors` encountered.

Looping through `tracking` is a collection of [Tracking](docs:models/tracking) models with the status and details. [TrackingDetail](docs:models/tracking-detail) models represent the events or stages in the journey of a shipment, if available.

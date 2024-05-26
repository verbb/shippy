# Events
Shippy raises some events when communicating with carrier APIs. This can be useful not only for knowing before or after requests are raised, but for manipulating them as well.

For example, we might like to modify something about the payload sent to carrier APIs before it's sent. Here, we can call `on()` on an instance of a carrier, and which event we'd like to be notified on.

```php
use verbb\shippy\carriers\AustraliaPost;
use verbb\shippy\events\RateEvent;

$carrier = new AustraliaPost([
    'apiKey' => '•••••••••••••••••••••••••••••••••••',
]);

$carrier->on(AustraliaPost::EVENT_BEFORE_FETCH_RATES, function(RateEvent $event) {
    // Change the endpoint for the request
    $event->getRequest()->setEndpoint('my/alternative/endpoint');

    // Change some data about the payload
    $payload = $event->getRequest()->getPayload();
    $payload['my-data'] = 'my-value';

    $event->getRequest()->setPayload($payload);
});
```

This would alter the [Request](docs:models/request) object's `endpoint` and `payload` properties to our custom values.

## Rate related events

### The `beforeFetchRates` event
The event raised before the rates are fetched from the carrier's API.

```php
use verbb\shippy\carriers\AustraliaPost;
use verbb\shippy\events\RateEvent;

$carrier = new AustraliaPost([
    'apiKey' => '•••••••••••••••••••••••••••••••••••',
]);

$carrier->on(AustraliaPost::EVENT_BEFORE_FETCH_RATES, function(RateEvent $event) {
    // ...
});
```

### The `afterFetchRates` event
The event raised after the rates are fetched from the carrier's API, and parsed by the carrier class.

```php
use verbb\shippy\carriers\AustraliaPost;
use verbb\shippy\events\RateEvent;

$carrier = new AustraliaPost([
    'apiKey' => '•••••••••••••••••••••••••••••••••••',
]);

$carrier->on(AustraliaPost::EVENT_AFTER_FETCH_RATES, function(RateEvent $event) {
    // ...
});
```

## Tracking related events

### The `beforeFetchTracking` event
The event raised before the tracking information is fetched from the carrier's API.

```php
use verbb\shippy\carriers\AustraliaPost;
use verbb\shippy\events\TrackingEvent;

$carrier = new AustraliaPost([
    'apiKey' => '•••••••••••••••••••••••••••••••••••',
]);

$carrier->on(AustraliaPost::EVENT_BEFORE_FETCH_TRACKING, function(TrackingEvent $event) {
    // ...
});
```

### The `afterFetchTracking` event
The event raised after the tracking information is fetched from the carrier's API, and parsed by the carrier class.

```php
use verbb\shippy\carriers\AustraliaPost;
use verbb\shippy\events\TrackingEvent;

$carrier = new AustraliaPost([
    'apiKey' => '•••••••••••••••••••••••••••••••••••',
]);

$carrier->on(AustraliaPost::EVENT_AFTER_FETCH_TRACKING, function(TrackingEvent $event) {
    // ...
});
```

## Label related events

### The `beforeFetchLabels` event
The event raised before the labels are fetched from the carrier's API.

```php
use verbb\shippy\carriers\AustraliaPost;
use verbb\shippy\events\LabelEvent;

$carrier = new AustraliaPost([
    'apiKey' => '•••••••••••••••••••••••••••••••••••',
]);

$carrier->on(AustraliaPost::EVENT_BEFORE_FETCH_LABELS, function(LabelEvent $event) {
    // ...
});
```

### The `afterFetchLabels` event
The event raised after the labels are fetched from the carrier's API, and parsed by the carrier class.

```php
use verbb\shippy\carriers\AustraliaPost;
use verbb\shippy\events\LabelEvent;

$carrier = new AustraliaPost([
    'apiKey' => '•••••••••••••••••••••••••••••••••••',
]);

$carrier->on(AustraliaPost::EVENT_AFTER_FETCH_LABELS, function(LabelEvent $event) {
    // ...
});
```

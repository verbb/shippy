# Custom Carrier
Building your own carrier class will require you to implement the [CarrierInterface](docs:models/carrier-interface) and should extend the [AbstractCarrier](docs:models/abstract-carrier) class.

## Carrier Anatomy
We won't be going through every single line of code in the [AbstractCarrier](docs:models/abstract-carrier) class (that's what source code is for), but we'll walk through the major functionality and requirements to have your own carrier.

:::tip
If you're like us and are more of a hands-on learner, take a look at the [carrier classes]() themselves and tinker with. These can be extended, or used as a good guide for your own carrier classes.
:::

For our example, let's use the fictional `Wakanda Post` carrier for the rest of this guide.

### Minimum Requirements
There's a few things every carrier class should implement.

```php
use verbb\shippy\carriers\AbstractCarrier;
use verbb\shippy\models\Shipment;

class WakandaPost extends AbstractCarrier
{
    public static function getName(): string
    {
        return 'Wakanda Post';
    }

    public static function getWeightUnit(Shipment $shipment): string
    {
        return 'kg';
    }

    public static function getDimensionUnit(Shipment $shipment): string
    {
        return 'cm';
    }

    public static function getServiceCodes(): array
    {
        return [
            'PARCEL_REGULAR' => 'Parcel Post',
            'PARCEL_EXPRESS' => 'Express Post',
        ];
    }
}
```

First and foremost, give your carrier a name! Secondly, define the units of measurement and weight the carrier uses. Don't forget, Shippy will convert whatever units your [Package](docs:models/package) models uses into **these** units.

Lastly, you may wish to supply a collection of service codes your carrier supports. It's not required, but is useful if users of this class want to only include certain services.

### HTTP Client
Next, as we'll likely be talking to an API for the carrier, we'll need to supply a HTTP client. Shippy uses a [HttpClient](docs:models/http-client) class for this, which is a proxy to a [Guzzle](https://docs.guzzlephp.org/en/stable/). This HTTP client should provide at least the `baseUri` to the provider API, and any authentication headers, so it's ready to use for requests.

While we're doing this, let's assume `Wakanda Post` requires an `apiKey` header in order to authenticate. Every provider will be different.

```php
use verbb\shippy\models\HttpClient;

protected ?string $apiKey = null;

public function getApiKey(): ?string
{
    return $this->apiKey;
}

public function setApiKey(?string $apiKey): WakandaPost
{
    $this->apiKey = $apiKey;
    return $this;
}

public function getHttpClient(): HttpClient
{
    return new HttpClient([
        'base_uri' => 'https://wakandapost.wk/api/v2/',
        'headers' => [
            'API-KEY' => $this->getApiKey(),
        ],
    ]);
}
```

Here, we've created an `apiKey` property and appropriate getter/setters methods. Our `getHttpClient` returns a [HttpClient](docs:models/http-client) ready to go.

With this property, users of course carrier will be able to initialize the carrier like:

```php
new WakandaPost([
    'isProduction' => false,
    'apiKey' => '•••••••••••••••••••••••••••••••••••',
]);
```

Or through the setter methods.

```php
new WakandaPost()
    ->setIsProduction(false)
    ->setApiKey('•••••••••••••••••••••••••••••••••••');
```

### Adding Features
Onto the good stuff! Let's add some features to our carrier. In our example, `Wakanda Post` supports fetching rates, checking tracking information and creating labels. If your carrier doesn't support a certain feature, it should be implemented with an `Exception`.

```php
public function getLabels(Shipment $shipment, Rate $rate, array $options = []): ?LabelResponse
{
     throw new Exception('Not implemented.');
}
```

### Fetching Rates
In order to fetch rates, we'll do the following:

1. Validate that we've supplied at least the `apiKey` for the carrier
1. Take the details of a [Shipment](docs:models/shipment) model
1. Turn it into a [Request](docs:models/request)
1. Call `fetchRates()`, parsing the raw response
1. From the carrier API reponse, create multiple [Rate](docs:models/rate) models
1. Return a [RateResponse](docs:models/rate-response) model


```php
use Illuminate\Support\Arr;
use verbb\shippy\models\Rate;
use verbb\shippy\models\RateResponse;
use verbb\shippy\models\Request;
use verbb\shippy\models\Response;
use verbb\shippy\models\Shipment;

public function getRates(Shipment $shipment): ?RateResponse
{
    $this->validate('apiKey');

    $payload = [
        'from' => [
            'postcode' => $shipment->getFrom()->getPostalCode(),
        ],
        'to' => [
            'postcode' => $shipment->getTo()->getPostalCode(),
        ],
        'items' => array_map(function($package) {
            return [
                'length' => $package->getLength(),
                'width' => $package->getWidth(),
                'height' => $package->getHeight(),
                'weight' => $package->getWeight(),
            ];
        }, $shipment->getPackages()),
    ];

    $request = new Request([
        'method' => 'POST',
        'endpoint' => 'rates',
        'payload' => [
            'json' => $payload,
        ],
    ]);

    $data = $this->fetchRates($request, function(Response $response) {
        return $response->json();
    });

    $rates = [];

    foreach (Arr::get($data, 'services', []) as $service) {
        $rates[] = new Rate([
            'carrier' => $this,
            'response' => $service,
            'serviceName' => Arr::get($service, 'services_name', ''),
            'serviceCode' => Arr::get($service, 'services_id', ''),
            'rate' => Arr::get($service, 'price', 0),
        ]);
    }

    return new RateResponse([
        'response' => $data,
        'rates' => $rates,
    ]);
}
```

Let's step through this code. We call `$this->validate('apiKey');` to ensure that an exception is raised if attempting to run this without a valid `apiKey`. This will just test if the value is set, not the actual validity of it.

We then create a `$payload` variable to translate our [Shipment](docs:models/shipment) model to a JSON payload that's sent to the carrier. This will depend on your carrier's API requirements of course.

Next is creating a [Request](docs:models/request) model, which represents the HTTP request details such as the `method` used, the `endpoint` (relative to the HTTP Client's `baseUri`) and the `payload`. This all aligns with [Guzzle](https://docs.guzzlephp.org/en/stable/) requests.

With that `Request`, we call `fetchRates()` and in a callback, we receive a [Response](docs:models/response) model containing the raw response from the carrier API. It's the job of this callback to parse the string response from the API into an array. We happen to know `Wakanda Post` fortunately uses JSON, so we can call `$response->json()` as a shortcut for `Json::decode($response->getContent())`. Now our `$data` variable is an array of whatever was sent back from the API.

We can defensively loop through some returned data, creating [Rate](docs:models/rate) models to be returned as a [RateResponse](docs:models/rate-response).

### Tracking Status
In order to fetch tracking status, we'll do the following:

1. Validate that we've supplied at least the `apiKey` for the carrier
1. Turn provided tracking number into a [Request](docs:models/request)
1. Call `fetchTracking()`, parsing the raw response
1. From the carrier API reponse, create multiple [Tracking](docs:models/tracking) models
1. Return a [TrackingResponse](docs:models/tracking-response) model

```php
use Illuminate\Support\Arr;
use verbb\shippy\models\Request;
use verbb\shippy\models\Response;
use verbb\shippy\models\Tracking;
use verbb\shippy\models\TrackingResponse;

public function getTrackingStatus(array $trackingNumbers, array $options = []): ?TrackingResponse
{
    $this->validate('apiKey');

    $request = new Request([
        'method' => 'GET',
        'endpoint' => 'track',
        'payload' => [
            'query' => [
                'tracking_ids' => $trackingNumbers,
            ],
        ],
    ]);

    $data = $this->fetchTracking($request, function(Response $response) {
        return $response->json();
    });

    $tracking = [];

    foreach (Arr::get($data, 'tracking_results', []) as $result) {
        $trackingNumber = Arr::get($result, 'tracking_id', '');
        $statusCode = Arr::get($result, 'status', '');

        $status = $this->_mapTrackingStatus($statusCode);

        $tracking[] = new Tracking([
            'carrier' => $this,
            'response' => $result,
            'trackingNumber' => $trackingNumber,
            'status' => $status,
            'details' => array_map(function($detail) {
                return new TrackingDetail([
                    'location' => Arr::get($detail, 'location', ''),
                    'description' => Arr::get($detail, 'description', ''),
                    'date' => Arr::get($detail, 'date', ''),
                ]);
            }, Arr::get($result, 'events', [])),
        ]);

    }

    return new TrackingResponse([
        'response' => $data,
        'tracking' => $tracking,
    ]);
}

private function _mapTrackingStatus(string $status): string
{
    return match (strtolower($status)) {
        'delivered' => Tracking::STATUS_DELIVERED,
        default => Tracking::STATUS_UNKNOWN,
    };
}
```

Let's step through this code. We call `$this->validate('apiKey');` to ensure that an exception is raised if attempting to run this without a valid `apiKey`. This will just test if the value is set, not the actual validity of it.

Next is creating a [Request](docs:models/request) model, which represents the HTTP request details such as the `method` used, the `endpoint` (relative to the HTTP Client's `baseUri`) and the `payload`. This all aligns with [Guzzle](https://docs.guzzlephp.org/en/stable/) requests.

With that `Request`, we call `fetchTracking()` and in a callback, we receive a [Response](docs:models/response) model containing the raw response from the carrier API. It's the job of this callback to parse the string response from the API into an array. We happen to know `Wakanda Post` fortunately uses JSON, so we can call `$response->json()` as a shortcut for `Json::decode($response->getContent())`. Now our `$data` variable is an array of whatever was sent back from the API.

We can defensively loop through some returned data, creating [Tracking](docs:models/tracking) models to be returned as a [TrackingResponse](docs:models/tracking-response). We cover mapping the status from the provider into well-defined Shippy statuses in `_mapTrackingStatus()`, and also handle creating any [TrackingDetail](docs:models/tracking-detail) models.

### Creating Labels
In order to fetch rates, we'll do the following:

1. Validate that we've supplied at least the `apiKey` for the carrier
1. Take the details of a [Shipment](docs:models/shipment) model and a [Rate](docs:models/rate) model
1. Turn it into a [Request](docs:models/request)
1. Call `fetchLabels()`, parsing the raw response
1. From the carrier API reponse, create multiple [Label](docs:models/label) models
1. Return a [LabelResponse](docs:models/label-response) model

```php
use Illuminate\Support\Arr;
use verbb\shippy\models\Request;
use verbb\shippy\models\Response;
use verbb\shippy\models\Label;
use verbb\shippy\models\LabelResponse;
use verbb\shippy\models\Rate;
use verbb\shippy\models\Shipment;

public function getLabels(Shipment $shipment, Rate $rate, array $options = []): ?LabelResponse
{
    $this->validate('apiKey');

    $payload = [
        'from' => [
            'streetAddress' => $shipment->getFrom()->getStreet1(),
            'locality' => $shipment->getFrom()->getCity(),
            'stateOrProvince' => $shipment->getFrom()->getStateProvince(),
            'postcode' => $shipment->getFrom()->getPostalCode(),
            'country' => $shipment->getFrom()->getCountryCode(),
        ],
        'to' => [
            'streetAddress' => $shipment->getTo()->getStreet1(),
            'locality' => $shipment->getTo()->getCity(),
            'stateOrProvince' => $shipment->getTo()->getStateProvince(),
            'postcode' => $shipment->getTo()->getPostalCode(),
            'country' => $shipment->getTo()->getCountryCode(),
        ],
        'serviceCode' => $rate->getServiceCode(),
        'items' => array_map(function($package) {
            return [
                'length' => $package->getLength(),
                'width' => $package->getWidth(),
                'height' => $package->getHeight(),
                'weight' => $package->getWeight(),
            ];
        }, $shipment->getPackages()),
    ];

    $request = new Request([
        'method' => 'POST',
        'endpoint' => 'shipments',
        'payload' => [
            'json' => $payload,
        ],
    ]);

    $data = $this->fetchLabels($request, function(Response $response) {
        return $response->json();
    });

    $labels = [];

    foreach (Arr::get($data, 'labels', []) as $label) {
        $labels[] = new Label([
            'carrier' => $this,
            'response' => $label,
            'rate' => $rate,
            'trackingNumber' => Arr::get($label, 'tracking', ''),
            'labelId' => Arr::get($label, 'label_id', ''),
            'labelData' => Arr::get($label, 'label', ''),
        ]);
    }

    return new LabelResponse([
        'response' => $data,
        'labels' => $labels,
    ]);
}
```

Let's step through this code. We call `$this->validate('apiKey');` to ensure that an exception is raised if attempting to run this without a valid `apiKey`. This will just test if the value is set, not the actual validity of it.

Next is creating a [Request](docs:models/request) model, which represents the HTTP request details such as the `method` used, the `endpoint` (relative to the HTTP Client's `baseUri`) and the `payload`. This all aligns with [Guzzle](https://docs.guzzlephp.org/en/stable/) requests.

With that `Request`, we call `fetchLabels()` and in a callback, we receive a [Response](docs:models/response) model containing the raw response from the carrier API. It's the job of this callback to parse the string response from the API into an array. We happen to know `Wakanda Post` fortunately uses JSON, so we can call `$response->json()` as a shortcut for `Json::decode($response->getContent())`. Now our `$data` variable is an array of whatever was sent back from the API.

We can defensively loop through some returned data, creating [Label](docs:models/label) models to be returned as a [LabelResponse](docs:models/label-response). 

Depending on the API, you may need to handle serializing the label itself into `labelData`. For example, the API might response with the label as a content. You could handle this in the parsing logic for a response.

```php
$labelData = $this->fetchLabels($request, function(Response $response) {
    return ['label' => base64_encode($response->getContent())];
});
```

Where `$response->getContent()` might be an actual PDF of the label, instead of containing a JSON response.

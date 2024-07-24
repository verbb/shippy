<?php
namespace verbb\shippy\carriers;

use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Arr;
use Throwable;
use verbb\shippy\Shippy;
use verbb\shippy\events\LabelEvent;
use verbb\shippy\events\RateEvent;
use verbb\shippy\events\TrackingEvent;
use verbb\shippy\exceptions\InvalidRequestException;
use verbb\shippy\helpers\Json;
use verbb\shippy\models\HttpClient;
use verbb\shippy\models\LabelResponse;
use verbb\shippy\models\Model;
use verbb\shippy\models\Rate;
use verbb\shippy\models\RateResponse;
use verbb\shippy\models\Request;
use verbb\shippy\models\Response;
use verbb\shippy\models\Shipment;
use verbb\shippy\models\TrackingResponse;

abstract class AbstractCarrier extends Model implements CarrierInterface
{
    // Constants
    // =========================================================================

    public const EVENT_BEFORE_FETCH_RATES = 'beforeFetchRates';
    public const EVENT_AFTER_FETCH_RATES = 'afterFetchRates';
    public const EVENT_BEFORE_FETCH_TRACKING = 'beforeFetchTracking';
    public const EVENT_AFTER_FETCH_TRACKING = 'afterFetchTracking';
    public const EVENT_BEFORE_FETCH_LABELS = 'beforeFetchLabels';
    public const EVENT_AFTER_FETCH_LABELS = 'afterFetchLabels';


    // Static Methods
    // =========================================================================

    abstract public static function getName(): string;
    abstract public static function getWeightUnit(Shipment $shipment): string;
    abstract public static function getDimensionUnit(Shipment $shipment): string;

    public static function getServiceCodes(): array
    {
        return [];
    }

    public static function supportsRates(): bool
    {
        return true;
    }

    public static function supportsTrackingStatus(): bool
    {
        return true;
    }

    public static function supportsLabels(): bool
    {
        return true;
    }
    
    public static function getTrackingUrl(string $trackingNumber): ?string
    {
        return null;
    }


    // Properties
    // =========================================================================

    protected bool $isProduction = false;
    protected array $allowedServiceCodes = [];
    protected array $settings = [];


    // Public Methods
    // =========================================================================

    abstract public function getHttpClient(): HttpClient;
    abstract public function getRates(Shipment $shipment): ?RateResponse;
    abstract public function getTrackingStatus(array $trackingNumbers, array $options = []): ?TrackingResponse;
    abstract public function getLabels(Shipment $shipment, Rate $rate, array $options = []): ?LabelResponse;

    public function __toString(): string
    {
        return (string)static::getName();
    }

    public function isProduction(): bool
    {
        return $this->isProduction;
    }

    public function setIsProduction(bool $isProduction): AbstractCarrier
    {
        $this->isProduction = $isProduction;
        return $this;
    }

    public function getAllowedServiceCodes(): array
    {
        return $this->allowedServiceCodes;
    }

    public function setAllowedServiceCodes(array $allowedServiceCodes): AbstractCarrier
    {
        $this->allowedServiceCodes = $allowedServiceCodes;
        return $this;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }

    public function setSettings(array $settings): AbstractCarrier
    {
        $this->settings = $settings;
        return $this;
    }

    public function getSetting(string $key): mixed
    {
        return Arr::get($this->settings, $key);
    }

    public function setSetting(string $key, mixed $value): AbstractCarrier
    {
        Arr::set($this->settings, $key, $value);
        return $this;
    }

    /**
     * @throws InvalidRequestException
     */
    public function validate(...$args): void
    {
        foreach ($args as $name) {
            $value = $this->$name;

            if (!isset($value)) {
                throw new InvalidRequestException("The $name parameter is required");
            }
        }
    }

    public function beforeFetchRates(Request $request): void
    {
        $event = new RateEvent([
            'request' => $request,
        ]);

        $this->trigger(self::EVENT_BEFORE_FETCH_RATES, $event);
    }

    public function afterFetchRates(array $data): void
    {
        $event = new RateEvent([
            'data' => $data,
        ]);

        $this->trigger(self::EVENT_AFTER_FETCH_RATES, $event);
    }

    public function beforeFetchTracking(Request $request): void
    {
        $event = new TrackingEvent([
            'request' => $request,
        ]);

        $this->trigger(self::EVENT_BEFORE_FETCH_TRACKING, $event);
    }

    public function afterFetchTracking(array $data): void
    {
        $event = new TrackingEvent([
            'data' => $data,
        ]);

        $this->trigger(self::EVENT_AFTER_FETCH_TRACKING, $event);
    }

    public function beforeFetchLabels(Request $request): void
    {
        $event = new LabelEvent([
            'request' => $request,
        ]);

        $this->trigger(self::EVENT_BEFORE_FETCH_LABELS, $event);
    }

    public function afterFetchLabels(array $data): void
    {
        $event = new LabelEvent([
            'data' => $data,
        ]);

        $this->trigger(self::EVENT_AFTER_FETCH_LABELS, $event);
    }


    // Protected Methods
    // =========================================================================

    protected function fetchRates(Request $request, callable $callback): array
    {
        // Allow carriers to modify the request before it's sent
        $this->beforeFetchRates($request);

        Shippy::debug('{name} Rate Request: {endpoint}: {payload}', [
            'name' => static::getName(),
            'endpoint' => $request->getEndpoint(),
            'payload' => Json::encode($request->getPayload()),
        ]);

        // Perform the actual request to fetch the data from the carrier
        $response = $this->request($request);

        // Allow carriers to define processing of the raw data from the carrier (JSON, XML, etc).
        // It should always return an array transforming the raw data
        $data = $callback($response);

        // If there's been any Guzzle-level errors, inject those into the data as a "private" key
        if ($response->getErrorMessage()) {
            $data['__errors'][] = "{$response->getStatusCode()} {$response->getErrorMessage()}";
        }

        // Allow carriers to modify the data after it's been processed
        $this->afterFetchRates($data);

        Shippy::debug('{name} Rate Response: {response}', [
            'name' => static::getName(),
            'response' => $response->getContent(),
        ]);

        return $data;
    }

    protected function fetchTracking(Request $request, callable $callback): array
    {
        // Allow carriers to modify the request before it's sent
        $this->beforeFetchTracking($request);

        Shippy::debug('{name} Tracking Request: {endpoint}: {payload}', [
            'name' => static::getName(),
            'endpoint' => $request->getEndpoint(),
            'payload' => Json::encode($request->getPayload()),
        ]);

        // Perform the actual request to fetch the data from the carrier
        $response = $this->request($request);

        // Allow carriers to define processing of the raw data from the carrier (JSON, XML, etc).
        // It should always return an array transforming the raw data
        $data = $callback($response);

        // If there's been any Guzzle-level errors, inject those into the data as a "private" key
        if ($response->getErrorMessage()) {
            $data['__errors'][] = "{$response->getStatusCode()} {$response->getErrorMessage()}";
        }

        // Allow carriers to modify the data after it's been processed
        $this->afterFetchTracking($data);

        Shippy::debug('{name} Tracking Response: {response}', [
            'name' => static::getName(),
            'response' => $response->getContent(),
        ]);

        return $data;
    }

    protected function fetchLabels(Request $request, callable $callback): array
    {
        // Allow carriers to modify the request before it's sent
        $this->beforeFetchLabels($request);

        Shippy::debug('{name} Labels Request: {endpoint}: {payload}', [
            'name' => static::getName(),
            'endpoint' => $request->getEndpoint(),
            'payload' => Json::encode($request->getPayload()),
        ]);

        // Perform the actual request to fetch the data from the carrier
        $response = $this->request($request);

        // Allow carriers to define processing of the raw data from the carrier (JSON, XML, etc).
        // It should always return an array transforming the raw data
        $data = $callback($response);

        // If there's been any Guzzle-level errors, inject those into the data as a "private" key
        if ($response->getErrorMessage()) {
            $data['__errors'][] = "{$response->getStatusCode()} {$response->getErrorMessage()}";
        }

        // Allow carriers to modify the data after it's been processed
        $this->afterFetchLabels($data);

        Shippy::debug('{name} Labels Response: {response}', [
            'name' => static::getName(),
            'response' => $response->getContent(),
        ]);

        return $data;
    }

    protected function request(Request $request): ?Response
    {
        try {
            // Allow requests to define an HTTP client to initiate the request with.
            $httpClient = $request->getHttpClient() ?? $this->getHttpClient();

            $response = $httpClient->request($request->getMethod(), $request->getEndpoint(), $request->getPayload());

            return new Response([
                'statusCode' => 200,
                'response' => $response,
                'content' => $response->getBody()->getContents(),
            ]);
        } catch (Throwable $exception) {
            $messageText = $exception->getMessage();

            // Check for Guzzle errors, which are truncated in the exception `getMessage()`.
            if ($exception instanceof RequestException && $exception->getResponse()) {
                $messageText = (string)$exception->getResponse()->getBody();
            }

            Shippy::error('{name} Request Error: “{message}” {file}:{line}', [
                'name' => static::getName(),
                'message' => $messageText,
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            // If this has been a response error return that to the carrier
            if ($exception instanceof RequestException && $exception->getResponse()) {
                return new Response([
                    'statusCode' => $exception->getResponse()->getStatusCode(),
                    'errorMessage' => $exception->getMessage(),
                    'response' => $exception->getResponse(),
                    'content' => $messageText,
                ]);
            }

            return new Response([
                'statusCode' => 500,
                'errorMessage' => $exception->getMessage(),
            ]);
        }

        return null;
    }
}

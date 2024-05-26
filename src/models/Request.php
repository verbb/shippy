<?php
namespace verbb\shippy\models;

class Request extends Model
{
    // Properties
    // =========================================================================

    protected ?HttpClient $httpClient = null;
    protected string $method = 'POST';
    protected string $endpoint = '';
    protected array $payload = [];


    // Public Methods
    // =========================================================================

    public function getHttpClient(): ?HttpClient
    {
        return $this->httpClient;
    }

    public function setHttpClient(?HttpClient $httpClient): Request
    {
        $this->httpClient = $httpClient;
        return $this;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setMethod(string $method): Request
    {
        $this->method = $method;
        return $this;
    }

    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    public function setEndpoint(string $endpoint): Request
    {
        $this->endpoint = $endpoint;
        return $this;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function setPayload(array $payload): Request
    {
        $this->payload = $payload;
        return $this;
    }
}

<?php
namespace verbb\shippy\models;

use verbb\shippy\helpers\Json;
use verbb\shippy\helpers\Xml;

class Response extends Model
{
    // Properties
    // =========================================================================

    protected string $content = '';
    protected mixed $response = null;
    protected ?int $statusCode = null;
    protected ?string $errorMessage = null;


    // Public Methods
    // =========================================================================

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): Response
    {
        $this->content = $content;
        return $this;
    }

    public function getResponse(): mixed
    {
        return $this->response;
    }

    public function setResponse(mixed $response): Response
    {
        $this->response = $response;
        return $this;
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    public function setStatusCode(?int $statusCode): Response
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(?string $errorMessage): Response
    {
        $this->errorMessage = $errorMessage;
        return $this;
    }

    public function json(): array
    {
        return Json::decode($this->content);
    }

    public function xml(): array
    {
        return Xml::decode($this->content);
    }
}

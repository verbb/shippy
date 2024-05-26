<?php
namespace verbb\shippy\models;

use Illuminate\Support\Arr;

class ResourceResponse extends Model
{
    // Properties
    // =========================================================================

    protected array $response = [];
    protected array $errors = [];


    // Public Methods
    // =========================================================================

    public function __construct(array $config = [])
    {
        // Rip out any errors stored in the response
        if (Arr::get($config, 'response.__errors')) {
            $config['errors'] = Arr::pull($config, 'response.__errors', []);
        }

        parent::__construct($config);
    }

    public function getResponse(): array
    {
        return $this->response;
    }

    public function setResponse(array $response): ResourceResponse
    {
        $this->response = $response;
        return $this;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function setErrors(array $errors): ResourceResponse
    {
        $this->errors = $errors;
        return $this;
    }
}

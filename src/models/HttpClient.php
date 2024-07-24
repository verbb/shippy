<?php
namespace verbb\shippy\models;

use GuzzleHttp\BodySummarizer;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;

class HttpClient extends GuzzleClient
{
    public function __construct(array $config = [])
    {
        // Remove error handling truncation
        // https://github.com/guzzle/guzzle/issues/2185#issuecomment-800293420
        if (!isset($config['handler'])) {
            $stack = HandlerStack::create();
            $stack->push(Middleware::httpErrors(new BodySummarizer(99999)), 'http_errors');
            $config['handler'] = $stack;
        }

        parent::__construct($config);
    }
}
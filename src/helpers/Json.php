<?php
namespace verbb\shippy\helpers;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Throwable;
use verbb\shippy\Shippy;

class Json
{
    // Properties
    // =========================================================================

    private static JsonEncoder $_jsonEncoder;


    // Public Methods
    // =========================================================================

    public static function getJsonEncoder(): JsonEncoder
    {
        return self::$_jsonEncoder ?? (self::$_jsonEncoder = new JsonEncoder());
    }

    public static function encode(mixed $data, array $options = []): string
    {
        try {
            return self::getJsonEncoder()->encode($data, 'json', $options);
        } catch (Throwable $e) {
            Shippy::error('JSON Encode Error: “{message}” {file}:{line}', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return '';
        }
    }

    public static function decode(?string $data): array
    {
        try {
            return self::getJsonEncoder()->decode((string)$data, 'json');
        } catch (Throwable $e) {
            Shippy::error('JSON Decode Error: “{message}” {file}:{line}', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return [];
        }
    }
}
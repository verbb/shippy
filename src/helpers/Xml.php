<?php
namespace verbb\shippy\helpers;

use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Throwable;
use verbb\shippy\Shippy;

class Xml
{
    // Properties
    // =========================================================================

    private static XmlEncoder $_xmlEncoder;


    // Public Methods
    // =========================================================================

    public static function getXmlEncoder(): XmlEncoder
    {
        return self::$_xmlEncoder ?? (self::$_xmlEncoder = new XmlEncoder());
    }

    public static function encode(mixed $data, array $options = []): string
    {
        try {
            return self::getXmlEncoder()->encode($data, 'xml', $options);
        } catch (Throwable $e) {
            Shippy::error('XML Encode Error: “{message}” {file}:{line}', [
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
            return self::getXmlEncoder()->decode((string)$data, 'xml');
        } catch (Throwable $e) {
            Shippy::error('XML Decode Error: “{message}” {file}:{line}', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return [];
        }
    }
}
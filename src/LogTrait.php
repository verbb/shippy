<?php
namespace verbb\shippy;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

trait LogTrait
{
    // Properties
    // =========================================================================

    private static ?LoggerInterface $_logger = null;


    // Static Methods
    // =========================================================================

    public static function getLogger(): ?LoggerInterface
    {
        return self::$_logger ?? (self::$_logger = new NullLogger());
    }

    public static function setLogger(LoggerInterface $logger): void
    {
        self::$_logger = $logger;
    }

    public static function error(string $message, array $params = []): void
    {
        static::getLogger()->error(self::t($message, $params));
    }

    public static function warning(string $message, array $params = []): void
    {
        static::getLogger()->warning(self::t($message, $params));
    }

    public static function notice(string $message, array $params = []): void
    {
        static::getLogger()->notice(self::t($message, $params));
    }

    public static function info(string $message, array $params = []): void
    {
        static::getLogger()->info(self::t($message, $params));
    }

    public static function debug(string $message, array $params = []): void
    {
        static::getLogger()->debug(self::t($message, $params));
    }

    public static function t(string $message, array $params = []): string
    {
        $placeholders = [];

        foreach ($params as $name => $value) {
            $placeholders['{' . $name . '}'] = $value;
        }

        return ($placeholders === []) ? $message : strtr($message, $placeholders);
    }

}
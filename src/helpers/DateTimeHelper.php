<?php
namespace verbb\shippy\helpers;

use DateTime;
use Throwable;

class DateTimeHelper
{
    // Static Methods
    // =========================================================================

    public static function toDateTime(DateTime|string|null $date): ?DateTime
    {
        if (!$date) {
            return null;
        }

        if ($date instanceof DateTime) {
            return $date;
        }

        try {
            return new DateTime($date);
        } catch (Throwable $e) {
            return null;
        }
    }
}
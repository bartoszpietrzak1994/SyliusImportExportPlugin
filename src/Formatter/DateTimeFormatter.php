<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Formatter;

use DateTime;

final class DateTimeFormatter implements DateTimeFormatterInterface
{
    function toDateTime(string $dateTimeAsString): \DateTimeInterface
    {
        return DateTime::createFromFormat(self::DATE_TIME_FORMAT, $dateTimeAsString);
    }

    function toString(\DateTimeInterface $dateTime): string
    {
        return $dateTime->format(self::DATE_TIME_FORMAT);
    }
}

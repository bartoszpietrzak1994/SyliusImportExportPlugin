<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Formatter;

use DateTime;

final class DateTimeFormatter implements DateTimeFormatterInterface
{
    public function toDateTime(string $dateTimeAsString): ?\DateTimeInterface
    {
        /** @var DateTime|false $dateTime */
        $dateTime = DateTime::createFromFormat(self::DATE_TIME_FORMAT, $dateTimeAsString);

        return false !== $dateTime ? $dateTime : null;
    }

    public function toString(\DateTimeInterface $dateTime): string
    {
        return $dateTime->format(self::DATE_TIME_FORMAT);
    }
}

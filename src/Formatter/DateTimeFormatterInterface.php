<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Formatter;

interface DateTimeFormatterInterface
{
    const DATE_TIME_FORMAT = 'Y-m-d H:i:s';

    function toDateTime(string $dateTimeAsString): \DateTimeInterface;
    function toString(\DateTimeInterface $dateTime): string;
}

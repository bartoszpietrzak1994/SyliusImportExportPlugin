<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Formatter;

interface DateTimeFormatterInterface
{
    public const DATE_TIME_FORMAT = 'Y-m-d H:i:s';

    public function toDateTime(string $dateTimeAsString): ?\DateTimeInterface;

    public function toString(\DateTimeInterface $dateTime): string;
}

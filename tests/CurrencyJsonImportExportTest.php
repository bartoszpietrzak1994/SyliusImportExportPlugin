<?php

declare(strict_types=1);

namespace Tests\FriendsOfSylius\SyliusImportExportPlugin;

final class CurrencyJsonImportExportTest extends AbstractJsonImportExportTest
{
    protected function provideName(): string
    {
        return 'currency';
    }

    protected function provideJsonData(): string
    {
        return <<<LOL
[
    {
        "Code": "USD"
    },
    {
        "Code": "PLN"
    }
]
LOL;
    }
}

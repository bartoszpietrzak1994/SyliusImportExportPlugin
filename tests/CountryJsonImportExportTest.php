<?php

declare(strict_types=1);

namespace Tests\FriendsOfSylius\SyliusImportExportPlugin;

final class CountryJsonImportExportTest extends AbstractJsonImportExportTest
{
    protected function provideName(): string
    {
        return 'country';
    }

    protected function provideJsonData(): string
    {
        return <<<LOL
[
    {
        "Code": "US",
        "Enabled": false
    },
    {
        "Code": "PL",
        "Enabled": true
    }
]
LOL;
    }
}

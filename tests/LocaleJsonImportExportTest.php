<?php

declare(strict_types=1);

namespace Tests\FriendsOfSylius\SyliusImportExportPlugin;

final class LocaleJsonImportExportTest extends AbstractJsonImportExportTest
{
    protected function provideName(): string
    {
        return 'locale';
    }

    protected function provideJsonData(): string
    {
        return <<<LOL
[
    {
        "Code": "en_US"
    },
    {
        "Code": "pl_PL"
    }
]
LOL;
    }
}

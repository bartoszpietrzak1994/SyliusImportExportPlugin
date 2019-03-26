<?php

declare(strict_types=1);

namespace Tests\FriendsOfSylius\SyliusImportExportPlugin;

final class ProvinceJsonImportExportTest extends AbstractJsonImportExportTest
{
    public function setUp(): void
    {
        parent::setUp();

        $this->loadJsonFixtures('country', <<<LOL
[
    {
        "Code": "US",
        "Enabled": true
    }
]
LOL
        );
    }

    protected function provideName(): string
    {
        return 'province';
    }

    protected function provideJsonData(): string
    {
        return <<<LOL
[
    {
        "Code": "CA",
        "Country": "US",
        "Name": "California",
        "Abbreviation": "Cal"
    },
    {
        "Code": "FL",
        "Country": "US",
        "Name": "Florida",
        "Abbreviation": "Flo"
    }
]
LOL;
    }
}

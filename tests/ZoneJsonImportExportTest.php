<?php

declare(strict_types=1);

namespace Tests\FriendsOfSylius\SyliusImportExportPlugin;

final class ZoneJsonImportExportTest extends AbstractJsonImportExportTest
{
    public function setUp(): void
    {
        parent::setUp();

        $this->loadJsonFixtures('country', <<<LOL
[
    {
        "Code": "US",
        "Enabled": true
    },
    {
        "Code": "CA",
        "Enabled": true
    },
    {
        "Code": "PL",
        "Enabled": true
    }
]
LOL
        );

        $this->loadJsonFixtures('province', <<<LOL
[
    {
        "Code": "PL_SL",
        "Country": "PL",
        "Name": "Silesia",
        "Abbreviation": "Sil"
    },
    {
        "Code": "PL_MA",
        "Country": "PL",
        "Name": "Masovia",
        "Abbreviation": "Mas"
    }
]
LOL
        );
    }

    protected function provideName(): string
    {
        return 'zone';
    }

    protected function provideJsonData(): string
    {
        return <<<LOL
[
    {
        "Code": "NA",
        "Name": "North America",
        "Type": "country",
        "Scope": "all",
        "Members": ["US", "CA"]
    },
    {
        "Code": "PL",
        "Name": "Poland: Silesian & Masovian Voivodeships",
        "Type": "province",
        "Scope": "all",
        "Members": ["PL_SL", "PL_MA"]
    }
    ,
    {
        "Code": "WEIRD",
        "Name": "Some random zones",
        "Type": "zone",
        "Scope": "all",
        "Members": ["NA", "PL"]
    }
]
LOL;
    }
}

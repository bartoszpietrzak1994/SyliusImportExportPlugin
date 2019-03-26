<?php

declare(strict_types=1);

namespace Tests\FriendsOfSylius\SyliusImportExportPlugin;

final class TaxRateJsonImportExportTest extends AbstractJsonImportExportTest
{
    public function setUp(): void
    {
        parent::setUp();

        $this->loadJsonFixtures('tax_category', <<<LOL
[
    {
        "Code": "clothing",
        "Name": "Clothing",
        "Description": "Corrupti dolorem ut qui et voluptatem. Repellendus sint omnis exercitationem ut. Quas soluta omnis quae tenetur consequatur voluptate."
    },
    {
        "Code": "books",
        "Name": "Books",
        "Description": "Voluptatem quia fugiat quidem quae ut molestiae autem. Nostrum aut non sit voluptatem quae. Eum et ea rerum voluptas est doloribus accusamus."
    }
]
LOL
        );

        $this->loadJsonFixtures('zone', <<<LOL
[
    {
        "Code": "US",
        "Name": "United States",
        "Type": "zone",
        "Scope": "all",
        "Members": []
    },
    {
        "Code": "PL",
        "Name": "Poland",
        "Type": "zone",
        "Scope": "all",
        "Members": []
    }
]
LOL
        );
    }

    protected function provideName(): string
    {
        return 'tax_rate';
    }

    protected function provideJsonData(): string
    {
        return <<<LOL
[
    {
        "Code": "clothing_sales_tax_7",
        "Category": "clothing",
        "Name": "Clothing Sales Tax 7%",
        "Amount": 0.07,
        "IncludedInPrice": false,
        "Calculator": "default",
        "Zone": "PL"
    },
    {
        "Code": "books_sales_tax_2",
        "Category": "books",
        "Name": "Books Sales Tax 2%",
        "Amount": 0.02,
        "IncludedInPrice": true,
        "Calculator": "default",
        "Zone": "US"
    }
]
LOL;
    }
}

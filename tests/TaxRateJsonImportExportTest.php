<?php

declare(strict_types=1);

namespace Tests\FriendsOfSylius\SyliusImportExportPlugin;

final class TaxRateJsonImportExportTest extends AbstractJsonImportExportTest
{
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
        "Zone": "US"
    },
    {
        "Code": "books_sales_tax_2",
        "Category": "books",
        "Name": "Books Sales Tax 2%",
        "Amount": 0.02,
        "IncludedInPrice": true,
        "Calculator": "default",
        "Zone": "US"
    },
    {
        "Code": "sales_tax_20",
        "Category": "other",
        "Name": "Sales Tax 20%",
        "Amount": 0.2,
        "IncludedInPrice": false,
        "Calculator": "default",
        "Zone": "US"
    }
]
LOL;
    }
}

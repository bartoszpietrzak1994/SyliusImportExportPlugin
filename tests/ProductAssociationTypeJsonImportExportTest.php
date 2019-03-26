<?php

declare(strict_types=1);

namespace Tests\FriendsOfSylius\SyliusImportExportPlugin;

final class ProductAssociationTypeJsonImportExportTest extends AbstractJsonImportExportTest
{
    protected function provideName(): string
    {
        return 'product_association_type';
    }

    protected function provideJsonData(): string
    {
        return <<<LOL
[
    {
        "Code": "UPSELL",
        "Translations": {
            "en_US": {
                "Name": "Up-sell"
            },
            "pl_PL": {
                "Name": "Dosprzedaż"
            }
        }
    },
    {
        "Code": "CROSSELL",
        "Translations": {
            "en_US": {
                "Name": "Cross-sell"
            }
        }
    }
]
LOL;
    }
}

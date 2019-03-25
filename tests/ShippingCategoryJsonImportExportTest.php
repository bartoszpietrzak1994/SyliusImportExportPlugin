<?php

declare(strict_types=1);

namespace Tests\FriendsOfSylius\SyliusImportExportPlugin;

final class ShippingCategoryJsonImportExportTest extends AbstractJsonImportExportTest
{
    protected function provideName(): string
    {
        return 'shipping_category';
    }

    protected function provideJsonData(): string
    {
        return <<<LOL
[
    {
        "Code": "LIGHT",
        "Name": "Light",
        "Description": "Light products."
    },
    {
        "Code": "HEAVY",
        "Name": "Heavy",
        "Description": "Heavy products."
    }
]
LOL;
    }
}

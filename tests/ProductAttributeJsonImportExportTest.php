<?php

declare(strict_types=1);

namespace Tests\FriendsOfSylius\SyliusImportExportPlugin;

final class ProductAttributeJsonImportExportTest extends AbstractJsonImportExportTest
{
    protected function provideName(): string
    {
        return 'product_attribute';
    }

    protected function provideJsonData(): string
    {
        return <<<LOL
[
    {
        "Code": "mug_material",
        "Type": "select",
        "StorageType": "json",
        "Configuration": {
            "multiple": false,
            "choices": {
                "e8a16878-c8f9-36c2-b61f-70ba54e2ff7f": {
                    "en_US": "Invisible porcelain"
                },
                "30dfc888-7be2-3d21-8aa0-5f81d6e3fd4c": {
                    "en_US": "Banana skin"
                }
            }
        },
        "Position": 0,
        "Translations": {
            "en_US": {
                "Name": "Mug material"
            }
        }
    },
    {
        "Code": "sticker_paper",
        "Type": "text",
        "StorageType": "text",
        "Configuration": [],
        "Position": 1,
        "Translations": {
            "en_US": {
                "Name": "Sticker paper"
            }
        }
    }
]
LOL;
    }
}

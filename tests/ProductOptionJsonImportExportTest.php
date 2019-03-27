<?php

declare(strict_types=1);

namespace Tests\FriendsOfSylius\SyliusImportExportPlugin;

final class ProductOptionJsonImportExportTest extends AbstractJsonImportExportTest
{
    protected function provideName(): string
    {
        return 'product_option';
    }

    protected function provideJsonData(): string
    {
        return <<<LOL
[
    {
        "Code": "mug_type",
        "Position": 0,
        "Translations": {
            "en_US": {
                "Name": "Mug type"
            },
            "pl_PL": {
                "Name": "Typ kubka"
            }
        },
        "Values": [
            {
                "Code": "mug_type_medium",
                "Translations": {
                    "en_US": {
                        "Value": "Medium mug"
                    },
                    "pl_PL": {
                        "Value": "Średni kubek"
                    }
                }
            },
            {
                "Code": "mug_type_double",
                "Translations": {
                    "en_US": {
                        "Value": "Double mug"
                    },
                    "pl_PL": {
                        "Value": "Podwójny kubek"
                    }
                }
            },
            {
                "Code": "mug_type_monster",
                "Translations": {
                    "en_US": {
                        "Value": "Monster mug"
                    },
                    "pl_PL": {
                        "Value": "Potworny kubek"
                    }
                }
            }
        ]
    },
    {
        "Code": "sticker_size",
        "Position": 1,
        "Translations": {
            "en_US": {
                "Name": "Sticker size"
            }
        },
        "Values": [
            {
                "Code": "sticker_size_3",
                "Translations": {
                    "en_US": {
                        "Value": "3\""
                    }
                }
            },
            {
                "Code": "sticker_size_5",
                "Translations": {
                    "en_US": {
                        "Value": "5\""
                    }
                }
            },
            {
                "Code": "sticker_size_7",
                "Translations": {
                    "en_US": {
                        "Value": "7\""
                    }
                }
            }
        ]
    }
]
LOL;
    }
}

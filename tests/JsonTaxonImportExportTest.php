<?php

declare(strict_types=1);

namespace Tests\FriendsOfSylius\SyliusImportExportPlugin;

final class JsonTaxonImportExportTest extends AbstractJsonImportExportTest
{
    protected function provideName(): string
    {
        return 'taxon';
    }

    protected function provideJsonData(): string
    {
        return <<<LOL
[
    {
        "Code": "category",
        "Parent": "",
        "Translations": {
            "en_US": {
                "Name": "Category",
                "Slug": "category",
                "Description": "In quasi sed hic mollitia consequuntur."
            }
        },
        "Position": 0
    },
    {
        "Code": "mugs",
        "Parent": "category",
        "Translations": {
            "en_US": {
                "Name": "Mugs",
                "Slug": "mugs",
                "Description": "Natus deleniti vel fugit aliquam distinctio consectetur."
            },
            "fr_FR": {
                "Name": "Tasses",
                "Slug": "tasses",
                "Description": "Quis aspernatur cum eum ad qui porro."
            }
        },
        "Position": 0
    },
    {
        "Code": "t_shirts",
        "Parent": "category",
        "Translations": {
            "en_US": {
                "Name": "T-Shirts",
                "Slug": "t-shirts",
                "Description": "Aut praesentium quaerat est minima."
            }
        },
        "Position": 1
    },
    {
        "Code": "mens_t_shirts",
        "Parent": "t_shirts",
        "Translations": {
            "en_US": {
                "Name": "Men",
                "Slug": "t-shirts\/men",
                "Description": "Alias voluptas non ipsam quia."
            },
            "fr_FR": {
                "Name": "Hommes",
                "Slug": "t-shirts\/hommes",
                "Description": "In unde inventore aliquid autem dolorum labore."
            }
        },
        "Position": 0
    }
]
LOL;
    }
}

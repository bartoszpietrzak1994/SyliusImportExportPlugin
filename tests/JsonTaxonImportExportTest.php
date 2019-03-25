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
                "Description": "In quasi sed hic mollitia consequuntur. Eius itaque non blanditiis debitis. Autem incidunt sed sint quis doloribus. Vitae itaque quos alias repellat nam sequi reiciendis voluptatum."
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
                "Description": "Natus deleniti vel fugit aliquam distinctio consectetur. Eius dignissimos quae eos consectetur dolorem placeat laudantium. Quae qui et qui."
            },
            "fr_FR": {
                "Name": "Tasses",
                "Slug": "tasses",
                "Description": "Quis aspernatur cum eum ad qui porro. Totam voluptatem sint cupiditate deleniti qui amet voluptatem. Est id dignissimos occaecati dignissimos fugit veritatis officiis."
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
                "Description": "Aut praesentium quaerat est minima. Sed magni error accusantium consequuntur. Sunt quibusdam sed quam et quasi quasi. Esse occaecati atque ut ut vel repudiandae."
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
                "Description": "Alias voluptas non ipsam quia. In nam voluptatibus aliquam debitis aut. Et earum voluptas fuga inventore. Dolorem illo aut eum autem inventore eius."
            },
            "fr_FR": {
                "Name": "Hommes",
                "Slug": "t-shirts\/hommes",
                "Description": "In unde inventore aliquid autem dolorum labore. Dolor quas at aut aut. Vero aut ratione ullam. Quia sunt voluptatem veritatis aut amet iure exercitationem."
            }
        },
        "Position": 0
    }
]
LOL;
    }
}

<?php

declare(strict_types=1);

namespace Tests\FriendsOfSylius\SyliusImportExportPlugin;

final class ShippingMethodJsonImportExportTest extends AbstractJsonImportExportTest
{
    public function setUp(): void
    {
        parent::setUp();

        $this->loadJsonFixtures('shipping_category', <<<LOL
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
LOL
        );
        $this->loadJsonFixtures('tax_category', <<<LOL
[
    {
        "Code": "clothing",
        "Name": "Clothing",
        "Description": "Corrupti dolorem ut qui et voluptatem. Repellendus sint omnis exercitationem ut. Quas soluta omnis quae tenetur consequatur voluptate."
    }
]
LOL
        );
        $this->loadJsonFixtures('zone', <<<LOL
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
]
LOL
        );
    }

    protected function provideName(): string
    {
        return "shipping_method";
    }

    protected function provideJsonData(): string
    {
        return <<<LOL
[
    {
        "Code": "ups",
        "Position": 0,
        "Category": "LIGHT",
        "CategoryRequirement": 1,
        "Configuration": {
            "US_WEB": {
                "amount": 7024
            }
        },
        "Translations": {
            "en_US": {
                "Name": "UPS",
                "Description": "Necessitatibus nemo et nihil inventore."
            }
        },
        "Zone": "PL",
        "TaxCategory": "clothing",
        "Calculator": "flat_rate",
        "Enabled": true
    },
    {
        "Code": "dhl_express",
        "Position": 1,
        "Category": "HEAVY",
        "CategoryRequirement": 1,
        "Configuration": {
            "US_WEB": {
                "amount": 4991
            }
        },
        "Translations": {
            "en_US": {
                "Name": "DHL Express",
                "Description": "Repellat officia aut assumenda nihil molestiae."
            }
        },
        "Zone": "NA",
        "TaxCategory": "clothing",
        "Calculator": "flat_rate",
        "Enabled": false
    },
    {
        "Code": "fedex",
        "Position": 2,
        "Category": "LIGHT",
        "CategoryRequirement": 1,
        "Configuration": {
            "US_WEB": {
                "amount": 6228
            }
        },
        "Translations": {
            "en_US": {
                "Name": "FedEx",
                "Description": "Impedit rerum omnis maxime iusto rerum quod exercitationem."
            }
        },
        "Zone": "PL",
        "TaxCategory": "clothing",
        "Calculator": "flat_rate",
        "Enabled": false
    }
]
LOL;
    }
}

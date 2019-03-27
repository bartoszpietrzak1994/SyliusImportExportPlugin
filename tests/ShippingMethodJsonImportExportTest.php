<?php

declare(strict_types=1);

namespace Tests\FriendsOfSylius\SyliusImportExportPlugin;

final class ShippingMethodJsonImportExportTest extends AbstractJsonImportExportTest
{
    public function setUp()
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
        "Zone": "US",
        "TaxCategory": "",
        "Channels": [
            "US_WEB"
        ]
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
        "Zone": "US",
        "TaxCategory": "",
        "Channels": [
            "US_WEB"
        ]
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
        "Zone": "US",
        "TaxCategory": "",
        "Channels": [
            "US_WEB"
        ]
    }
]
LOL;
    }
}

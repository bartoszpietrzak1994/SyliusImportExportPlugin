<?php

declare(strict_types=1);

namespace Tests\FriendsOfSylius\SyliusImportExportPlugin;

final class ShippingMethodJsonImportExportTest extends AbstractJsonImportExportTest
{
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
        "Enabled": true,
        "Channels": ["US_WEB"]
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
        "Enabled": false,
        "Channels": []
    }
]
LOL;
    }

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

        $this->loadJsonFixtures('currency', <<<LOL
[
    {
        "Code": "USD"
    }
]
LOL
        );

        $this->loadJsonFixtures('locale', <<<LOL
[
    {
        "Code": "en_US"
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

        $this->loadJsonFixtures('channel', <<<LOL
[
    {
        "Code": "US_WEB",
        "Name": "US Web Store",
        "Description": "sample_description",
        "Hostname": "localhost",
        "Color": "GreenYellow",
        "Enabled": true,
        "Currencies": ["USD"],
        "Locales": ["en_US"],
        "BaseCurrency": "USD",
        "DefaultLocale": "en_US",
        "DefaultTaxZone": "NA",
        "TaxCalculationStrategy": "order_items_based",
        "ThemeName": "default",
        "ContactEmail": "sylius@example.com",
        "SkippingShippingStepAllowed": false,
        "SkippingPaymentStepAllowed": false,
        "AccountVerificationRequired": true,
        "ShopBillingData" : {
            "City": "sample_city",
            "Street": "sample_street",
            "Country": "sample_country",
            "TaxId": "sample_tax_id",
            "Company": "sample_company",
            "Postcode": "11-111"
        }
    }
]
LOL
        );
    }
}

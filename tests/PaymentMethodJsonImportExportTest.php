<?php

declare(strict_types=1);

namespace Tests\FriendsOfSylius\SyliusImportExportPlugin;

final class PaymentMethodJsonImportExportTest extends AbstractJsonImportExportTest
{
    protected function provideName(): string
    {
        return 'payment_method';
    }

    protected function provideJsonData(): string
    {
        return <<<LOL
[
    {
        "Code": "cash_on_delivery",
        "Environment": "Lol",
        "Enabled": true,
        "Position": 0,
        "GatewayConfig": {
            "GatewayName": "Offline",
            "FactoryName": "offline",
            "Config": {
                "Foo": "Bar"
            }
        },
        "Channels": ["US_WEB"]
    },
    {
        "Code": "bank_transfer",
        "Environment": "",
        "Enabled": false,
        "Position": 1,
        "GatewayConfig": {
            "GatewayName": "Offline Custom",
            "FactoryName": "offline_custom",
            "Config": {
                "Bar": "Foo"
            }
        },
        "Channels": []
    }
]

LOL;
    }

    public function setUp(): void
    {
        parent::setUp();

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

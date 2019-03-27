<?php

declare(strict_types=1);

namespace Tests\FriendsOfSylius\SyliusImportExportPlugin;

final class ChannelJsonImportExportTest extends AbstractJsonImportExportTest
{
    public function setUp(): void
    {
        parent::setUp();

        $this->loadJsonFixtures('currency', <<<LOL
[
    {
        "Code": "USD"
    },
    {
        "Code": "PLN"
    }
]
LOL
        );
        $this->loadJsonFixtures('locale', <<<LOL
[
    {
        "Code": "en_US"
    },
    {
        "Code": "pl_PL"
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
    }

    protected function provideName(): string
    {
        return 'channel';
    }

    protected function provideJsonData(): string
    {
        return <<<LOL
[
    {
        "Code": "US_WEB",
        "Name": "US Web Store",
        "Description": "sample_description",
        "Hostname": "localhost",
        "Color": "GreenYellow",
        "Enabled": true,
        "BaseCurrency": "PLN",
        "DefaultLocale": "pl_PL",
        "DefaultTaxZone": "NA",
        "TaxCalculationStrategy": "order_items_based",
        "ThemeName": "default",
        "ContactEmail": "sylius@example.com",
        "SkippingShippingStepAllowed": false,
        "SkippingPaymentStepAllowed": false,
        "AccountVerificationRequired": true,
        "Postcode": "11-111",
        "City": "sample_city",
        "Street": "sample_street",
        "Country": "sample_country",
        "TaxId": "sample_tax_id",
        "Company": "sample_company"
  }
]
LOL;
    }
}

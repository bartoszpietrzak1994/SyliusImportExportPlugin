<?php

declare(strict_types=1);

namespace Tests\FriendsOfSylius\SyliusImportExportPlugin;

final class ExchangeRateJsonImportExportTest extends AbstractJsonImportExportTest
{
    public function setUp()
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
    }

    protected function provideName(): string
    {
        return 'exchange_rate';
    }

    protected function provideJsonData(): string
    {
        return <<<LOL
[
    {
        "Source_currency": "USD",
        "Target_currency": "PLN",
        "Ratio": 3.0
    },
    {
        "Source_currency": "PLN",
        "Target_currency": "USD",
        "Ratio": 1.5
    }
]
LOL;
    }
}

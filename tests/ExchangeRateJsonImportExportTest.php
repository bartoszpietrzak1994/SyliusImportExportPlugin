<?php

declare(strict_types=1);

namespace Tests\FriendsOfSylius\SyliusImportExportPlugin;

final class ExchangeRateJsonImportExportTest extends AbstractJsonImportExportTest
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
        "SourceCurrency": "USD",
        "TargetCurrency": "PLN",
        "Ratio": 3.0
    },
    {
        "SourceCurrency": "PLN",
        "TargetCurrency": "USD",
        "Ratio": 1.5
    }
]
LOL;
    }
}

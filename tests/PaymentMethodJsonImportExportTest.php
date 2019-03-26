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
        }
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
        }
    }
]

LOL;
    }
}

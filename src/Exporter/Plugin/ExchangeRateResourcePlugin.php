<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

use Sylius\Component\Currency\Model\CurrencyInterface;
use Sylius\Component\Currency\Model\ExchangeRateInterface;
use Webmozart\Assert\Assert;

final class ExchangeRateResourcePlugin extends ResourcePlugin
{
    /**
     * {@inheritdoc}
     */
    public function init(array $idsToExport): void
    {
        parent::init($idsToExport);

        /** @var ExchangeRateInterface $resource */
        foreach ($this->resources as $resource) {
            /** @var CurrencyInterface $sourceCurrency */
            $sourceCurrency = $resource->getSourceCurrency();
            Assert::notNull($sourceCurrency);

            $this->addDataForResource($resource, 'Source_currency', $sourceCurrency->getCode());

            /** @var CurrencyInterface $targetCurrency */
            $targetCurrency = $resource->getTargetCurrency();
            Assert::notNull($targetCurrency);

            $this->addDataForResource($resource, 'Target_currency', $targetCurrency->getCode());
        }
    }
}

<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

use Sylius\Component\Currency\Model\ExchangeRateInterface;

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
            $this->addDataForResource($resource, 'Source_currency', $resource->getSourceCurrency()->getCode());
            $this->addDataForResource($resource, 'Target_currency', $resource->getTargetCurrency()->getCode());
        }
    }
}

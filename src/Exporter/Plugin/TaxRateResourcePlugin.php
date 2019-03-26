<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

use Sylius\Component\Core\Model\TaxRateInterface;

final class TaxRateResourcePlugin extends ResourcePlugin
{
    /**
     * {@inheritdoc}
     */
    public function init(array $idsToExport): void
    {
        parent::init($idsToExport);

        /** @var TaxRateInterface $resource */
        foreach ($this->resources as $resource) {
            if (null !== $resource->getCategory()) {
                $this->addDataForResource($resource, 'Category', $resource->getCategory()->getCode());
            }
            if (null !== $resource->getZone()) {
                $this->addDataForResource($resource, 'Zone', $resource->getZone()->getCode());
            }
        }
    }
}

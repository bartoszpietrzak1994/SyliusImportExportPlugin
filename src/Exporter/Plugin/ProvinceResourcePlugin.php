<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

use Sylius\Component\Addressing\Model\ProvinceInterface;

final class ProvinceResourcePlugin extends ResourcePlugin
{
    /**
     * {@inheritdoc}
     */
    public function init(array $idsToExport): void
    {
        parent::init($idsToExport);

        /** @var ProvinceInterface $resource */
        foreach ($this->resources as $resource) {
            if (null !== $resource->getCountry()) {
                $this->addDataForResource($resource, 'Country', $resource->getCountry()->getCode());
            }
        }
    }
}

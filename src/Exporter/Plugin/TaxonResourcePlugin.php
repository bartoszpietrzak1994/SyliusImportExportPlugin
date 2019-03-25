<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

use Sylius\Component\Taxonomy\Model\TaxonInterface;

final class TaxonResourcePlugin extends ResourcePlugin
{
    /**
     * {@inheritdoc}
     */
    public function init(array $idsToExport): void
    {
        parent::init($idsToExport);

        /** @var TaxonInterface $resource */
        foreach ($this->resources as $resource) {
            if (null !== $resource->getParent()) {
                $this->addDataForResource($resource, 'Parent', $resource->getParent()->getId());
            }
        }
    }
}

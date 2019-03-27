<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

use Sylius\Component\Core\Model\ShippingMethodInterface;
use Sylius\Component\Shipping\Model\ShippingMethodTranslationInterface;

final class ShippingMethodResourcePlugin extends ResourcePlugin
{
    /**
     * {@inheritdoc}
     */
    public function init(array $idsToExport): void
    {
        parent::init($idsToExport);

        /** @var ShippingMethodInterface $resource */
        foreach ($this->resources as $resource) {
            if (null !== $resource->getZone()) {
                $this->addDataForResource($resource, 'Zone', $resource->getZone()->getCode());
            }

            if (null !== $resource->getCategory()) {
                $this->addDataForResource($resource, 'Category', $resource->getCategory()->getCode());
            }

            if (null !== $resource->getTaxCategory()) {
                $this->addDataForResource($resource, "TaxCategory", $resource->getTaxCategory()->getCode());
            }

            $translations = [];
            /** @var ShippingMethodTranslationInterface $translation */
            foreach ($resource->getTranslations() as $translation) {
                $translations[$translation->getLocale()] = [
                    'Name' => $translation->getName(),
                    'Description' => $translation->getDescription(),
                ];
            }

            $this->addDataForResource($resource, 'Translations', $translations);
        }
    }
}

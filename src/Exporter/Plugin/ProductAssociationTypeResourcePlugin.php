<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

use Sylius\Component\Product\Model\ProductAssociationTypeInterface;
use Sylius\Component\Product\Model\ProductAssociationTypeTranslationInterface;

final class ProductAssociationTypeResourcePlugin extends ResourcePlugin
{
    /**
     * {@inheritdoc}
     */
    public function init(array $idsToExport): void
    {
        parent::init($idsToExport);

        /** @var ProductAssociationTypeInterface $resource */
        foreach ($this->resources as $resource) {
            $translations = [];
            /** @var ProductAssociationTypeTranslationInterface $translation */
            foreach ($resource->getTranslations() as $translation) {
                $translations[$translation->getLocale()] = [
                    'Name' => $translation->getName(),
                ];
            }

            $this->addDataForResource($resource, 'Translations', $translations);
        }
    }
}

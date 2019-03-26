<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

use Sylius\Component\Product\Model\ProductAttributeInterface;
use Sylius\Component\Product\Model\ProductAttributeTranslationInterface;

final class ProductAttributeResourcePlugin extends ResourcePlugin
{
    /**
     * {@inheritdoc}
     */
    public function init(array $idsToExport): void
    {
        parent::init($idsToExport);

        /** @var ProductAttributeInterface $resource */
        foreach ($this->resources as $resource) {
            $translations = [];
            /** @var ProductAttributeTranslationInterface $translation */
            foreach ($resource->getTranslations() as $translation) {
                $translations[$translation->getLocale()] = [
                    'Name' => $translation->getName(),
                ];
            }

            $this->addDataForResource($resource, 'Translations', $translations);
        }
    }
}

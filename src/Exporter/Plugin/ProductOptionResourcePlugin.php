<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

use Sylius\Component\Product\Model\ProductOptionInterface;
use Sylius\Component\Product\Model\ProductOptionTranslationInterface;
use Sylius\Component\Product\Model\ProductOptionValueInterface;
use Sylius\Component\Product\Model\ProductOptionValueTranslationInterface;

final class ProductOptionResourcePlugin extends ResourcePlugin
{
    /**
     * {@inheritdoc}
     */
    public function init(array $idsToExport): void
    {
        parent::init($idsToExport);

        /** @var ProductOptionInterface $resource */
        foreach ($this->resources as $resource) {
            $translations = [];

            /** @var ProductOptionTranslationInterface $translation */
            foreach ($resource->getTranslations() as $translation) {
                $translations[$translation->getLocale()] = [
                    'Name' => $translation->getName(),
                ];
            }

            $this->addDataForResource($resource, 'Translations', $translations);

            $optionValues = [];

            /** @var ProductOptionValueInterface $optionValue */
            foreach ($resource->getValues() as $optionValue) {
                $translations = [];

                /** @var ProductOptionValueTranslationInterface $translation */
                foreach ($optionValue->getTranslations() as $translation) {
                    $translations[$translation->getLocale()] = [
                        'Value' => $translation->getValue(),
                    ];
                }

                $optionValues[] = [
                    'Code' => $optionValue->getCode(),
                    'Translations' => $translations,
                ];
            }

            $this->addDataForResource($resource, 'Values', $optionValues);
        }
    }
}

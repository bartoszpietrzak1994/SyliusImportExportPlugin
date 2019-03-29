<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

use Sylius\Component\Addressing\Model\ZoneInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ShopBillingDataInterface;
use Sylius\Component\Currency\Model\CurrencyInterface;
use Sylius\Component\Locale\Model\LocaleInterface;

final class ChannelResourcePlugin extends ResourcePlugin
{
    /**
     * {@inheritdoc}
     */
    public function init(array $idsToExport): void
    {
        parent::init($idsToExport);

        /** @var ChannelInterface $resource */
        foreach ($this->resources as $resource) {
            if (null !== $resource->getShopBillingData()) {
                $this->addShopBillingData($resource);
            }

            if (null !== $resource->getBaseCurrency()) {
                $this->addBaseCurrency($resource);
            }

            if (null !== $resource->getDefaultLocale()) {
                $this->addDefaultLocale($resource);
            }

            if (null !== $resource->getDefaultTaxZone()) {
                $this->addDefaultTaxZone($resource);
            }

            $this->addDataForResource($resource, 'Locales', array_map(function (LocaleInterface $locale): ?string {
                return $locale->getCode();
            }, $resource->getLocales()->toArray()));

            $this->addDataForResource($resource, 'Currencies', array_map(function (CurrencyInterface $currency): ?string {
                return $currency->getCode();
            }, $resource->getCurrencies()->toArray()));
        }
    }

    private function addShopBillingData(ChannelInterface $channel): void
    {
        /** @var ShopBillingDataInterface $shopBillingData */
        $shopBillingData = $channel->getShopBillingData();

        $exportedShopBillingData = [
            'Postcode' => $shopBillingData->getPostcode(),
            'City' => $shopBillingData->getCity(),
            'Street' => $shopBillingData->getStreet(),
            'Country' => $shopBillingData->getCountryCode(),
            'TaxId' => $shopBillingData->getTaxId(),
            'Company' => $shopBillingData->getCompany(),
        ];

        $this->addDataForResource($channel, 'ShopBillingData', $exportedShopBillingData);
    }

    private function addBaseCurrency(ChannelInterface $channel): void
    {
        /** @var CurrencyInterface $baseCurrency */
        $baseCurrency = $channel->getBaseCurrency();

        $this->addDataForResource($channel, 'BaseCurrency', $baseCurrency->getCode());
    }

    private function addDefaultLocale(ChannelInterface $channel): void
    {
        /** @var LocaleInterface $baseLocale */
        $baseLocale = $channel->getDefaultLocale();

        $this->addDataForResource($channel, 'DefaultLocale', $baseLocale->getCode());
    }

    private function addDefaultTaxZone(ChannelInterface $channel): void
    {
        /** @var ZoneInterface $defaultTaxZone */
        $defaultTaxZone = $channel->getDefaultTaxZone();

        $this->addDataForResource($channel, 'DefaultTaxZone', $defaultTaxZone->getCode());
    }
}

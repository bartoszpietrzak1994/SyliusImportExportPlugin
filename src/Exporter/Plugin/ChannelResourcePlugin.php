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
        }
    }

    private function addShopBillingData(ChannelInterface $channel): void
    {
        /** @var ShopBillingDataInterface $shopBillingData */
        $shopBillingData = $channel->getShopBillingData();

        $this->addDataForResource($channel, 'Postcode', $shopBillingData->getPostcode());
        $this->addDataForResource($channel, 'City', $shopBillingData->getCity());
        $this->addDataForResource($channel, 'Street', $shopBillingData->getStreet());
        $this->addDataForResource($channel, 'Country', $shopBillingData->getCountryCode());
        $this->addDataForResource($channel, 'TaxId', $shopBillingData->getTaxId());
        $this->addDataForResource($channel, 'Company', $shopBillingData->getCompany());
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

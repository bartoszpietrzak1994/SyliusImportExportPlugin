<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Processor;

use Doctrine\Common\Persistence\ObjectManager;
use Sylius\Component\Addressing\Model\ZoneInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ShopBillingData;
use Sylius\Component\Core\Model\ShopBillingDataInterface;
use Sylius\Component\Currency\Model\CurrencyInterface;
use Sylius\Component\Locale\Model\LocaleInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class ChannelProcessor implements ResourceProcessorInterface
{
    /** @var FactoryInterface */
    private $channelFactory;

    /** @var ChannelRepositoryInterface */
    private $channelRepository;

    /** @var RepositoryInterface */
    private $currencyRepository;

    /** @var RepositoryInterface */
    private $localeRepository;

    /** @var RepositoryInterface */
    private $zoneRepository;

    /** @var ObjectManager */
    private $manager;

    /** @var MetadataValidatorInterface */
    private $metadataValidator;

    /** @var string[] */
    private $headerKeys;

    public function __construct(
        FactoryInterface $channelFactory,
        ChannelRepositoryInterface $channelRepository,
        RepositoryInterface $currencyRepository,
        RepositoryInterface $localeRepository,
        RepositoryInterface $zoneRepository,
        ObjectManager $manager,
        MetadataValidatorInterface $metadataValidator,
        array $headerKeys
    ) {
        $this->channelFactory = $channelFactory;
        $this->channelRepository = $channelRepository;
        $this->currencyRepository = $currencyRepository;
        $this->localeRepository = $localeRepository;
        $this->zoneRepository = $zoneRepository;
        $this->manager = $manager;
        $this->metadataValidator = $metadataValidator;
        $this->headerKeys = $headerKeys;
    }


    public function process(array $data): void
    {
        $this->metadataValidator->validateHeaders($this->headerKeys, $data);

        $channel = $this->getChannel($data['Code']);
        $channel->setName($data['Name']);
        $channel->setHostname($data['Hostname']);
        $channel->setDescription($data['Description']);
        $channel->setEnabled($data['Enabled']);
        $channel->setTaxCalculationStrategy($data['TaxCalculationStrategy']);
        $channel->setAccountVerificationRequired($data['AccountVerificationRequired']);
        $channel->setContactEmail($data['ContactEmail']);
        $channel->setSkippingPaymentStepAllowed($data['SkippingPaymentStepAllowed']);
        $channel->setSkippingShippingStepAllowed($data['SkippingShippingStepAllowed']);
        $channel->setThemeName($data['ThemeName']);
        $channel->setColor($data['Color']);

        $channel->setShopBillingData(
            $this->getShopBillingData(
                $data['TaxId'],
                $data['Company'],
                $data['Country'],
                $data['Street'],
                $data['City'],
                $data['Postcode']
            )
        );

        $channel->setBaseCurrency($this->getCurrency($data['BaseCurrency']));
        $channel->setDefaultLocale($this->getLocale($data['DefaultLocale']));
        $channel->setDefaultTaxZone($this->getZone($data['DefaultTaxZone']));
    }

    private function getChannel(string $code): ChannelInterface
    {
        /** @var ChannelInterface|null $channel */
        $channel = $this->channelRepository->findOneBy(['code' => $code]);

        if ($channel === null) {
            /** @var ChannelInterface $channel */
            $channel = $this->channelFactory->createNew();
            $channel->setCode($code);

            $this->manager->persist($channel);
        }

        return $channel;
    }

    private function getShopBillingData(
        string $taxId,
        string $company,
        string $country,
        string $street,
        string $city,
        string $postcode
    ): ShopBillingDataInterface {
        $shopBillingData = new ShopBillingData();

        $shopBillingData->setTaxId($taxId);
        $shopBillingData->setCompany($company);
        $shopBillingData->setCountryCode($country);
        $shopBillingData->setStreet($street);
        $shopBillingData->setCity($city);
        $shopBillingData->setPostcode($postcode);

        $this->manager->persist($shopBillingData);

        return $shopBillingData;
    }

    private function getCurrency(?string $code): ?CurrencyInterface
    {
        /** @var CurrencyInterface|null $currency */
        $currency = $this->currencyRepository->findOneBy(['code' => $code]);

        return $currency;
    }

    private function getLocale(?string $code): ?LocaleInterface
    {
        /** @var LocaleInterface|null $locale */
        $locale = $this->localeRepository->findOneBy(['code' => $code]);

        return $locale;
    }

    private function getZone(?string $code): ?ZoneInterface
    {
        /** @var ZoneInterface|null $zone */
        $zone = $this->zoneRepository->findOneBy(['code' => $code]);

        return $zone;
    }
}

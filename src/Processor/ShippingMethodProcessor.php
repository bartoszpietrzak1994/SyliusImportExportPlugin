<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Processor;

use Doctrine\Common\Persistence\ObjectManager;
use Sylius\Component\Addressing\Model\ZoneInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ShippingMethodInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Shipping\Model\ShippingCategoryInterface;

final class ShippingMethodProcessor implements ResourceProcessorInterface
{
    /** @var FactoryInterface */
    private $shippingMethodFactory;

    /** @var RepositoryInterface */
    private $shippingMethodRepository;

    /** @var RepositoryInterface */
    private $zoneRepository;

    /** @var RepositoryInterface */
    private $categoryRepository;

    /** @var RepositoryInterface */
    private $channelRepository;

    /** @var ObjectManager */
    private $manager;

    /** @var MetadataValidatorInterface */
    private $metadataValidator;

    /** @var string[] */
    private $headerKeys;

    public function __construct(
        FactoryInterface $shippingMethodFactory,
        RepositoryInterface $shippingMethodRepository,
        RepositoryInterface $zoneRepository,
        RepositoryInterface $categoryRepository,
        RepositoryInterface $channelRepository,
        ObjectManager $manager,
        MetadataValidatorInterface $metadataValidator,
        array $headerKeys
    ) {
        $this->shippingMethodFactory = $shippingMethodFactory;
        $this->shippingMethodRepository = $shippingMethodRepository;
        $this->zoneRepository = $zoneRepository;
        $this->categoryRepository = $categoryRepository;
        $this->channelRepository = $channelRepository;
        $this->manager = $manager;
        $this->metadataValidator = $metadataValidator;
        $this->headerKeys = $headerKeys;
    }

    public function process(array $data): void
    {
        $this->metadataValidator->validateHeaders($this->headerKeys, $data);

        /** @var ShippingMethodInterface $shippingMethod */
        $shippingMethod = $this->getShippingMethod($data['Code']);
        $shippingMethod->setName($data['Name']);
        $shippingMethod->setZone($this->findZone($data['Zone']));
        $shippingMethod->setCategory($this->findCategory($data['Category']));
        $shippingMethod->setCalculator($data['Calculator']);
        $shippingMethod->setEnabled($data['Enabled']);
        $shippingMethod->setDescription($data['Description']);
        $shippingMethod->setConfiguration($data['Configuration']);
        $shippingMethod->setPosition($data['Position']);
        $shippingMethod->setCategoryRequirement($data['CategoryRequirement']);

        /** @var ChannelInterface $channel */
        foreach ($data['Channels'] as $channelCode) {
            $shippingMethod->addChannel($this->findChannel($channelCode));
        }

        foreach ($data['Translations'] as $locale => $translation) {
            $shippingMethod->setCurrentLocale($locale);
            $shippingMethod->setFallbackLocale($locale);

            $shippingMethod->setName($translation['Name']);
            $shippingMethod->setDescription($translation['Description']);
        }

        $this->manager->flush();
    }

    private function getShippingMethod(?string $code): ?ShippingMethodInterface
    {
        if ($code === null || $code === '') {
            return null;
        }

        $shippingMethod = $this->findShippingMethod($code);

        if ($shippingMethod === null) {
            /** @var ShippingMethodInterface $shippingMethod */
            $shippingMethod = $this->shippingMethodFactory->createNew();
            $shippingMethod->setCode($code);

            $this->saveShippingMethod($shippingMethod);
        }

        return $shippingMethod;
    }

    private function findShippingMethod(?string $code): ?ShippingMethodInterface
    {
        /** @var ShippingMethodInterface|null $shippingMethod */
        $shippingMethod = $this->shippingMethodRepository->findOneBy(['code' => $code]);

        return $shippingMethod;
    }

    private function saveShippingMethod(ShippingMethodInterface $shippingMethod): void
    {
        $this->manager->persist($shippingMethod);
    }

    private function findZone(?string $code): ?ZoneInterface
    {
        /** @var ZoneInterface|null $zone */
        $zone = $this->zoneRepository->findOneBy(['code' => $code]);

        return $zone;
    }

    private function findCategory(?string $code): ?ShippingCategoryInterface
    {
        /** @var ShippingCategoryInterface|null $shippingCategory */
        $shippingCategory = $this->categoryRepository->findOneBy(['code' => $code]);

        return $shippingCategory;
    }

    private function findChannel(?string $code): ?ChannelInterface
    {
        /** @var ChannelInterface|null $channel */
        $channel = $this->channelRepository->findOneBy(['code' => $code]);

        return $channel;
    }
}

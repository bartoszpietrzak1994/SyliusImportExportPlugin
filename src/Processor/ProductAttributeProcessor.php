<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Processor;

use Doctrine\Common\Persistence\ObjectManager;
use Sylius\Component\Product\Model\ProductAttributeInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class ProductAttributeProcessor implements ResourceProcessorInterface
{
    /** @var FactoryInterface */
    private $factory;

    /** @var RepositoryInterface */
    private $repository;

    /** @var ObjectManager */
    private $manager;

    /** @var MetadataValidatorInterface */
    private $metadataValidator;

    /** @var string[] */
    private $headerKeys;

    /**
     * @param string[] $headerKeys
     */
    public function __construct(
        FactoryInterface $factory,
        RepositoryInterface $repository,
        ObjectManager $manager,
        MetadataValidatorInterface $metadataValidator,
        array $headerKeys
    ) {
        $this->factory = $factory;
        $this->repository = $repository;
        $this->manager = $manager;
        $this->metadataValidator = $metadataValidator;
        $this->headerKeys = $headerKeys;
    }

    public function process(array $data): void
    {
        $this->metadataValidator->validateHeaders($this->headerKeys, $data);

        /** @var ProductAttributeInterface $productAttribute */
        $productAttribute = $this->getProductAttribute($data['Code']);
        $productAttribute->setType($data['Type']);
        $productAttribute->setStorageType($data['StorageType']);
        $productAttribute->setConfiguration($data['Configuration']);
        $productAttribute->setPosition($data['Position']);

        foreach ($data['Translations'] as $locale => $translation) {
            $productAttribute->setCurrentLocale($locale);
            $productAttribute->setFallbackLocale($locale);

            $productAttribute->setName($translation['Name']);
        }

        $this->manager->flush();
    }

    private function getProductAttribute(string $code): ProductAttributeInterface
    {
        /** @var ProductAttributeInterface|null $productAttribute */
        $productAttribute = $this->repository->findOneBy(['code' => $code]);

        if ($productAttribute === null) {
            /** @var ProductAttributeInterface $productAttribute */
            $productAttribute = $this->factory->createNew();
            $productAttribute->setCode($code);

            $this->manager->persist($productAttribute);
        }

        return $productAttribute;
    }
}

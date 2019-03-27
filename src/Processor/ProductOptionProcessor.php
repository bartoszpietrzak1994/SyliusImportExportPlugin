<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Processor;

use Doctrine\Common\Persistence\ObjectManager;
use Sylius\Component\Product\Model\ProductOptionInterface;
use Sylius\Component\Product\Model\ProductOptionValueInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class ProductOptionProcessor implements ResourceProcessorInterface
{
    /** @var FactoryInterface */
    private $factory;

    /** @var RepositoryInterface */
    private $repository;

    /** @var ObjectManager */
    private $manager;

    /** @var RepositoryInterface */
    private $productOptionValueRepository;

    /** @var FactoryInterface */
    private $productOptionValueFactory;

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
        RepositoryInterface $productOptionValueRepository,
        FactoryInterface $productOptionValueFactory,
        MetadataValidatorInterface $metadataValidator,
        array $headerKeys
    ) {
        $this->factory = $factory;
        $this->repository = $repository;
        $this->manager = $manager;
        $this->productOptionValueRepository = $productOptionValueRepository;
        $this->productOptionValueFactory = $productOptionValueFactory;
        $this->metadataValidator = $metadataValidator;
        $this->headerKeys = $headerKeys;
    }

    public function process(array $data): void
    {
        $this->metadataValidator->validateHeaders($this->headerKeys, $data);

        /** @var ProductOptionInterface $productOption */
        $productOption = $this->getProductOption($data['Code']);
        $productOption->setPosition($data['Position']);

        foreach ($data['Translations'] as $locale => $translation) {
            $productOption->setCurrentLocale($locale);
            $productOption->setFallbackLocale($locale);

            $productOption->setName($translation['Name']);
        }

        foreach ($data['Values'] as $valueData) {
            $productOptionValue = $this->getProductOptionValue($valueData['Code']);

            foreach ($valueData['Translations'] as $locale => $translation) {
                $productOptionValue->setCurrentLocale($locale);
                $productOptionValue->setFallbackLocale($locale);

                $productOptionValue->setValue($translation['Value']);
            }

            $productOption->addValue($productOptionValue);
        }

        $this->manager->flush();
    }

    private function getProductOption(string $code): ProductOptionInterface
    {
        /** @var ProductOptionInterface|null $productOption */
        $productOption = $this->repository->findOneBy(['code' => $code]);

        if ($productOption === null) {
            /** @var ProductOptionInterface $productOption */
            $productOption = $this->factory->createNew();
            $productOption->setCode($code);

            $this->manager->persist($productOption);
        }

        return $productOption;
    }

    private function getProductOptionValue(string $code): ProductOptionValueInterface
    {
        /** @var ProductOptionValueInterface|null $productOptionValue */
        $productOptionValue = $this->productOptionValueRepository->findOneBy(['code' => $code]);

        if ($productOptionValue === null) {
            /** @var ProductOptionValueInterface $productOptionValue */
            $productOptionValue = $this->productOptionValueFactory->createNew();
            $productOptionValue->setCode($code);
        }

        return $productOptionValue;
    }
}

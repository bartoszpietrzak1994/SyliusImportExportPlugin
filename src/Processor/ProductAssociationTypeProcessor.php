<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Processor;

use Doctrine\Common\Persistence\ObjectManager;
use Sylius\Component\Product\Model\ProductAssociationTypeInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class ProductAssociationTypeProcessor implements ResourceProcessorInterface
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

        /** @var ProductAssociationTypeInterface $productAssociationType */
        $productAssociationType = $this->getProductAssociationType($data['Code']);

        foreach ($data['Translations'] as $locale => $translation) {
            $productAssociationType->setCurrentLocale($locale);
            $productAssociationType->setFallbackLocale($locale);

            $productAssociationType->setName($translation['Name']);
        }

        $this->manager->flush();
    }

    private function getProductAssociationType(string $code): ProductAssociationTypeInterface
    {
        /** @var ProductAssociationTypeInterface|null $productAssociationType */
        $productAssociationType = $this->repository->findOneBy(['code' => $code]);

        if ($productAssociationType === null) {
            /** @var ProductAssociationTypeInterface $productAssociationType */
            $productAssociationType = $this->factory->createNew();
            $productAssociationType->setCode($code);

            $this->manager->persist($productAssociationType);
        }

        return $productAssociationType;
    }
}

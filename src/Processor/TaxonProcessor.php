<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Processor;

use Doctrine\Common\Persistence\ObjectManager;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Taxonomy\Factory\TaxonFactoryInterface;

final class TaxonProcessor implements ResourceProcessorInterface
{
    /** @var TaxonFactoryInterface */
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
        TaxonFactoryInterface $factory,
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

        /** @var TaxonInterface $taxon */
        $taxon = $this->getTaxon($data['Code']);
        $taxon->setParent($this->getTaxon($data['Parent']));
        $taxon->setPosition($data['Position']);

        foreach ($data['Translations'] as $locale => $translation) {
            $taxon->setCurrentLocale($locale);
            $taxon->setFallbackLocale($locale);

            $taxon->setName($translation['Name']);
            $taxon->setDescription($translation['Description']);
            $taxon->setSlug($translation['Slug']);
        }

        $this->manager->flush();
    }

    private function getTaxon(?string $code): ?TaxonInterface
    {
        if ($code === null || $code === '') {
            return null;
        }
        $taxon = $this->findTaxon($code);

        if ($taxon === null) {
            /** @var TaxonInterface $taxon */
            $taxon = $this->factory->createNew();
            $taxon->setCode($code);

            $this->saveTaxon($taxon);
        }

        return $taxon;
    }

    private function findTaxon(?string $code): ?TaxonInterface
    {
        /** @var TaxonInterface|null $taxon */
        $taxon = $this->repository->findOneBy(['code' => $code]);

        return $taxon;
    }

    private function saveTaxon(TaxonInterface $taxon): void
    {
        $this->manager->persist($taxon);
    }
}

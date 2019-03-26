<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Processor;

use Doctrine\Common\Persistence\ObjectManager;
use Sylius\Component\Addressing\Model\ZoneInterface;
use Sylius\Component\Core\Model\TaxRateInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Taxation\Model\TaxCategoryInterface;

final class TaxRateProcessor implements ResourceProcessorInterface
{
    /** @var FactoryInterface */
    private $taxRateFactory;

    /** @var RepositoryInterface */
    private $taxRateRepository;

    /** @var RepositoryInterface */
    private $taxCategoryRepository;

    /** @var RepositoryInterface */
    private $zoneRepository;

    /** @var ObjectManager */
    private $manager;

    /** @var MetadataValidatorInterface */
    private $metadataValidator;

    /** @var string[] */
    private $headerKeys;

    public function __construct(
        FactoryInterface $taxRateFactory,
        RepositoryInterface $taxRateRepository,
        RepositoryInterface $taxCategoryRepository,
        RepositoryInterface $zoneRepository,
        ObjectManager $manager,
        MetadataValidatorInterface $metadataValidator,
        array $headerKeys
    ) {
        $this->taxRateFactory = $taxRateFactory;
        $this->taxRateRepository = $taxRateRepository;
        $this->taxCategoryRepository = $taxCategoryRepository;
        $this->zoneRepository = $zoneRepository;
        $this->manager = $manager;
        $this->metadataValidator = $metadataValidator;
        $this->headerKeys = $headerKeys;
    }

    public function process(array $data): void
    {
        $this->metadataValidator->validateHeaders($this->headerKeys, $data);

        /** @var TaxRateInterface $taxRate */
        $taxRate = $this->getTaxRate($data['Code']);
        $taxRate->setAmount($data['Amount']);
        $taxRate->setName($data['Name']);
        $taxRate->setCalculator($data['Calculator']);
        $taxRate->setIncludedInPrice($data['IncludedInPrice']);
        $taxRate->setZone($this->findZone($data['Zone']));
        $taxRate->setCategory($this->findTaxCategory($data['Category']));

        $this->manager->flush();
    }

    private function getTaxRate(?string $code): ?TaxRateInterface
    {
        if ($code === null || $code === '') {
            return null;
        }
        $taxRate = $this->findTaxRate($code);

        if ($taxRate === null) {
            /** @var TaxRateInterface $taxRate */
            $taxRate = $this->taxRateFactory->createNew();
            $taxRate->setCode($code);

            $this->saveTaxRate($taxRate);
        }

        return $taxRate;
    }

    private function findTaxRate(?string $code): ?TaxRateInterface
    {
        /** @var TaxRateInterface|null $taxRate */
        $taxRate = $this->taxRateRepository->findOneBy(['code' => $code]);

        return $taxRate;
    }

    private function saveTaxRate(TaxRateInterface $taxRate): void
    {
        $this->manager->persist($taxRate);
    }

    private function findTaxCategory(?string $code): ?TaxCategoryInterface
    {
        /** @var TaxCategoryInterface|null $taxCategory */
        $taxCategory = $this->taxCategoryRepository->findOneBy(['code' => $code]);

        return $taxCategory;
    }

    private function findZone(?string $code): ?ZoneInterface
    {
        /** @var ZoneInterface|null $zone */
        $zone = $this->zoneRepository->findOneBy(['code' => $code]);

        return $zone;
    }
}

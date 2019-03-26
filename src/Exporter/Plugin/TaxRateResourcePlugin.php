<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\TaxRateInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

final class TaxRateResourcePlugin extends ResourcePlugin
{
    /** @var ResourceInterface */
    private $taxCategoryRepository;

    /** @var ResourceInterface */
    private $zoneRepository;

    public function __construct(
        RepositoryInterface $repository,
        PropertyAccessorInterface $propertyAccessor,
        EntityManagerInterface $entityManager,
        ResourceInterface $taxCategoryRepository,
        ResourceInterface $zoneRepository
    ) {
        parent::__construct($repository, $propertyAccessor, $entityManager);

        $this->taxCategoryRepository = $taxCategoryRepository;
        $this->zoneRepository = $zoneRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function init(array $idsToExport): void
    {
        parent::init($idsToExport);

        /** @var TaxRateInterface $resource */
        foreach ($this->resources as $resource) {
            if (null !== $resource->getCategory()) {
                $this->addDataForResource($resource, 'Category', $resource->getCategory()->getCode());
            }
            if (null !== $resource->getZone()) {
                $this->addDataForResource($resource, 'Zone', $resource->getZone()->getCode());
            }
        }
    }
}
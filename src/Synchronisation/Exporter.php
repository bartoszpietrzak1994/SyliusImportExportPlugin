<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Synchronisation;

use FriendsOfSylius\SyliusImportExportPlugin\Exporter\ExporterRegistry;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\ResourceExporterInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class Exporter
{
    /** @var ContainerInterface */
    private $container;

    /** @var ExporterRegistry */
    private $exporterRegistry;

    public function __construct(ContainerInterface $container, ExporterRegistry $exporterRegistry)
    {
        $this->container = $container;
        $this->exporterRegistry = $exporterRegistry;
    }

    public function __invoke(string $name): array
    {
        /** @var RepositoryInterface $repository */
        $repository = $this->container->get(sprintf('sylius.repository.%s', $name));
        $items = $repository->findAll();
        $idsToExport = array_map(function (ResourceInterface $item) {
            return $item->getId();
        }, $items);

        $exporterName = ExporterRegistry::buildServiceName(sprintf('sylius.%s', $name), 'json');

        /** @var ResourceExporterInterface $exporter */
        $exporter = $this->exporterRegistry->get($exporterName);

        return $exporter->exportData($idsToExport);
    }
}

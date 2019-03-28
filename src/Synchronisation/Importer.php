<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Synchronisation;

use FriendsOfSylius\SyliusImportExportPlugin\Importer\ImporterInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\ImporterRegistry;

final class Importer
{
    /** @var ImporterRegistry */
    private $importerRegistry;

    public function __construct(ImporterRegistry $importerRegistry)
    {
        $this->importerRegistry = $importerRegistry;
    }

    public function __invoke(string $name, string $path): void
    {
        $importerName = ImporterRegistry::buildServiceName($name, 'json');

        /** @var ImporterInterface $importer */
        $importer = $this->importerRegistry->get($importerName);

        $importer->import($path);
    }
}

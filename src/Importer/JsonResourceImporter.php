<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Importer;

use Doctrine\Common\Persistence\ObjectManager;
use FriendsOfSylius\SyliusImportExportPlugin\Exception\ImporterException;
use FriendsOfSylius\SyliusImportExportPlugin\Processor\ResourceProcessorInterface;

final class JsonResourceImporter extends ResourceImporter implements SingleDataArrayImporterInterface
{
    public function __construct(
        ObjectManager $objectManager,
        ResourceProcessorInterface $resourceProcessor,
        ImporterResultInterface $importerResult,
        int $batchSize,
        bool $failOnIncomplete,
        bool $stopOnFailure
    ) {
        $this->objectManager = $objectManager;
        $this->resourceProcessor = $resourceProcessor;
        $this->result = $importerResult;
        $this->batchSize = $batchSize;
        $this->failOnIncomplete = $failOnIncomplete;
        $this->stopOnFailure = $stopOnFailure;
    }

    /**
     * {@inheritdoc}
     */
    public function import(string $fileName): ImporterResultInterface
    {
        $this->result->start();

        $contents = file_get_contents($fileName);
        if (false === $contents) {
            throw new ImporterException(sprintf('File %s could not be loaded', $fileName));
        }

        foreach (json_decode($contents, true) as $i => $row) {
            if ($this->importData($i, $row)) {
                break;
            }
        }

        $this->result->stop();

        return $this->result;
    }

    /**
     * {@inheritdoc}
     */
    public function importSingleDataArrayWithoutResult(array $dataToImport): void
    {
        $this->resourceProcessor->process($dataToImport);
        $this->objectManager->flush();
    }
}

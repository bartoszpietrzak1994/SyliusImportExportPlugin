<?php

declare(strict_types=1);

namespace Tests\FriendsOfSylius\SyliusImportExportPlugin;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\ExporterRegistry;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\ResourceExporterInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\ImporterInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\ImporterRegistry;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\ImporterResultInterface;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Assert;
use Sylius\Component\Resource\Model\ResourceInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class AbstractJsonImportExportTest extends KernelTestCase
{
    private $filesystemPath;

    public function setUp(): void {
        $this->filesystemPath = vfsStream::setup()->url();

        static::bootKernel();
        (new ORMPurger(static::$container->get('doctrine.orm.default_entity_manager')))->purge();
    }

    /** @test */
    final public function it_imports_and_exports(): void
    {
        $importingFile = sprintf('%s/%s', $this->filesystemPath, 'import.json');
        $exportingFile = sprintf('%s/%s', $this->filesystemPath, 'export.json');

        file_put_contents($importingFile, $this->provideJsonData());

        $this->doImport($this->provideName(), $importingFile);
        $this->doExport($this->provideName(), $exportingFile);

        Assert::assertEquals(
            json_decode(file_get_contents($importingFile), true),
            json_decode(file_get_contents($exportingFile), true)
        );
    }

    final protected function loadJsonFixtures(string $name, string $content): void
    {
        $file = sprintf('%s/%s', $this->filesystemPath, 'load.json');

        file_put_contents($file, $content);

        $this->doImport($name, $file);

    }

    abstract protected function provideName(): string;
    abstract protected function provideJsonData(): string;

    private function doImport(string $name, string $file): void
    {
        $importerName = ImporterRegistry::buildServiceName($name, 'json');

        /** @var ImporterInterface $importer */
        $importer = static::$container->get('sylius.importers_registry')->get($importerName);

        /** @var ImporterResultInterface $result */
        $result = static::$container->get('sylius.importer.result');

        $successRowsBefore = count($result->getSuccessRows());

        $importer->import($file);

        $successRowsAfter = count($result->getSuccessRows());

        Assert::assertSame(count(json_decode(file_get_contents($file), true)), $successRowsAfter - $successRowsBefore);
    }

    private function doExport(string $name, string $exportingFile): void
    {
        $repository = static::$container->get(sprintf('sylius.repository.%s', $name));
        $items = $repository->findAll();
        $idsToExport = array_map(function (ResourceInterface $item) {
            return $item->getId();
        }, $items);

        $exporterName = ExporterRegistry::buildServiceName(sprintf('sylius.%s', $name), 'json');

        /** @var ResourceExporterInterface $exporter */
        $exporter = static::$container->get('sylius.exporters_registry')->get($exporterName);
        $exporter->setExportFile($exportingFile);
        $exporter->export($idsToExport);
    }
}

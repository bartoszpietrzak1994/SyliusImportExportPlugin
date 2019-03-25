<?php

declare(strict_types=1);

namespace Tests\FriendsOfSylius\SyliusImportExportPlugin;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\ExporterRegistry;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\ResourceExporterInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\ImporterInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\ImporterRegistry;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Assert;
use Sylius\Component\Resource\Model\ResourceInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class AbstractJsonImportExportTest extends KernelTestCase
{
    private $filesystemPath;

    public function setUp() {
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

        $importerName = ImporterRegistry::buildServiceName($this->provideName(), 'json');

        /** @var ImporterInterface $importer */
        $importer = static::$container->get('sylius.importers_registry')->get($importerName);
        $importer->import($importingFile);

        $repository = static::$container->get(sprintf('sylius.repository.%s', $this->provideName()));
        $items = $repository->findAll();
        $idsToExport = array_map(function (ResourceInterface $item) {
            return $item->getId();
        }, $items);

        $exporterName = ExporterRegistry::buildServiceName(sprintf('sylius.%s', $this->provideName()), 'json');

        /** @var ResourceExporterInterface $exporter */
        $exporter = static::$container->get('sylius.exporters_registry')->get($exporterName);
        $exporter->setExportFile($exportingFile);
        $exporter->export($idsToExport);

        Assert::assertEquals(
            json_decode(file_get_contents($importingFile), true),
            json_decode(file_get_contents($exportingFile), true)
        );
    }

    final protected function loadJsonFixtures(string $name, string $content): void
    {
        $file = sprintf('%s/%s', $this->filesystemPath, 'load.json');

        file_put_contents($file, $content);

        $importerName = ImporterRegistry::buildServiceName($name, 'json');

        /** @var ImporterInterface $importer */
        $importer = static::$container->get('sylius.importers_registry')->get($importerName);
        $importer->import($file);
    }

    abstract protected function provideName(): string;
    abstract protected function provideJsonData(): string;
}

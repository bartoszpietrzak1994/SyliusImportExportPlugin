<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Synchronisation;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Gaufrette\Filesystem as GaufretteFilesystem;
use Sylius\Component\Core\Model\ImageInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;

final class BackendSynchroniser
{
    /** @var array */
    private const JOBS = [
        'locale',
        'currency',
        'exchange_rate',
        'country',
        'province',
        'zone',
        'tax_category',
        'tax_rate',
        'shipping_category',
        'shipping_method',
        'payment_method',
        'channel',
        'taxon',
        'product_option',
        'product_attribute',
        'product_association_type',
        'product',
    ];

    /** @var GaufretteFilesystem */
    private $externalStorageFilesystem;

    /** @var SymfonyFilesystem */
    private $temporaryFilesystem;

    /** @var Exporter */
    private $exporter;

    /** @var Importer */
    private $importer;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var GaufretteFilesystem */
    private $imageFilesystem;

    /** @var RepositoryInterface */
    private $productImageRepository;

    /** @var RepositoryInterface */
    private $taxonImageRepository;

    public function __construct(
        GaufretteFilesystem $externalStorageFilesystem,
        Exporter $exporter,
        Importer $importer,
        EntityManagerInterface $entityManager,
        GaufretteFilesystem $imageFilesystem,
        RepositoryInterface $productImageRepository,
        RepositoryInterface $taxonImageRepository
    ) {
        $this->externalStorageFilesystem = $externalStorageFilesystem;
        $this->temporaryFilesystem = new SymfonyFilesystem();
        $this->exporter = $exporter;
        $this->importer = $importer;
        $this->entityManager = $entityManager;
        $this->imageFilesystem = $imageFilesystem;
        $this->productImageRepository = $productImageRepository;
        $this->taxonImageRepository = $taxonImageRepository;
    }

    public function export(string $namespace): void
    {
        foreach (self::JOBS as $job) {
            $data = ($this->exporter)($job);

            $this->externalStorageFilesystem->write(sprintf('%s/%s.json', $namespace, $job), (string) json_encode($data), true);
        }

        /** @var ImageInterface $image */
        foreach ($this->productImageRepository->findAll() as $image) {
            $this->externalStorageFilesystem->write(
                sprintf('%s/images/%s', $namespace, $image->getPath()),
                $this->imageFilesystem->read($image->getPath()),
                true
            );
        }

        /** @var ImageInterface $image */
        foreach ($this->taxonImageRepository->findAll() as $image) {
            $this->externalStorageFilesystem->write(
                sprintf('%s/images/%s', $namespace, $image->getPath()),
                $this->imageFilesystem->read($image->getPath()),
                true
            );
        }
    }

    public function import(string $namespace): void
    {
        (new ORMPurger($this->entityManager))->purge();

        foreach (self::JOBS as $job) {
            $temporaryPath = $this->temporaryFilesystem->tempnam(md5(self::class), sprintf('%s/%s', $namespace, $job));

            file_put_contents($temporaryPath, $this->externalStorageFilesystem->read(sprintf('%s/%s.json', $namespace, $job)));

            ($this->importer)($job, $temporaryPath);

            unlink($temporaryPath);
        }

        /** @var ImageInterface $image */
        foreach ($this->productImageRepository->findAll() as $image) {
            $this->imageFilesystem->write(
                $image->getPath(),
                $this->externalStorageFilesystem->read(sprintf('%s/images/%s', $namespace, $image->getPath())),
                true
            );
        }

        /** @var ImageInterface $image */
        foreach ($this->taxonImageRepository->findAll() as $image) {
            $this->imageFilesystem->write(
                $image->getPath(),
                $this->externalStorageFilesystem->read(sprintf('%s/images/%s', $namespace, $image->getPath())),
                true
            );
        }
    }
}

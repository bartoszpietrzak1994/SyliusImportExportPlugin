<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Synchronisation;

use Doctrine\Common\Persistence\ObjectManager;
use Gaufrette\Filesystem as GaufretteFilesystem;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;

final class OrderSynchroniser
{
    /** @var GaufretteFilesystem */
    private $externalStorageFilesystem;

    /** @var SymfonyFilesystem */
    private $temporaryFilesystem;

    /** @var Exporter */
    private $exporter;

    /** @var Importer */
    private $importer;

    /** @var RepositoryInterface */
    private $orderRepository;

    /** @var ObjectManager */
    private $orderManager;

    public function __construct(
        GaufretteFilesystem $externalStorageFilesystem,
        Exporter $exporter,
        Importer $importer,
        RepositoryInterface $orderRepository,
        ObjectManager $orderManager
    ) {
        $this->externalStorageFilesystem = $externalStorageFilesystem;
        $this->temporaryFilesystem = new SymfonyFilesystem();
        $this->exporter = $exporter;
        $this->importer = $importer;
        $this->orderRepository = $orderRepository;
        $this->orderManager = $orderManager;
    }

    public function export(): void
    {
        $data = ($this->exporter)('order');

        $this->externalStorageFilesystem->write(
            sprintf('orders/%s.json', md5(uniqid('', true))),
            (string) json_encode($data),
            true
        );

        foreach ($this->orderRepository->findAll() as $order) {
            $this->orderManager->remove($order);
        }
        $this->orderManager->flush();
    }

    public function import(): void
    {
        foreach ($this->externalStorageFilesystem->listKeys('orders/')['keys'] as $file) {
            $temporaryPath = $this->temporaryFilesystem->tempnam(md5(self::class . $file), 'order.json');

            file_put_contents($temporaryPath, $this->externalStorageFilesystem->read($file));

            ($this->importer)('order', $temporaryPath);

            unlink($temporaryPath);

            $this->externalStorageFilesystem->delete($file);
        }
    }
}

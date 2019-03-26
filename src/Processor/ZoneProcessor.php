<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Processor;

use Doctrine\Common\Persistence\ObjectManager;
use Sylius\Component\Addressing\Model\ZoneInterface;
use Sylius\Component\Addressing\Model\ZoneMemberInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class ZoneProcessor implements ResourceProcessorInterface
{
    /** @var FactoryInterface */
    private $factory;

    /** @var RepositoryInterface */
    private $repository;

    /** @var ObjectManager */
    private $manager;

    /** @var FactoryInterface */
    private $zoneMemberFactory;

    /** @var MetadataValidatorInterface */
    private $metadataValidator;

    /** @var string[] */
    private $headerKeys;

    /**
     * @param string[] $headerKeys
     */
    public function __construct(
        FactoryInterface $factory,
        RepositoryInterface $repository,
        ObjectManager $manager,
        FactoryInterface $zoneMemberFactory,
        MetadataValidatorInterface $metadataValidator,
        array $headerKeys
    ) {
        $this->factory = $factory;
        $this->repository = $repository;
        $this->manager = $manager;
        $this->zoneMemberFactory = $zoneMemberFactory;
        $this->metadataValidator = $metadataValidator;
        $this->headerKeys = $headerKeys;
    }

    public function process(array $data): void
    {
        $this->metadataValidator->validateHeaders($this->headerKeys, $data);

        /** @var ZoneInterface $zone */
        $zone = $this->getZone($data['Code']);
        $zone->setName($data['Name']);
        $zone->setType($data['Type']);
        $zone->setScope($data['Scope']);

        foreach ($data['Members'] as $zoneMemberCode) {
            /** @var ZoneMemberInterface $zoneMember */
            $zoneMember = $this->zoneMemberFactory->createNew();
            $zoneMember->setCode($zoneMemberCode);

            $zone->addMember($zoneMember);
        }
    }

    private function getZone(string $code): ZoneInterface
    {
        /** @var ZoneInterface|null $zone */
        $zone = $this->repository->findOneBy(['code' => $code]);

        if ($zone === null) {
            /** @var ZoneInterface $zone */
            $zone = $this->factory->createNew();
            $zone->setCode($code);

            $this->manager->persist($zone);
        }

        return $zone;
    }
}

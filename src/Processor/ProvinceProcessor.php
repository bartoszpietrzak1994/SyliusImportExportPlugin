<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Processor;

use Doctrine\Common\Persistence\ObjectManager;
use Sylius\Component\Addressing\Model\ProvinceInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class ProvinceProcessor implements ResourceProcessorInterface
{
    /** @var FactoryInterface */
    private $factory;

    /** @var RepositoryInterface */
    private $repository;

    /** @var ObjectManager */
    private $manager;

    /** @var RepositoryInterface */
    private $countryRepository;

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
        RepositoryInterface $countryRepository,
        MetadataValidatorInterface $metadataValidator,
        array $headerKeys
    ) {
        $this->factory = $factory;
        $this->repository = $repository;
        $this->manager = $manager;
        $this->countryRepository = $countryRepository;
        $this->metadataValidator = $metadataValidator;
        $this->headerKeys = $headerKeys;
    }

    public function process(array $data): void
    {
        $this->metadataValidator->validateHeaders($this->headerKeys, $data);

        /** @var ProvinceInterface $province */
        $province = $this->getProvince($data['Code']);
        $province->setName($data['Name']);
        $province->setAbbreviation($data['Abbreviation']);
        $province->setCountry($this->countryRepository->findOneBy(['code' => $data['Country']]));
    }

    private function getProvince(string $code): ProvinceInterface
    {
        /** @var ProvinceInterface|null $province */
        $province = $this->repository->findOneBy(['code' => $code]);

        if ($province === null) {
            /** @var ProvinceInterface $province */
            $province = $this->factory->createNew();
            $province->setCode($code);

            $this->manager->persist($province);
        }

        return $province;
    }
}

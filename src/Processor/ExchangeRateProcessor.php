<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Processor;

use Doctrine\Common\Persistence\ObjectManager;
use FriendsOfSylius\SyliusImportExportPlugin\Exception\ItemIncompleteException;
use Sylius\Component\Currency\Model\CurrencyInterface;
use Sylius\Component\Currency\Model\ExchangeRateInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class ExchangeRateProcessor implements ResourceProcessorInterface
{
    /** @var FactoryInterface */
    private $factory;

    /** @var RepositoryInterface */
    private $repository;

    /** @var ObjectManager */
    private $manager;

    /** @var RepositoryInterface */
    private $currencyRepository;

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
        RepositoryInterface $currencyRepository,
        MetadataValidatorInterface $metadataValidator,
        array $headerKeys
    ) {
        $this->factory = $factory;
        $this->repository = $repository;
        $this->manager = $manager;
        $this->currencyRepository = $currencyRepository;
        $this->metadataValidator = $metadataValidator;
        $this->headerKeys = $headerKeys;
    }

    public function process(array $data): void
    {
        $this->metadataValidator->validateHeaders($this->headerKeys, $data);

        /** @var ExchangeRateInterface $exchangeRate */
        $exchangeRate = $this->getExchangeRate($data['SourceCurrency'], $data['TargetCurrency']);
        $exchangeRate->setRatio($data['Ratio']);

        $this->manager->flush();
    }

    private function getExchangeRate(string $sourceCurrencyCode, string $targetCurrencyCode): ExchangeRateInterface
    {
        /** @var CurrencyInterface|null $sourceCurrency */
        $sourceCurrency = $this->currencyRepository->findOneBy(['code' => $sourceCurrencyCode]);

        /** @var CurrencyInterface|null $targetCurrency */
        $targetCurrency = $this->currencyRepository->findOneBy(['code' => $targetCurrencyCode]);

        if ($sourceCurrency === null || $targetCurrency === null) {
            throw new ItemIncompleteException();
        }

        /** @var ExchangeRateInterface|null $exchangeRate */
        $exchangeRate = $this->repository->findOneBy(['sourceCurrency' => $sourceCurrency, 'targetCurrency' => $targetCurrency]);

        if ($exchangeRate === null) {
            /** @var ExchangeRateInterface $exchangeRate */
            $exchangeRate = $this->factory->createNew();
            $exchangeRate->setSourceCurrency($sourceCurrency);
            $exchangeRate->setTargetCurrency($targetCurrency);

            $this->manager->persist($exchangeRate);
        }

        return $exchangeRate;
    }
}

<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Processor;

use Doctrine\Common\Persistence\ObjectManager;
use Sylius\Bundle\PayumBundle\Model\GatewayConfigInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class PaymentMethodProcessor implements ResourceProcessorInterface
{
    /** @var FactoryInterface */
    private $factory;

    /** @var RepositoryInterface */
    private $repository;

    /** @var ObjectManager */
    private $manager;

    /** @var FactoryInterface */
    private $gatewayConfigFactory;

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
        FactoryInterface $gatewayFactory,
        MetadataValidatorInterface $metadataValidator,
        array $headerKeys
    ) {
        $this->factory = $factory;
        $this->repository = $repository;
        $this->manager = $manager;
        $this->gatewayConfigFactory = $gatewayFactory;
        $this->metadataValidator = $metadataValidator;
        $this->headerKeys = $headerKeys;
    }

    public function process(array $data): void
    {
        $this->metadataValidator->validateHeaders($this->headerKeys, $data);

        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $this->getPaymentMethod($data['Code']);
        $paymentMethod->setEnvironment($data['Environment']);
        $paymentMethod->setEnabled($data['Enabled']);
        $paymentMethod->setPosition($data['Position']);

        /** @var GatewayConfigInterface $gatewayConfig */
        $gatewayConfig = $this->gatewayConfigFactory->createNew();
        $gatewayConfig->setGatewayName($data['Gateway_config']['Gateway_name']);
        $gatewayConfig->setFactoryName($data['Gateway_config']['Factory_name']);
        $gatewayConfig->setConfig($data['Gateway_config']['Config']);

        $paymentMethod->setGatewayConfig($gatewayConfig);
    }

    private function getPaymentMethod(string $code): PaymentMethodInterface
    {
        /** @var PaymentMethodInterface|null $paymentMethod */
        $paymentMethod = $this->repository->findOneBy(['code' => $code]);

        if ($paymentMethod === null) {
            /** @var PaymentMethodInterface $paymentMethod */
            $paymentMethod = $this->factory->createNew();
            $paymentMethod->setCode($code);

            $this->manager->persist($paymentMethod);
        }

        return $paymentMethod;
    }
}

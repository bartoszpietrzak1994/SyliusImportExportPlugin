<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Processor;

use Doctrine\Common\Persistence\ObjectManager;
use FriendsOfSylius\SyliusImportExportPlugin\Formatter\DateTimeFormatterInterface;
use Sylius\Component\Core\Model\Address;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\Customer;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItem;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\OrderItemUnit;
use Sylius\Component\Core\Model\Payment;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Model\Shipment;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\Component\Core\Model\ShippingMethodInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Order\Model\AdjustableInterface;
use Sylius\Component\Order\Model\Adjustment;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class OrderProcessor implements ResourceProcessorInterface
{
    /** @var FactoryInterface */
    private $orderFactory;

    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var RepositoryInterface */
    private $paymentMethodRepository;

    /** @var RepositoryInterface */
    private $shippingMethodRepository;

    /** @var RepositoryInterface */
    private $channelRepository;

    /** @var RepositoryInterface */
    private $productRepository;

    /** @var RepositoryInterface */
    private $productVariantRepository;

    /** @var ObjectManager */
    private $manager;

    /** @var DateTimeFormatterInterface */
    private $dateTimeFormatter;

    /** @var MetadataValidatorInterface */
    private $metadataValidator;

    /** @var string[] */
    private $headerKeys;

    public function __construct(
        FactoryInterface $orderFactory,
        OrderRepositoryInterface $orderRepository,
        RepositoryInterface $paymentMethodRepository,
        RepositoryInterface $shippingMethodRepository,
        RepositoryInterface $channelRepository,
        RepositoryInterface $productRepository,
        RepositoryInterface $productVariantRepository,
        ObjectManager $manager,
        DateTimeFormatterInterface $dateTimeFormatter,
        MetadataValidatorInterface $metadataValidator,
        array $headerKeys
    ) {
        $this->orderFactory = $orderFactory;
        $this->orderRepository = $orderRepository;
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->shippingMethodRepository = $shippingMethodRepository;
        $this->channelRepository = $channelRepository;
        $this->productRepository = $productRepository;
        $this->productVariantRepository = $productVariantRepository;
        $this->manager = $manager;
        $this->dateTimeFormatter = $dateTimeFormatter;
        $this->metadataValidator = $metadataValidator;
        $this->headerKeys = $headerKeys;
    }


    public function process(array $data): void
    {
        $this->metadataValidator->validateHeaders($this->headerKeys, $data);

        /** @var OrderInterface $order */
        $order = $this->getOrder($data['Number']);

        /** @var CustomerInterface $customer */
        $customer = $this->getCustomer($data['Customer']);

        $order->setCreatedAt($this->dateTimeFormatter->toDateTime($data['CreatedAt']));
        $order->setUpdatedAt($this->dateTimeFormatter->toDateTime($data['UpdatedAt']));
        $order->setCheckoutCompletedAt($this->dateTimeFormatter->toDateTime($data['CheckoutCompletedAt']));

        $order->setCurrencyCode($data['CurrencyCode']);
        $order->setLocaleCode($data['LocaleCode']);
        $order->setNotes($data['Notes']);
        $order->setState($data['State']);
        $order->setCustomerIp($data['CustomerIp']);
        $order->setTokenValue($data['TokenValue']);
        $order->setCustomer($customer);
        $order->setPaymentState($data['PaymentState']);
        $order->setShippingState($data['ShippingState']);

        $order->setChannel($this->getChannel($data['Channel']));
        $order->setShippingAddress($this->getAddress($data['ShippingAddress'], $customer));
        $order->setBillingAddress($this->getAddress($data['BillingAddress'], $customer));
        $order->setCheckoutState($data['CheckoutState']);

        $this->addItemsToOrder($order, $data['Items']);

        /** @var array $adjustment */
        foreach ($data['Adjustments'] as $adjustment) {
            $this->getAdjustment($adjustment, $order);
        }

        /** @var array $shipment */
        foreach ($data['Shipments'] as $shipment) {
            $order->addShipment($this->getShipment($shipment, $order));
        }

        /** @var array $payment */
        foreach ($data['Payments'] as $payment) {
            $order->addPayment($this->getPayment($payment, $order));
        }
    }

    private function getOrder(string $number): OrderInterface
    {
        /** @var OrderInterface|null $order */
        $order = $this->orderRepository->findOneByNumber($number);

        if ($order === null)
        {
            /** @var OrderInterface $order */
            $order = $this->orderFactory->createNew();

            $this->manager->persist($order);
        }

        return $order;
    }

    private function getCustomer(array $parameters): CustomerInterface
    {
        /** @var CustomerInterface $customer */
        $customer = new Customer();

        $customer->setEmail($parameters['Email']);
        $customer->setEmailCanonical($parameters['EmailCanonical']);
        $customer->setBirthday($this->dateTimeFormatter->toDateTime($parameters['Birthday']));
        $customer->setFirstName($parameters['FirstName']);
        $customer->setLastName($parameters['LastName']);
        $customer->setGender($parameters['Gender']);
        $customer->setPhoneNumber($parameters['PhoneNumber']);
        $customer->setSubscribedToNewsletter($parameters['SubscribedToNewsletter']);

        $this->manager->persist($customer);

        $customer->setDefaultAddress($this->getAddress($parameters['DefaultAddress'], $customer));

        return $customer;
    }

    private function getAddress(array $parameters, CustomerInterface $customer): AddressInterface
    {
        /** @var AddressInterface $address */
        $address = new Address();

        $address->setPhoneNumber($parameters['PhoneNumber']);
        $address->setFirstName($parameters['FirstName']);
        $address->setLastName($parameters['LastName']);
        $address->setCity($parameters['City']);
        $address->setStreet($parameters['Street']);
        $address->setPostcode($parameters['Postcode']);
        $address->setCountryCode($parameters['CountryCode']);
        $address->setCompany($parameters['Company']);
        $address->setProvinceCode($parameters['ProvinceCode']);
        $address->setProvinceName($parameters['ProvinceName']);
        $address->setCustomer($customer);

        $this->manager->persist($address);

        return $address;
    }

    private function getChannel(?string $code): ?ChannelInterface
    {
        /** @var ChannelInterface|null $channel */
        $channel = $this->channelRepository->findOneBy(['code' => $code]);

        return $channel;
    }

    private function addItemsToOrder(OrderInterface $order, array $orderItemsParameters): void
    {
        /** @var array $orderItemParameters */
        foreach ($orderItemsParameters as $orderItemParameters) {
            /** @var OrderItemInterface $orderItem */
            $orderItem = new OrderItem();
            $orderItem->setOrder($order);

            $orderItem->setImmutable($orderItemParameters['Immutable']);
            $orderItem->setUnitPrice($orderItemParameters['UnitPrice']);

            foreach ($orderItemParameters['Units'] as $orderItemUnit) {
                $orderItemUnit = new OrderItemUnit($orderItem);
                $orderItemUnit->setCreatedAt($this->dateTimeFormatter->toDateTime($orderItemUnit['CreatedAt']));
                $orderItemUnit->setUpdatedAt($this->dateTimeFormatter->toDateTime($orderItemUnit['UpdatedAt']));
                $orderItemUnit->setShipment($this->getShipment($orderItemUnit['Shipment'], $order));

                foreach ($orderItemUnit['Adjustments'] as $adjustment) {
                    $orderItemUnit->addAdjustment($this->getAdjustment($adjustment, $orderItemUnit));
                }

                $orderItem->addUnit($orderItemUnit);
            }

            foreach ($orderItemsParameters['Adjustments'] as $adjustment) {
                $orderItem->addAdjustment($this->getAdjustment($adjustment, $orderItem));
            }

            $orderItem->setVariant($this->getProductVariant($orderItemParameters['Variant']));

            $order->addItem($orderItem);
        }
    }

    private function getAdjustment(array $parameters, AdjustableInterface $adjustable): AdjustmentInterface
    {
        /** @var AdjustmentInterface $adjustment */
        $adjustment = new Adjustment();

        $adjustment->setCreatedAt($this->dateTimeFormatter->toDateTime($parameters['CreatedAt']));
        $adjustment->setUpdatedAt($this->dateTimeFormatter->toDateTime($parameters['UpdatedAt']));
        $adjustment->setAmount($parameters['Amount']);
        $adjustment->setLabel($parameters['Label']);
        $adjustment->setType($parameters['Type']);
        $adjustment->setAdjustable($adjustable);
        $adjustment->setNeutral($parameters['Neutral']);
        $adjustment->setOriginCode($parameters['OriginCode']);

        $this->manager->persist($adjustment);

        return $adjustment;
    }

    private function getShipment(array $parameters, OrderInterface $order): ShipmentInterface
    {
        /** @var ShipmentInterface $shipment */
        $shipment = new Shipment();

        $shipment->setCreatedAt($this->dateTimeFormatter->toDateTime($parameters['CreatedAt']));
        $shipment->setUpdatedAt($this->dateTimeFormatter->toDateTime($parameters['UpdatedAt']));
        $shipment->setState($parameters['State']);
        $shipment->setTracking($parameters['Tracking']);
        $shipment->setMethod($this->getShippingMethod($parameters['ShippingMethod']));
        $shipment->setOrder($order);

        $this->manager->persist($shipment);

        return $shipment;
    }

    private function getPayment(array $parameters, OrderInterface $order): PaymentInterface
    {
        /** @var PaymentInterface $payment */
        $payment = new Payment();
        $payment->setOrder($order);

        $payment->setCreatedAt($this->dateTimeFormatter->toDateTime($parameters['CreatedAt']));
        $payment->setUpdatedAt($this->dateTimeFormatter->toDateTime($parameters['UpdatedAt']));
        $payment->setMethod($this->getPaymentMethod($parameters['PaymentMethod']));
        $payment->setState($parameters['State']);
        $payment->setAmount($parameters['Amount']);
        $payment->setCurrencyCode($parameters['CurrencyCode']);
        $payment->setDetails($parameters['Details']);

        $this->manager->persist($payment);

        return $payment;
    }

    private function getProductVariant(?string $code): ?ProductVariantInterface
    {
        /** @var ProductVariantInterface|null $productVariant */
        $productVariant = $this->productVariantRepository->findOneBy(['code' => $code]);

        return $productVariant;
    }

    private function getShippingMethod(?string $code): ?ShippingMethodInterface
    {
        /** @var ShippingMethodInterface|null $shippingMethod */
        $shippingMethod = $this->shippingMethodRepository->findOneBy(['code' => $code]);

        return $shippingMethod;
    }

    private function getPaymentMethod(?string $code): ?PaymentMethodInterface
    {
        /** @var PaymentMethodInterface|null $paymentMethod */
        $paymentMethod = $this->paymentMethodRepository->findOneBy(['code' => $code]);

        return $paymentMethod;
    }
}

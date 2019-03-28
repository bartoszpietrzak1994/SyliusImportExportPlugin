<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Formatter\DateTimeFormatterInterface;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\OrderItemUnitInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PromotionCouponInterface;
use Sylius\Component\Order\Model\AdjustableInterface;
use Sylius\Component\Resource\Model\CodeAwareInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Shipping\Model\ShipmentInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

final class OrderResourcePlugin extends ResourcePlugin
{
    /** @var DateTimeFormatterInterface */
    private $dateTimeFormatter;

    public function __construct(
        RepositoryInterface $repository,
        PropertyAccessorInterface $propertyAccessor,
        EntityManagerInterface $entityManager,
        DateTimeFormatterInterface $dateTimeFormatter
    ) {
        parent::__construct($repository, $propertyAccessor, $entityManager);

        $this->dateTimeFormatter = $dateTimeFormatter;
    }

    /**
     * {@inheritdoc}
     */
    public function init(array $idsToExport): void
    {
        parent::init($idsToExport);

        /** @var OrderInterface $resource */
        foreach ($this->resources as $resource) {
            if (!$resource->getItems()->isEmpty()) {
                $this->addOrderItems($resource);
            }

            if (!$resource->getPayments()->isEmpty()) {
                $this->addPayments($resource);
            }

            if (!$resource->getShipments()->isEmpty()) {
                $this->addShipments($resource);
            }

            if (!$resource->getAdjustments()->isEmpty()) {
                $this->addAdjustments($resource);
            }

            if (null !== $resource->getCustomer()) {
                $this->addCustomer($resource);
            }

            if (null !== $resource->getBillingAddress()) {
                $this->addBillingAddress($resource);
            }

            if (null !== $resource->getShippingAddress()) {
                $this->addShippingAddress($resource);
            }

            $this->addDataForResource($resource, 'Channel', $this->getPossibleResourceCodeValue($resource->getChannel()));
            $this->addDataForResource($resource, 'CheckoutCompletedAt', $this->getFormattedDateTime($resource->getCheckoutCompletedAt()));
            $this->addDataForResource(
                $resource,
                'CreatedAt',
                null !== $resource->getCreatedAt() ? $this->dateTimeFormatter->toString($resource->getCreatedAt()) : null
            );
            $this->addDataForResource(
                $resource,
                'UpdatedAt',
                null !== $resource->getUpdatedAt() ? $this->dateTimeFormatter->toString($resource->getUpdatedAt()) : null
            );
            $this->addDataForResource($resource, 'Number', $resource->getNumber());
        }
    }

    private function addOrderItems(OrderInterface $order): void
    {
        $orderItemsToExport = [];

        /** @var Collection|OrderItemInterface[] $orderItems */
        $orderItems = $order->getItems();

        foreach ($orderItems as $orderItem) {
            $orderItemsToExport[] = [
                'Quantity' => $orderItem->getQuantity(),
                'UnitPrice' => $orderItem->getUnitPrice(),
                'Total' => $orderItem->getTotal(),
                'Immutable' => $orderItem->isImmutable(),
                'Units' => $this->getOrderItemUnits($orderItem),
                'Adjustments' => $this->getAdjustments($orderItem),
                'Product' => $this->getPossibleResourceCodeValue($orderItem->getProduct()),
                'Variant' => $this->getPossibleResourceCodeValue($orderItem->getVariant()),
            ];
        }

        $this->addDataForResource($order, 'Items', $orderItemsToExport);
    }

    private function getOrderItemUnits(OrderItemInterface $orderItem): array
    {
        $orderItemUnitsToExport = [];

        /** @var Collection|OrderItemUnitInterface[] $orderItemUnits */
        $orderItemUnits = $orderItem->getUnits();

        foreach ($orderItemUnits as $orderItemUnit) {
            $orderItemUnitsToExport[] = [
                'CreatedAt' => $this->getFormattedDateTime($orderItemUnit->getCreatedAt()),
                'UpdatedAt' => $this->getFormattedDateTime($orderItemUnit->getUpdatedAt()),
                'Total' => $orderItemUnit->getTotal(),
                'Shipment' => $this->getShipmentAsArray($orderItemUnit->getShipment()),
                'Adjustments' => $this->getAdjustments($orderItemUnit),
            ];
        }

        return $orderItemUnitsToExport;
    }

    private function getAdjustments(AdjustableInterface $adjustable): array
    {
        $adjustmentsToExport = [];

        /** @var Collection|AdjustmentInterface[] $adjustments */
        $adjustments = $adjustable->getAdjustments();

        foreach ($adjustments as $adjustment) {
            $adjustmentsToExport[] = [
                'CreatedAt' => $this->getFormattedDateTime($adjustment->getCreatedAt()),
                'UpdatedAt' => $this->getFormattedDateTime($adjustment->getUpdatedAt()),
                'Type' => $adjustment->getType(),
                'Label' => $adjustment->getLabel(),
                'Amount' => $adjustment->getAmount(),
                'Neutral' => $adjustment->isNeutral(),
                'Locked' => $adjustment->isLocked(),
                'Charge' => $adjustment->isCharge(),
                'Credit' => $adjustment->isCredit(),
                'OriginCode' => $adjustment->getOriginCode(),
            ];
        }

        return $adjustmentsToExport;
    }

    private function getShipmentAsArray(ShipmentInterface $shipment): array
    {
        return [
            'CreatedAt' => $this->getFormattedDateTime($shipment->getCreatedAt()),
            'UpdatedAt' => $this->getFormattedDateTime($shipment->getUpdatedAt()),
            'State' => $shipment->getState(),
            'Method' => $this->getPossibleResourceCodeValue($shipment->getMethod()),
            'Tracking' => $shipment->getTracking(),
            'Tracked' => $shipment->isTracked(),
        ];
    }

    private function addPayments(OrderInterface $order): void
    {
        $paymentsToExport = [];

        /** @var Collection|PaymentInterface[] $payments */
        $payments = $order->getPayments();

        foreach ($payments as $payment) {
            $paymentsToExport[] = [
                'CreatedAt' => $this->getFormattedDateTime($payment->getCreatedAt()),
                'UpdatedAt' => $this->getFormattedDateTime($payment->getUpdatedAt()),
                'PaymentMethod' => $this->getPossibleResourceCodeValue($payment->getMethod()),
                'State' => $payment->getState(),
                'CurrencyCode' => $payment->getCurrencyCode(),
                'Amount' => $payment->getAmount(),
                'Details' => $payment->getDetails(),
            ];
        }

        $this->addDataForResource($order, 'Payments', $paymentsToExport);
    }

    private function addShipments(OrderInterface $order): void
    {
        $shipmentsToExport = [];

        /** @var Collection|ShipmentInterface[] $payments */
        $shipments = $order->getShipments();

        foreach ($shipments as $shipment) {
            $shipmentsToExport[] = $this->getShipmentAsArray($shipment);
        }

        $this->addDataForResource($order, 'Shipments', $shipmentsToExport);
    }

    private function addCustomer(OrderInterface $order): void
    {
        /** @var CustomerInterface $customer */
        $customer = $order->getCustomer();

        $customerToExport = [
            'CreatedAt' => $this->getFormattedDateTime($customer->getCreatedAt()),
            'UpdatedAt' => $this->getFormattedDateTime($customer->getUpdatedAt()),
            'Email' => $customer->getEmail(),
            'EmailCanonical' => $customer->getEmailCanonical(),
            'FirstName' => $customer->getFirstName(),
            'LastName' => $customer->getLastName(),
            'Birthday' => null !== $customer->getBirthday() ? $this->dateTimeFormatter->toString($customer->getBirthday()) : null,
            'Gender' => $customer->getGender(),
            'PhoneNumber' => $customer->getPhoneNumber(),
            'SubscribedToNewsletter' => $customer->isSubscribedToNewsletter(),
            'DefaultAddress' => $this->getAddressAsArray($customer->getDefaultAddress()),
        ];

        $this->addDataForResource($order, 'Customer', $customerToExport);
    }

    private function addBillingAddress(OrderInterface $order): void
    {
        /** @var AddressInterface $address */
        $address = $order->getBillingAddress();

        $addressToExport = $this->getAddressAsArray($address);

        $this->addDataForResource($order, 'BillingAddress', $addressToExport);
    }

    private function addShippingAddress(OrderInterface $order): void
    {
        /** @var AddressInterface $address */
        $address = $order->getShippingAddress();

        $addressToExport = $this->getAddressAsArray($address);

        $this->addDataForResource($order, 'ShippingAddress', $addressToExport);
    }

    private function getAddressAsArray(?AddressInterface $address): array
    {
        if (null === $address) {
            return [];
        }

        return [
            'FirstName' => $address->getFirstName(),
            'LastName' => $address->getLastName(),
            'PhoneNumber' => $address->getPhoneNumber(),
            'Company' => $address->getCompany(),
            'CountryCode' => $address->getCountryCode(),
            'ProvinceCode' => $address->getProvinceCode(),
            'ProvinceName' => $address->getProvinceName(),
            'Street' => $address->getStreet(),
            'City' => $address->getCity(),
            'Postcode' => $address->getPostcode(),
        ];
    }

    private function addAdjustments(OrderInterface $order): void
    {
        $adjustmentsToExport = $this->getAdjustments($order);

        $this->addDataForResource($order, 'Adjustments', $adjustmentsToExport);
    }

    private function addPromotionCoupon(OrderInterface $order): void
    {
        /** @var PromotionCouponInterface $promotionCoupon */
        $promotionCoupon = $order->getPromotionCoupon();

        $promotionCouponToExport = [
            'CreatedAt' => $this->getFormattedDateTime($promotionCoupon->getCreatedAt()),
            'UpdatedAt' => $this->getFormattedDateTime($promotionCoupon->getUpdatedAt()),
            'ExpiresAt' => $this->getFormattedDateTime($promotionCoupon->getExpiresAt()),
            'UsageLimit' => $promotionCoupon->getUsageLimit(),
            'Used' => $promotionCoupon->getUsed(),
            'PerCustomerUsageLimit' => $promotionCoupon->getPerCustomerUsageLimit(),
            'Promotion' => $this->getPossibleResourceCodeValue($promotionCoupon->getPromotion()),
        ];

        $this->addDataForResource($order, 'PromotionCoupon', $promotionCouponToExport);
    }

    private function getFormattedDateTime(?\DateTimeInterface $dateTime): ?string
    {
        return null !== $dateTime ? $this->dateTimeFormatter->toString($dateTime) : null;
    }

    private function getPossibleResourceCodeValue(?CodeAwareInterface $codeAware): ?string
    {
        return null !== $codeAware ? $codeAware->getCode() : null;
    }
}

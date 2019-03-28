<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin\Order;

use Doctrine\Common\Collections\Collection;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin\ResourcePlugin;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\OrderItemUnitInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PromotionCouponInterface;
use Sylius\Component\Core\Model\PromotionInterface;
use Sylius\Component\Order\Model\AdjustableInterface;
use Sylius\Component\Promotion\Model\PromotionActionInterface;
use Sylius\Component\Promotion\Model\PromotionRuleInterface;
use Sylius\Component\Resource\Model\CodeAwareInterface;
use Sylius\Component\Shipping\Model\ShipmentInterface;

final class OrderResourcePlugin extends ResourcePlugin
{
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

            if (!$resource->getPromotions()->isEmpty()) {
                $this->addPromotions($resource);
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

            if (null !== $resource->getPromotionCoupon()) {
                $this->addPromotionCoupon($resource);
            }

            $this->addDataForResource($resource, 'Channel', $this->getPossibleResourceCodeValue($resource->getChannel()));
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
                'Type' => $adjustment->getType(),
                'Label' => $adjustment->getLabel(),
                'Amount' => $adjustment->getAmount(),
                'Neutral' => $adjustment->isNeutral(),
                'Locked' => $adjustment->isLocked(),
                'Charge' => $adjustment->isCharge(),
                'Credit' => $adjustment->isCredit(),
                'OriginCode' => $adjustment->getOriginCode()
            ];
        }

        return $adjustmentsToExport;
    }

    private function getShipmentAsArray(ShipmentInterface $shipment): array
    {
        return [
            'State' => $shipment->getState(),
            'Method' => $this->getPossibleResourceCodeValue($shipment->getMethod()),
            'Tracking' => $shipment->getTracking(),
            'Tracked' => $shipment->isTracked(),
        ];
    }

    private function addPromotions(OrderInterface $order): void
    {
        $promotionsToExport = [];

        /** @var Collection|PromotionInterface[] $adjustments */
        $promotions = $order->getPromotions();

        foreach ($promotions as $promotion) {
            $promotionsToExport[] = [
                'Name' => $promotion->getName(),
                'Description' => $promotion->getDescription(),
                'Priority' => $promotion->getPriority(),
                'Exclusive' => $promotion->isExclusive(),
                'UsageLimit' => $promotion->getUsageLimit(),
                'Used' => $promotion->getUsed(),
                'CouponBased' => $promotion->isCouponBased(),
                'Coupons' => $this->getPromotionCoupons($promotion),
                'Rules' => $this->getPromotionRules($promotion),
                'Actions' => $this->getPromotionActions($promotion),
            ];
        }

        $this->addDataForResource($order, 'Promotions', $promotionsToExport);
    }

    private function getPromotionCoupons(PromotionInterface $promotion): array
    {
        $promotionCouponsToExport = [];

        /** @var Collection|PromotionCouponInterface[] $adjustments */
        $promotionCoupons = $promotion->getCoupons();

        foreach ($promotionCoupons as $promotionCoupon) {
            $promotionCouponsToExport[] = [
                'UsageLimit' => $promotionCoupon->getUsageLimit(),
                'Used' => $promotionCoupon->getUsed(),
                'ExpiresAt' => null !== $promotionCoupon->getExpiresAt() ? $promotionCoupon->getExpiresAt()->format('Y-m-d H:i:s') : null,
                'Valid' => $promotionCoupon->isValid(),
            ];
        }

        return $promotionCouponsToExport;
    }

    private function getPromotionRules(PromotionInterface $promotion): array
    {
        $promotionRulesToExport = [];

        /** @var Collection|PromotionRuleInterface[] $adjustments */
        $promotionRules = $promotion->getRules();

        foreach ($promotionRules as $promotionRule) {
            $promotionRulesToExport[] = [
                'Type' => $promotionRule->getType(),
                'Configuration' => $promotionRule->getConfiguration(),
            ];
        }

        return $promotionRulesToExport;
    }

    private function getPromotionActions(PromotionInterface $promotion): array
    {
        $promotionActionsToExport = [];

        /** @var Collection|PromotionActionInterface[] $adjustments */
        $promotionActions = $promotion->getActions();

        foreach ($promotionActions as $promotionAction) {
            $promotionActionsToExport[] = [
                'Type' => $promotionAction->getType(),
                'Configuration' => $promotionAction->getConfiguration()
            ];
        }

        return $promotionActionsToExport;
    }

    private function addPayments(OrderInterface $order): void
    {
        $paymentsToExport = [];

        /** @var Collection|PaymentInterface[] $payments */
        $payments = $order->getPayments();

        foreach ($payments as $payment) {
            $paymentsToExport[] = [
                'PaymentMethod' => $this->getPossibleResourceCodeValue($payment->getMethod()),
                'State' => $payment->getState(),
                'CurrencyCode' => $payment->getCurrencyCode(),
                'Amount' => $payment->getAmount(),
                'Details' => $payment->getDetails()
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
            'Email' => $customer->getEmail(),
            'EmailCanonical' => $customer->getEmailCanonical(),
            'FirstName' => $customer->getFirstName(),
            'LastName' => $customer->getLastName(),
            'Birthday' => null !== $customer->getBirthday() ? $customer->getBirthday()->format('Y-m-d H:i:s'): null,
            'Gender' => $customer->getGender(),
            'Group' => null !== $customer->getGroup() ? $customer->getGroup()->getName() : null,
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
            'UsageLimit' => $promotionCoupon->getUsageLimit(),
            'Used' => $promotionCoupon->getUsed(),
            'ExpiresAt' => null !== $promotionCoupon->getExpiresAt() ? $promotionCoupon->getExpiresAt()->format('Y-m-d H:i:s') : null,
        ];

        $this->addDataForResource($order, 'PromotionCoupon', $promotionCouponToExport);
    }

    private function getPossibleResourceCodeValue(?CodeAwareInterface $codeAware): ?string
    {
        return null !== $codeAware ? $codeAware->getCode() : null;
    }
}

<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;

final class PaymentMethodResourcePlugin extends ResourcePlugin
{
    /**
     * {@inheritdoc}
     */
    public function init(array $idsToExport): void
    {
        parent::init($idsToExport);

        /** @var PaymentMethodInterface $resource */
        foreach ($this->resources as $resource) {
            if (null !== $resource->getGatewayConfig()) {
                $this->addDataForResource($resource, 'GatewayConfig', [
                    'GatewayName' => $resource->getGatewayConfig()->getGatewayName(),
                    'FactoryName' => $resource->getGatewayConfig()->getFactoryName(),
                    'Config' => $resource->getGatewayConfig()->getConfig(),
                ]);
            }

            $this->addDataForResource($resource, 'Channels', array_map(function (ChannelInterface $channel): ?string {
                return $channel->getCode();
            }, $resource->getChannels()->toArray()));
        }
    }
}

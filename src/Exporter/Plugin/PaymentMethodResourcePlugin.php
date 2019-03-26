<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

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
                $this->addDataForResource($resource, 'Gateway_config', [
                    'Gateway_name' => $resource->getGatewayConfig()->getGatewayName(),
                    'Factory_name' => $resource->getGatewayConfig()->getFactoryName(),
                    'Config' => $resource->getGatewayConfig()->getConfig(),
                ]);
            }
        }
    }
}

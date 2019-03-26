<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

use Sylius\Component\Addressing\Model\ZoneInterface;
use Sylius\Component\Addressing\Model\ZoneMember;

final class ZoneResourcePlugin extends ResourcePlugin
{
    /**
     * {@inheritdoc}
     */
    public function init(array $idsToExport): void
    {
        parent::init($idsToExport);

        /** @var ZoneInterface $resource */
        foreach ($this->resources as $resource) {
            $zoneMembers = [];

            /** @var ZoneMember $zoneMember */
            foreach ($resource->getMembers() as $zoneMember) {
                $zoneMembers[] = $zoneMember->getCode();
            }

            $this->addDataForResource($resource, 'Members', $zoneMembers);
        }
    }
}

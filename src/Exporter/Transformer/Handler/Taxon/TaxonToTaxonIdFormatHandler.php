<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer\Handler\Taxon;

use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer\Handler;
use Sylius\Component\Taxonomy\Model\Taxon;

final class TaxonToTaxonIdFormatHandler extends Handler
{
    protected function process($key, $value)
    {
        return $value->getId();
    }

    protected function allows($key, $value): bool
    {
        return $value instanceof Taxon;
    }
}

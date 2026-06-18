<?php

declare(strict_types=1);

namespace Kirstenroschanski\ContaoWiderrufBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Kirstenroschanski\ContaoWiderrufBundle\KirstenroschanskiContaoWiderrufBundle;

class Plugin implements BundlePluginInterface
{
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(KirstenroschanskiContaoWiderrufBundle::class)
                ->setLoadAfter([
                    ContaoCoreBundle::class,
                ])
                ->setReplace(['kirstenroschanski_widerruf']),
        ];
    }
}

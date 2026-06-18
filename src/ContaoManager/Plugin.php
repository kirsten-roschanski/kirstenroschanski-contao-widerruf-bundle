<?php

declare(strict_types=1);

namespace Kirstenroschanski\ContaoWiderrufBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Routing\RoutingPluginInterface;
use Kirstenroschanski\ContaoWiderrufBundle\KirstenroschanskiContaoWiderrufBundle;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class Plugin implements BundlePluginInterface, RoutingPluginInterface
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

    public function getRouteCollection(LoaderResolverInterface $resolver, KernelInterface $kernel)
    {
        return $resolver
            ->resolve(__DIR__.'/../../config/routes.php')
            ->load(__DIR__.'/../../config/routes.php')
        ;
    }
}

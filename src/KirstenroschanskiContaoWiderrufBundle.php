<?php

declare(strict_types=1);

namespace Kirstenroschanski\ContaoWiderrufBundle;

use Kirstenroschanski\ContaoWiderrufBundle\KirstenroschanskiWiderrufExtension as WiderrufExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class KirstenroschanskiContaoWiderrufBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function getContainerExtension(): WiderrufExtension
    {
        return new WiderrufExtension();
    }

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->setParameter(
            'contao.image.target_dir',
            '%kernel.project_dir%/public/assets/images/widerruf'
        );
    }
}
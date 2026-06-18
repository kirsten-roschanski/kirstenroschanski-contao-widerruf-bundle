<?php

declare(strict_types=1);

use Kirstenroschanski\ContaoWiderrufBundle\Controller\RevocationApiController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes
        ->add('widerruf_submit', '/_widerruf')
        ->controller([RevocationApiController::class, '__invoke'])
        ->methods(['POST'])
    ;
};
<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Siganushka\ApiFactory\Wechat\Configuration;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services()
        ->defaults()
            ->autowire()
            ->autoconfigure()
    ;

    $ref = new \ReflectionClass(Configuration::class);
    $services->load($ref->getNamespaceName().'\\', '../src/')
        ->exclude([
            '../src/{Configuration.php,OptionSet.php}',
            '../src/Miniapp/CrypterUtils.php',
            '../src/Message/Template.php',
        ]);
};

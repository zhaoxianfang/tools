<?php

namespace zxf\Symfony\Component\DependencyInjection\Loader\Configurator;

return function (ContainerConfigurator $c) {
    $c->services()
        ->set('service', \stdClass::class)
        ->factory('factory:method');
};

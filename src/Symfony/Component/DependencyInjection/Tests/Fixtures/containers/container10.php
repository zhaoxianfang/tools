<?php

require_once __DIR__.'/../includes/classes.php';

use zxf\Symfony\Component\DependencyInjection\ContainerBuilder;
use zxf\Symfony\Component\DependencyInjection\Reference;

$container = new ContainerBuilder();
$container->
    register('foo', 'FooClass')->
    addArgument(new Reference('bar'))
    ->setPublic(true)
;

return $container;

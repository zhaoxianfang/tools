<?php

use zxf\Symfony\Component\DependencyInjection\ContainerBuilder;
use zxf\Symfony\Component\DependencyInjection\Definition;
use zxf\Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use zxf\Symfony\Component\DependencyInjection\Reference;

$container = new ContainerBuilder();

$container->setParameter('env(BAR)', 'bar');

$container->register('foo', 'stdClass')->setPublic(true)
   ->addArgument(new Reference('bar_%env(BAR)%'))
   ->addArgument(array('baz_%env(BAR)%' => new Reference('baz_%env(BAR)%')));

$container->register('bar', 'stdClass')->setPublic(true)
   ->addArgument(new Reference('bar_%env(BAR)%'));

$container->register('bar_%env(BAR)%', 'stdClass')->setPublic(false);
$container->register('baz_%env(BAR)%', 'stdClass')->setPublic(false);

return $container;

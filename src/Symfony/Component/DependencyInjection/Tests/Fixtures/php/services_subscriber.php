<?php

use zxf\Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use zxf\Symfony\Component\DependencyInjection\ContainerInterface;
use zxf\Symfony\Component\DependencyInjection\Container;
use zxf\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use zxf\Symfony\Component\DependencyInjection\Exception\LogicException;
use zxf\Symfony\Component\DependencyInjection\Exception\RuntimeException;
use zxf\Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;

/**
 * This class has been auto-generated
 * by the Symfony Dependency Injection Component.
 *
 * @final since Symfony 3.3
 */
class ProjectServiceContainer extends Container
{
    private $parameters;
    private $targetDirs = array();

    public function __construct()
    {
        $this->services = array();
        $this->normalizedIds = array(
            'symfony\\component\\dependencyinjection\\tests\\fixtures\\customdefinition' => 'Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\CustomDefinition',
            'symfony\\component\\dependencyinjection\\tests\\fixtures\\testservicesubscriber' => 'Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\TestServiceSubscriber',
        );
        $this->methodMap = array(
            'Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\CustomDefinition' => 'getCustomDefinitionService',
            'Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\TestServiceSubscriber' => 'getTestServiceSubscriberService',
            'foo_service' => 'getFooServiceService',
        );
        $this->privates = array(
            'Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\CustomDefinition' => true,
        );

        $this->aliases = array();
    }

    public function getRemovedIds()
    {
        return array(
            'Psr\\Container\\ContainerInterface' => true,
            'Symfony\\Component\\DependencyInjection\\ContainerInterface' => true,
            'Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\CustomDefinition' => true,
            'service_locator.jmktfsv' => true,
            'service_locator.jmktfsv.foo_service' => true,
        );
    }

    public function compile()
    {
        throw new LogicException('You cannot compile a dumped container that was already compiled.');
    }

    public function isCompiled()
    {
        return true;
    }

    public function isFrozen()
    {
        @trigger_error(sprintf('The %s() method is deprecated since Symfony 3.3 and will be removed in 4.0. Use the isCompiled() method instead.', __METHOD__), E_USER_DEPRECATED);

        return true;
    }

    /**
     * Gets the public 'zxf\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber' shared service.
     *
     * @return \Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber
     */
    protected function getTestServiceSubscriberService()
    {
        return $this->services['zxf\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber'] = new \Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber();
    }

    /**
     * Gets the public 'foo_service' shared autowired service.
     *
     * @return \Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber
     */
    protected function getFooServiceService()
    {
        return $this->services['foo_service'] = new \Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber((new \Symfony\Component\DependencyInjection\ServiceLocator(array('Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\CustomDefinition' => function () {
            $f = function (\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition $v = null) { return $v; }; return $f(${($_ = isset($this->services['zxf\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition']) ? $this->services['zxf\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition'] : $this->services['zxf\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition'] = new \Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition()) && false ?: '_'});
        }, 'Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\TestServiceSubscriber' => function () {
            $f = function (\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber $v) { return $v; }; return $f(${($_ = isset($this->services['zxf\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber']) ? $this->services['zxf\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber'] : $this->services['zxf\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber'] = new \Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber()) && false ?: '_'});
        }, 'bar' => function () {
            $f = function (\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition $v) { return $v; }; return $f(${($_ = isset($this->services['zxf\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber']) ? $this->services['zxf\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber'] : $this->services['zxf\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber'] = new \Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber()) && false ?: '_'});
        }, 'baz' => function () {
            $f = function (\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition $v = null) { return $v; }; return $f(${($_ = isset($this->services['zxf\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition']) ? $this->services['zxf\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition'] : $this->services['zxf\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition'] = new \Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition()) && false ?: '_'});
        })))->withContext('foo_service', $this));
    }

    /**
     * Gets the private 'zxf\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition' shared service.
     *
     * @return \Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition
     */
    protected function getCustomDefinitionService()
    {
        return $this->services['zxf\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition'] = new \Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition();
    }
}

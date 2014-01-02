<?php

use Berny\Bundle\TagBundle\DependencyInjection\Compiler\TagInjectablePass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class InjectablePassTest extends \PHPUnit_Framework_TestCase
{
    public function test_set_injectable_as_dependency()
    {
        $cb = $this->getContainer(new TagInjectablePass(), array(
            'my_injectable' => $this->getInjectableDefinition()->addTag('tag.injectable', array(
                'tag' => 'injectable_aware',
                'method' => 'setInjectable'
            )),
            'my_dep_1' => $this->getInjectableAwareDefinition()->addTag('injectable_aware'),
            'my_dep_2' => $this->getInjectableAwareDefinition()->addTag('not_injectable_aware'),
            'my_dep_3' => $this->getInjectableAwareDefinition()->addTag('injectable_aware'),
        ));

        $dep1 = $cb->get('my_dep_1');
        $dep2 = $cb->get('my_dep_2');
        $dep3 = $cb->get('my_dep_3');

        $this->assertNull($dep2->getInjectable());
        $this->assertInstanceOf('StdClass', $dep1->getInjectable());
        $this->assertSame($dep1->getInjectable(), $dep3->getInjectable());
    }

    /**
     * @return Definition
     */
    protected function getInjectableDefinition()
    {
        return new Definition('StdClass');
    }

    /**
     * @return Definition
     */
    protected function getInjectableAwareDefinition()
    {
        return new Definition('MockedInjectableAwareService');
    }

    /**
     * @param CompilerPassInterface $compilerPass
     *
     * @param array $definitions
     *
     * @return ContainerBuilder
     */
    protected function getContainer(CompilerPassInterface $compilerPass, array $definitions)
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions($definitions);
        $compilerPass->process($containerBuilder);
        $containerBuilder->compile();

        return $containerBuilder;
    }
}

class MockedInjectableAwareService
{
    protected $injectable;

    public function setInjectable($injectable)
    {
        $this->injectable = $injectable;
    }

    public function getInjectable()
    {
        return $this->injectable;
    }
}

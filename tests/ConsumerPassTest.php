<?php

use Berny\Bundle\TagBundle\DependencyInjection\Compiler\TagConsumerPass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class ConsumerPassTest extends \PHPUnit_Framework_TestCase
{
    public function test_inject_selected_dependencies()
    {
        $cb = $this->getContainer(new TagConsumerPass(), array(
            'my_service' => $this->getConsumerDefinition()->addTag('tag.consumer', array(
                'tag' => 'dependency',
                'method' => 'addDependency'
            )),
            'my_dep_1' => $this->getDependencyDefinition()->addTag('dependency'),
            'my_dep_2' => $this->getDependencyDefinition()->addTag('not_a_dependency'),
            'my_dep_3' => $this->getDependencyDefinition()->addTag('dependency'),
        ));
        $dependencies = $cb->get('my_service')->getDependencies();

        $this->assertCount(2, $dependencies);
        $this->assertContainsOnlyInstancesOf('StdClass', $dependencies);
    }

    /**
     * @return Definition
     */
    protected function getConsumerDefinition()
    {
        return new Definition('MockedConsumerService');
    }

    /**
     * @return Definition
     */
    protected function getDependencyDefinition()
    {
        return new Definition('StdClass');
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

class MockedConsumerService
{
    protected $dependencies = array();

    public function addDependency($dependency)
    {
        $this->dependencies[] = $dependency;
    }

    public function getDependencies()
    {
        return $this->dependencies;
    }
}

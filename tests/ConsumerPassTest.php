<?php

/**
 * This file is part of the xphere/tag-bundle package
 *
 * (c) Berny Cantos <be@rny.cc>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

use xPheRe\Bundle\TagBundle\DependencyInjection\Compiler\TagConsumerPass;

/**
 * Class ConsumerPassTest
 *
 * @author Berny Cantos <be@rny.cc>
 */
class ConsumerPassTest extends \PHPUnit_Framework_TestCase
{
    public function test_inject_selected_dependencies()
    {
        $cb = $this->getContainer(new TagConsumerPass('tag.consumer'), array(
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

    public function test_custom_consumer_tag()
    {
        $consumerTagName = 'service.consumer';
        $definition = $this->getConsumerDefinition();
        $cb = $this->getContainer(new TagConsumerPass($consumerTagName), array(
            'my_service' => $definition->addTag($consumerTagName, array(
                'tag' => 'dependency',
                'method' => 'addDependency'
            )),
            'my_dep_1' => $this->getDependencyDefinition()->addTag('dependency'),
            'my_dep_2' => $this->getDependencyDefinition()->addTag('not_a_dependency'),
        ));
        $dependencies = $cb->get('my_service')->getDependencies();

        $this->assertCount(1, $dependencies);
        $this->assertContainsOnlyInstancesOf('StdClass', $dependencies);
        $this->assertCount(0, $definition->getArguments());
        $this->assertCount(1, $definition->getMethodCalls());
    }

    public function test_bulk_insert()
    {
        $consumerTagName = 'service.consumer';
        $definition = $this->getConsumerDefinition();
        $cb = $this->getContainer(new TagConsumerPass($consumerTagName), array(
            'my_service' => $definition->addTag($consumerTagName, array(
                'tag' => 'dependency',
                'method' => 'setDependencies',
                'bulk' => true,
            )),
            'my_dep_1' => $this->getDependencyDefinition()->addTag('dependency'),
            'my_dep_2' => $this->getDependencyDefinition()->addTag('not_a_dependency'),
            'my_dep_3' => $this->getDependencyDefinition()->addTag('dependency'),
        ));
        $dependencies = $cb->get('my_service')->getDependencies();

        $this->assertCount(2, $dependencies);
        $this->assertContainsOnlyInstancesOf('StdClass', $dependencies);
        $this->assertCount(0, $definition->getArguments());
        $this->assertCount(1, $definition->getMethodCalls());
    }

    public function test_constructor_insert()
    {
        $consumerTagName = 'service.consumer';
        $definition = $this->getConsumerDefinition();
        $cb = $this->getContainer(new TagConsumerPass($consumerTagName), array(
            'my_service' => $definition->addTag($consumerTagName, array(
                'tag' => 'dependency',
            )),
            'my_dep_1' => $this->getDependencyDefinition()->addTag('dependency'),
            'my_dep_2' => $this->getDependencyDefinition()->addTag('not_a_dependency'),
            'my_dep_3' => $this->getDependencyDefinition()->addTag('dependency'),
        ));
        $dependencies = $cb->get('my_service')->getDependencies();

        $this->assertCount(2, $dependencies);
        $this->assertContainsOnlyInstancesOf('StdClass', $dependencies);
        $this->assertCount(1, $definition->getArguments());
        $this->assertCount(0, $definition->getMethodCalls());
    }

    public function test_use_key()
    {
        $cb = $this->getContainer(new TagConsumerPass('tag.consumer'), array(
            'my_service' => $this->getConsumerDefinition()->addTag('tag.consumer', array(
                'tag' => 'dependency',
                'method' => 'addDependencyWithAlias',
                'key' => 'alias',
            )),
            'my_dep_1' => $this->getDependencyDefinition()->addTag('dependency', [ 'alias' => 'second', ]),
            'my_dep_2' => $this->getDependencyDefinition()->addTag('not_a_dependency'),
            'my_dep_3' => $this->getDependencyDefinition()->addTag('dependency', [ 'alias' => 'first', ]),
        ));
        $dependencies = $cb->get('my_service')->getDependencies();

        $this->assertCount(2, $dependencies);
        $this->assertContainsOnlyInstancesOf('StdClass', $dependencies);
        $this->assertArrayHasKey('first', $dependencies);
        $this->assertArrayHasKey('second', $dependencies);
    }

    public function test_bulk_use_key()
    {
        $cb = $this->getContainer(new TagConsumerPass('tag.consumer'), array(
            'my_service' => $this->getConsumerDefinition()->addTag('tag.consumer', array(
                'tag' => 'dependency',
                'method' => 'setDependencies',
                'bulk' => true,
                'key' => 'alias',
            )),
            'my_dep_1' => $this->getDependencyDefinition()->addTag('dependency', [ 'alias' => 'second', ]),
            'my_dep_2' => $this->getDependencyDefinition()->addTag('not_a_dependency'),
            'my_dep_3' => $this->getDependencyDefinition()->addTag('dependency', [ 'alias' => 'first', ]),
        ));
        $dependencies = $cb->get('my_service')->getDependencies();

        $this->assertCount(2, $dependencies);
        $this->assertContainsOnlyInstancesOf('StdClass', $dependencies);
        $this->assertArrayHasKey('first', $dependencies);
        $this->assertArrayHasKey('second', $dependencies);
    }

    public function test_constructor_use_key()
    {
        $cb = $this->getContainer(new TagConsumerPass('tag.consumer'), array(
            'my_service' => $this->getConsumerDefinition()->addTag('tag.consumer', array(
                'tag' => 'dependency',
                'key' => 'alias',
            )),
            'my_dep_1' => $this->getDependencyDefinition()->addTag('dependency', [ 'alias' => 'second', ]),
            'my_dep_2' => $this->getDependencyDefinition()->addTag('not_a_dependency'),
            'my_dep_3' => $this->getDependencyDefinition()->addTag('dependency', [ 'alias' => 'first', ]),
        ));
        $dependencies = $cb->get('my_service')->getDependencies();

        $this->assertCount(2, $dependencies);
        $this->assertContainsOnlyInstancesOf('StdClass', $dependencies);
        $this->assertArrayHasKey('first', $dependencies);
        $this->assertArrayHasKey('second', $dependencies);
    }

    public function test_service_names_when_reference_is_false()
    {
        $cb = $this->getContainer(new TagConsumerPass('tag.consumer'), array(
            'my_service' => $this->getConsumerDefinition()->addTag('tag.consumer', array(
                'tag' => 'dependency',
                'key' => 'alias',
                'reference' => false,
            )),
            'my_dep_1' => $this->getDependencyDefinition()->addTag('dependency', [ 'alias' => 'second', ]),
            'my_dep_2' => $this->getDependencyDefinition()->addTag('not_a_dependency'),
            'my_dep_3' => $this->getDependencyDefinition()->addTag('dependency', [ 'alias' => 'first', ]),
        ));
        $dependencies = $cb->get('my_service')->getDependencies();

        $this->assertCount(2, $dependencies);
        $this->assertContainsOnly('string', $dependencies);
        $this->assertContains('my_dep_1', $dependencies);
        $this->assertContains('my_dep_3', $dependencies);
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

    public function __construct(array $dependencies = array())
    {
        $this->setDependencies($dependencies);
    }

    public function addDependency($dependency)
    {
        $this->dependencies[] = $dependency;
    }

    public function addDependencyWithAlias($dependency, $alias)
    {
        $this->dependencies[$alias] = $dependency;
    }

    public function setDependencies(array $dependencies)
    {
        $this->dependencies = $dependencies;
    }

    public function getDependencies()
    {
        return $this->dependencies;
    }
}

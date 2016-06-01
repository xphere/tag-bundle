<?php

/**
 * This file is part of the xphere/tag-bundle package
 *
 * (c) Berny Cantos <be@rny.cc>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
    const TAG = 'service.consumer';

    public function test_inject_selected_dependencies()
    {
        $this
            ->withConsumer('my_service', [
                'tag' => 'dependency',
                'method' => 'addDependency',
            ])
            ->withService('my_dep_1', 'dependency')
            ->withService('my_dep_2', 'not_a_dependency')
            ->withService('my_dep_3', 'dependency')
            ->withService('my_dep_4')
            ->compile();

        $dependencies = $this->getService('my_service')->getDependencies();

        $this->assertCount(2, $dependencies);
        $this->assertContainsOnlyInstancesOf(MockedDependency::class, $dependencies);
        $this->assertCount(0, $this->consumer->getArguments());
        $this->assertCount(2, $this->consumer->getMethodCalls());
    }

    public function test_bulk_insert()
    {
        $this
            ->withConsumer('my_service', [
                'tag' => 'dependency',
                'method' => 'setDependencies',
                'bulk' => true,
            ])
            ->withService('my_dep_1', 'dependency')
            ->withService('my_dep_2', 'not_a_dependency')
            ->withService('my_dep_3', 'dependency')
            ->withService('my_dep_4')
            ->compile();

        $dependencies = $this->getService('my_service')->getDependencies();

        $this->assertCount(2, $dependencies);
        $this->assertContainsOnlyInstancesOf(MockedDependency::class, $dependencies);
        $this->assertCount(0, $this->consumer->getArguments());
        $this->assertCount(1, $this->consumer->getMethodCalls());
    }

    public function test_constructor_insert()
    {
        $this
            ->withConsumer('my_service', [
                'tag' => 'dependency',
            ])
            ->withService('my_dep_1', 'dependency')
            ->withService('my_dep_2', 'not_a_dependency')
            ->withService('my_dep_3', 'dependency')
            ->withService('my_dep_4')
            ->compile();

        $dependencies = $this->getService('my_service')->getDependencies();

        $this->assertCount(2, $dependencies);
        $this->assertContainsOnlyInstancesOf(MockedDependency::class, $dependencies);
        $this->assertCount(1, $this->consumer->getArguments());
        $this->assertCount(0, $this->consumer->getMethodCalls());
    }

    public function test_use_key()
    {
        $this
            ->withConsumer('my_service', [
                'tag' => 'dependency',
                'method' => 'addDependencyWithAlias',
                'key' => 'alias',
            ])
            ->withService('my_dep_1', 'dependency', [ 'alias' => 'second', ])
            ->withService('my_dep_2', 'not_a_dependency', [ 'alias' => 'third', ])
            ->withService('my_dep_3', 'dependency', [ 'alias' => 'first', ])
            ->withService('my_dep_4')
            ->compile();

        $dependencies = $this->getService('my_service')->getDependencies();

        $this->assertCount(2, $dependencies);
        $this->assertContainsOnlyInstancesOf(MockedDependency::class, $dependencies);
        $this->assertArrayHasKey('first', $dependencies);
        $this->assertArrayHasKey('second', $dependencies);
        $this->assertCount(0, $this->consumer->getArguments());
        $this->assertCount(2, $this->consumer->getMethodCalls());
    }

    public function test_bulk_use_key()
    {
        $this
            ->withConsumer('my_service', [
                'tag' => 'dependency',
                'method' => 'setDependencies',
                'bulk' => true,
                'key' => 'alias',
            ])
            ->withService('my_dep_1', 'dependency', [ 'alias' => 'second', ])
            ->withService('my_dep_2', 'not_a_dependency', [ 'alias' => 'third', ])
            ->withService('my_dep_3', 'dependency', [ 'alias' => 'first', ])
            ->withService('my_dep_4')
            ->compile();

        $dependencies = $this->getService('my_service')->getDependencies();

        $this->assertCount(2, $dependencies);
        $this->assertContainsOnlyInstancesOf(MockedDependency::class, $dependencies);
        $this->assertArrayHasKey('first', $dependencies);
        $this->assertArrayHasKey('second', $dependencies);
        $this->assertCount(0, $this->consumer->getArguments());
        $this->assertCount(1, $this->consumer->getMethodCalls());
    }

    public function test_constructor_use_key()
    {
        $this
            ->withConsumer('my_service', [
                'tag' => 'dependency',
                'key' => 'alias',
            ])
            ->withService('my_dep_1', 'dependency', [ 'alias' => 'second', ])
            ->withService('my_dep_2', 'not_a_dependency', [ 'alias' => 'third', ])
            ->withService('my_dep_3', 'dependency', [ 'alias' => 'first', ])
            ->withService('my_dep_4')
            ->compile();

        $dependencies = $this->getService('my_service')->getDependencies();

        $this->assertCount(2, $dependencies);
        $this->assertContainsOnlyInstancesOf(MockedDependency::class, $dependencies);
        $this->assertArrayHasKey('first', $dependencies);
        $this->assertArrayHasKey('second', $dependencies);
        $this->assertCount(1, $this->consumer->getArguments());
        $this->assertCount(0, $this->consumer->getMethodCalls());
    }

    public function test_service_names_when_reference_is_false()
    {
        $this
            ->withConsumer('my_service', [
                'tag' => 'dependency',
                'key' => 'alias',
                'reference' => false,
            ])
            ->withService('my_dep_1', 'dependency', [ 'alias' => 'second', ])
            ->withService('my_dep_2', 'not_a_dependency', [ 'alias' => 'third', ])
            ->withService('my_dep_3', 'dependency', [ 'alias' => 'first', ])
            ->withService('my_dep_4')
            ->compile();

        $dependencies = $this->getService('my_service')->getDependencies();

        $this->assertCount(2, $dependencies);
        $this->assertContainsOnly('string', $dependencies);
        $this->assertContains('my_dep_1', $dependencies);
        $this->assertContains('my_dep_3', $dependencies);
    }

    public function test_pass_check_instances()
    {
        $this
            ->withConsumer('my_service', [
                'tag' => 'dependency',
                'instanceof' => MockedDependency::class,
            ])
            ->withService('my_dep_1', 'dependency')
            ->withService('my_dep_2', 'not_a_dependency', MockedAlternateDependency::class)
            ->withService('my_dep_3', 'dependency')
            ->withService('my_dep_4')
            ->compile();

        $dependencies = $this->getService('my_service')->getDependencies();

        $this->assertCount(2, $dependencies);
        $this->assertContainsOnlyInstancesOf(MockedDependency::class, $dependencies);
    }

    public function test_fail_check_instances()
    {
        $this->setExpectedException(UnexpectedValueException::class);

        $this
            ->withConsumer('my_service', [
                'tag' => 'dependency',
                'instanceof' => MockedDependency::class,
            ])
            ->withService('my_dep_1', 'dependency')
            ->withService('my_dep_2', 'not_a_dependency')
            ->withService('my_dep_3', 'dependency', MockedAlternateDependency::class)
            ->withService('my_dep_4')
            ->compile();
    }

    public function test_allow_multiple_services_with_same_key()
    {
        $this
            ->withConsumer('my_service', [
                'tag' => 'dependency',
                'key' => 'index',
                'multiple' => true,
            ])
            ->withService('my_dep_1', 'dependency', [
                'index' => 'first',
            ])
            ->withService('my_dep_2', 'not_a_dependency')
            ->withService('my_dep_3', 'dependency', MockedAlternateDependency::class, [
                'index' => 'first',
            ])
            ->withService('my_dep_4')
            ->compile();

        $dependencies = $this->getService('my_service')->getDependencies();

        $this->assertCount(1, $dependencies);
        $this->assertCount(2, $dependencies['first']);
    }

    public function test_multiple_holds_order()
    {
        $this
            ->withConsumer('my_service', [
                'tag' => 'dependency',
                'key' => 'index',
                'multiple' => true,
            ])
            ->withService('my_dep_1', 'dependency', [
                'index' => 'first',
            ])
            ->withService('my_dep_2', 'not_a_dependency')
            ->withService('my_dep_3', 'dependency', MockedAlternateDependency::class, [
                'index' => 'first',
                'order' => 1,
            ])
            ->withService('my_dep_4')
            ->compile();

        $dependencies = $this->getService('my_service')->getDependencies();

        $this->assertInstanceOf(MockedAlternateDependency::class, $dependencies['first'][0]);
        $this->assertInstanceOf(MockedDependency::class, $dependencies['first'][1]);
    }

    public function test_multiple_even_with_single_dependency()
    {
        $this
            ->withConsumer('my_service', [
                'tag' => 'dependency',
                'key' => 'index',
                'multiple' => true,
            ])
            ->withService('my_dep_1', 'dependency', [
                'index' => 'first',
            ])
            ->withService('my_dep_2', 'not_a_dependency')
            ->withService('my_dep_3')
            ->compile();

        $dependencies = $this->getService('my_service')->getDependencies();

        $this->assertCount(1, $dependencies);
        $this->assertCount(1, $dependencies['first']);
        $this->assertInstanceOf(MockedDependency::class, $dependencies['first'][0]);
    }

    public function test_index_by_key_with_key_undefined()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        $this
            ->withConsumer('my_service', [
                'tag' => 'dependency',
                'index-by' => 'key',
            ])
            ->compile();
    }

    public function test_index_by_key()
    {
        $this
            ->withConsumer('my_service', [
                'tag' => 'dependency',
                'index-by' => 'key',
                'key' => 'index',
            ])
            ->withService('my_dep_1', 'dependency', [
                'index' => 'first',
            ])
            ->withService('my_dep_2', 'not_a_dependency')
            ->withService('my_dep_3', 'dependency', MockedAlternateDependency::class, [
                'index' => 'second',
            ])
            ->compile();

        $dependencies = $this->getService('my_service')->getDependencies();

        $this->assertCount(2, $dependencies);
        $this->assertInstanceOf(MockedDependency::class, $dependencies['first']);
        $this->assertInstanceOf(MockedAlternateDependency::class, $dependencies['second']);
    }

    public function test_index_by_key_multiple_services()
    {
        $this
            ->withConsumer('my_service', [
                'tag' => 'dependency',
                'index-by' => 'key',
                'key' => 'index',
                'multiple' => true,
            ])
            ->withService('my_dep_1', 'dependency', [
                'index' => 'first',
            ])
            ->withService('my_dep_2', 'not_a_dependency')
            ->withService('my_dep_3', 'dependency', MockedAlternateDependency::class, [
                'index' => 'second',
            ])
            ->compile();

        $dependencies = $this->getService('my_service')->getDependencies();

        $this->assertCount(2, $dependencies);
        $this->assertCount(1, $dependencies['first']);
        $this->assertCount(1, $dependencies['second']);
        $this->assertInstanceOf(MockedDependency::class, $dependencies['first'][0]);
        $this->assertInstanceOf(MockedAlternateDependency::class, $dependencies['second'][0]);
    }

    public function test_index_by_key_multiple_services_with_multiple_option_disabled()
    {
        $this
            ->withConsumer('my_service', [
                'tag' => 'dependency',
                'index-by' => 'key',
                'key' => 'index',
            ])
            ->withService('my_dep_1', 'dependency', [
                'index' => 'first',
            ])
            ->withService('my_dep_2', 'dependency', [
                'index' => 'second',
            ])
            ->withService('my_dep_3', 'not_a_dependency')
            ->withService('my_dep_4', 'dependency', MockedAlternateDependency::class, [
                'index' => 'second',
            ])
            ->compile();

        $dependencies = $this->getService('my_service')->getDependencies();

        $this->assertCount(2, $dependencies);
        $this->assertInstanceOf(MockedDependency::class, $dependencies['first']);
        $this->assertInstanceOf(MockedAlternateDependency::class, $dependencies['second']);
    }

    public function test_index_by_class()
    {
        $this
            ->withConsumer('my_service', [
                'tag' => 'dependency',
                'index-by' => 'class',
            ])
            ->withService('my_dep_1', 'dependency')
            ->withService('my_dep_2', 'not_a_dependency')
            ->withService('my_dep_3', 'dependency', MockedAlternateDependency::class)
            ->compile();

        $dependencies = $this->getService('my_service')->getDependencies();

        $this->assertCount(2, $dependencies);
        $this->assertInstanceOf(MockedDependency::class, $dependencies[MockedDependency::class]);
        $this->assertInstanceOf(MockedAlternateDependency::class, $dependencies[MockedAlternateDependency::class]);
    }

    public function test_index_by_class_with_multiple_option_active()
    {
        $this
            ->withConsumer('my_service', [
                'tag' => 'dependency',
                'index-by' => 'class',
                'multiple' => true,
            ])
            ->withService('my_dep_1', 'dependency')
            ->withService('my_dep_2', 'dependency')
            ->withService('my_dep_3', 'not_a_dependency')
            ->withService('my_dep_4', 'dependency', MockedAlternateDependency::class)
            ->compile();

        $dependencies = $this->getService('my_service')->getDependencies();

        $this->assertCount(2, $dependencies);
        $this->assertCount(2, $dependencies[MockedDependency::class]);
        $this->assertCount(1, $dependencies[MockedAlternateDependency::class]);
        $this->assertInstanceOf(MockedDependency::class, $dependencies[MockedDependency::class][0]);
        $this->assertInstanceOf(MockedDependency::class, $dependencies[MockedDependency::class][1]);
        $this->assertInstanceOf(MockedAlternateDependency::class, $dependencies[MockedAlternateDependency::class][0]);
    }

    public function test_index_by_class_with_multiple_option_disabled_and_multiple_same_classes()
    {
        $this
            ->withConsumer('my_service', [
                'tag' => 'dependency',
                'index-by' => 'class',
            ])
            ->withService('my_dep_1', 'dependency')
            ->withService('my_dep_2', 'dependency')
            ->withService('my_dep_3', 'not_a_dependency')
            ->withService('my_dep_4', 'dependency', MockedAlternateDependency::class)
            ->compile();

        $dependencies = $this->getService('my_service')->getDependencies();

        $this->assertCount(2, $dependencies);
        $this->assertInstanceOf(MockedDependency::class, $dependencies[MockedDependency::class]);
        $this->assertInstanceOf(MockedAlternateDependency::class, $dependencies[MockedAlternateDependency::class]);
    }

    public function test_constructor_without_bulk()
    {
        $this
            ->withConsumer('my_service', MockedNotBulkService::class, [
                'tag' => 'dependency',
                'bulk' => false,
            ])
            ->withService('my_dep_1', 'dependency', [ 'alias' => 'second', ])
            ->withService('my_dep_2', 'not_a_dependency', [ 'alias' => 'third', ])
            ->withService('my_dep_3', 'dependency', [ 'alias' => 'first', ])
            ->withService('my_dep_4')
            ->compile();

        $dependencies = $this->getService('my_service')->getDependencies();

        $this->assertCount(2, $dependencies);
        $this->assertContainsOnlyInstancesOf(MockedDependency::class, $dependencies);
        $this->assertCount(2, $this->consumer->getArguments());
        $this->assertCount(0, $this->consumer->getMethodCalls());
    }

    /** @var ContainerBuilder */
    private $container;

    /** @var Definition */
    private $consumer;

    /**
     * Get current container
     *
     * @return ContainerBuilder
     */
    private function getContainerBuilder()
    {
        if (!$this->container) {
            $compilerPass = new TagConsumerPass(self::TAG);
            $this->container = new ContainerBuilder();
            $this->container->addCompilerPass($compilerPass);
        }

        return $this->container;
    }

    /**
     * Create a tagged consumer
     *
     * @param string $serviceName
     * @param array $tagOptions
     * @param string $className
     *
     * @return static
     */
    private function withConsumer($serviceName, $className = MockedConsumerService::class, array $tagOptions = [])
    {
        if (is_array($className)) {
            $tagOptions = $className;
            $className = MockedConsumerService::class;
        }

        $cb = $this->getContainerBuilder();

        $definition = new Definition($className);
        $definition->addTag(self::TAG, $tagOptions);

        $cb->setDefinition($serviceName, $definition);

        $this->consumer = $definition;

        return $this;
    }

    /**
     * Create a (optionally tagged) service
     *
     * @param string $serviceName
     * @param string|null $tagName
     * @param array $tagAttributes
     * @param string $className
     *
     * @return static
     */
    private function withService($serviceName, $tagName = null, $className = MockedDependency::class, array $tagAttributes = [])
    {
        if (is_array($className)) {
            $tagAttributes = $className;
            $className = MockedDependency::class;
        }

        $cb = $this->getContainerBuilder();

        $definition = new Definition($className);
        if ($tagName) {
            $definition->addTag($tagName, $tagAttributes);
        }

        $cb->setDefinition($serviceName, $definition);

        return $this;
    }

    /**
     * Finish container building and compile
     */
    private function compile()
    {
        $cb = $this->getContainerBuilder();
        $cb->compile();
    }

    /**
     * Get a service from the container
     *
     * @param string $serviceId
     *
     * @return object
     */
    private function getService($serviceId)
    {
        return $this->getContainerBuilder()->get($serviceId);
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

class MockedNotBulkService extends MockedConsumerService
{
    public function __construct()
    {
        parent::__construct(func_get_args());
    }
}

class MockedDependency
{
}

class MockedAlternateDependency
{
}

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

use Xphere\Bundle\TagBundle\DependencyInjection\Compiler\TagInjectablePass;

/**
 * Class InjectablePassTest
 *
 * @author Berny Cantos <be@rny.cc>
 */
class InjectablePassTest extends \PHPUnit_Framework_TestCase
{
    const TAG = 'service.injectable';

    public function test_set_injectable_as_dependency()
    {
        $this
            ->withInjectable('my_injectable', [
                'tag' => 'injectable_aware',
                'method' => 'setInjectable',
            ])
            ->withService('my_dep_1', 'injectable_aware')
            ->withService('my_dep_2', 'not_injectable_aware')
            ->withService('my_dep_3', 'injectable_aware')
            ->withService('my_dep_4')
            ->compile();

        $dep1 = $this->getService('my_dep_1');
        $dep2 = $this->getService('my_dep_2');
        $dep3 = $this->getService('my_dep_3');

        $this->assertNull($dep2->getInjectable());
        $this->assertInstanceOf(MockedInjectable::class, $dep1->getInjectable());
        $this->assertSame($dep1->getInjectable(), $dep3->getInjectable());
    }

    public function test_custom_method_on_a_per_injectable_aware_basis()
    {
        $this
            ->withInjectable('my_injectable', [
                'tag' => 'injectable_aware',
                'method' => 'setInjectable',
            ])
            ->withService('my_dep_1', 'injectable_aware')
            ->withService('my_dep_2', 'not_injectable_aware')
            ->withAlternateService('my_dep_3', 'injectable_aware', [
                'method' => 'setCustomInjectableMethod',
            ])
            ->withService('my_dep_4')
            ->compile();

        $dep1 = $this->getService('my_dep_1');
        $dep3 = $this->getService('my_dep_3');

        $this->assertInstanceOf(MockedInjectable::class, $dep1->getInjectable());
        $this->assertSame($dep1->getInjectable(), $dep3->getInjectable());
    }

    /** @var ContainerBuilder */
    private $container;

    /** @var Definition */
    private $injectable;

    /**
     * Get current container
     *
     * @return ContainerBuilder
     */
    private function getContainerBuilder()
    {
        if (!$this->container) {
            $compilerPass = new TagInjectablePass(self::TAG);
            $this->container = new ContainerBuilder();
            $this->container->addCompilerPass($compilerPass);
        }

        return $this->container;
    }

    /**
     * Create a tagged injectable
     *
     * @param string $serviceName
     * @param array $tagOptions
     *
     * @return self
     */
    private function withInjectable($serviceName, array $tagOptions)
    {
        $cb = $this->getContainerBuilder();

        $definition = new Definition(MockedInjectable::class);
        $definition->addTag(self::TAG, $tagOptions);

        $cb->setDefinition($serviceName, $definition);

        $this->injectable = $definition;

        return $this;
    }

    /**
     * Create a (optionally tagged) service
     *
     * @param string $serviceName
     * @param string|null $tagName
     * @param array $tagAttributes
     *
     * @return self
     */
    private function withService($serviceName, $tagName = null, array $tagAttributes = [])
    {
        $cb = $this->getContainerBuilder();

        $definition = new Definition(MockedInjectableAwareService::class);
        if ($tagName) {
            $definition->addTag($tagName, $tagAttributes);
        }

        $cb->setDefinition($serviceName, $definition);

        return $this;
    }

    /**
     * Create an alternate (optionally tagged) service
     *
     * @param string $serviceName
     * @param string|null $tagName
     * @param array $tagAttributes
     *
     * @return self
     */
    private function withAlternateService($serviceName, $tagName = null, array $tagAttributes = [])
    {
        $cb = $this->getContainerBuilder();

        $definition = new Definition(MockedAlternateInjectableAwareService::class);
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

class MockedInjectable
{
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

class MockedAlternateInjectableAwareService
{
    protected $injectable;

    public function setCustomInjectableMethod($injectable)
    {
        $this->injectable = $injectable;
    }

    public function getInjectable()
    {
        return $this->injectable;
    }
}

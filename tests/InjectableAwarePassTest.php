<?php

/**
 * This file is part of the Berny\TagBundle package
 *
 * (c) Berny Cantos <be@rny.cc>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Berny\Bundle\TagBundle\DependencyInjection\Compiler\TagInjectablePass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Class InjectablePassTest
 *
 * @author Berny Cantos <be@rny.cc>
 */
class InjectablePassTest extends \PHPUnit_Framework_TestCase
{
    public function test_set_injectable_as_dependency()
    {
        $cb = $this->getContainer(new TagInjectablePass('tag.injectable'), array(
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

    public function test_custom_injectable_tag()
    {
        $injectableTagName = 'custom.injectable_tag';
        $cb = $this->getContainer(new TagInjectablePass($injectableTagName), array(
            'my_injectable' => $this->getInjectableDefinition()->addTag($injectableTagName, array(
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

    public function test_custom_method_on_a_per_injectable_aware_basis()
    {
        $cb = $this->getContainer(new TagInjectablePass('tag.injectable'), array(
            'my_injectable' => $this->getInjectableDefinition()->addTag('tag.injectable', array(
                'tag' => 'injectable_aware',
                'method' => 'setInjectable'
            )),
            'my_dep_1' => $this->getInjectableAwareDefinition()->addTag('injectable_aware'),
            'my_dep_2' => $this->getAlternateInjectableAwareDefinition()->addTag('injectable_aware', array(
                'method' => 'setCustomInjectableMethod',
            )),
        ));

        $dep1 = $cb->get('my_dep_1');
        $dep2 = $cb->get('my_dep_2');

        $this->assertInstanceOf('StdClass', $dep1->getInjectable());
        $this->assertSame($dep1->getInjectable(), $dep2->getInjectable());
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
     * @return Definition
     */
    protected function getAlternateInjectableAwareDefinition()
    {
        return new Definition('MockedAlternateInjectableAwareService');
    }

    /**
     * @param CompilerPassInterface $compilerPass
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

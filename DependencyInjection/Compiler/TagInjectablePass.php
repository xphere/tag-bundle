<?php

/**
 * This file is part of the xphere/tag-bundle package
 *
 * (c) Berny Cantos <be@rny.cc>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xphere\Bundle\TagBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Collects all injectables and configures them like this:
 * Call "method" in each service tagged "tag", passing the "injectable" service as parameter.
 *
 * You can define an injectable with the "tag.injectable" tag. (by default)
 * Also you can redefine the method on a per service basis.
 * Mandatory parameters: method, tag
 * Optional parameters: -
 *
 * Example:
 *
 * services:
 *   dispatcher:
 *     class: Symfony\Component\EventDispatcher\EventDispatcher
 *     tags:
 *       - { name: "tag.injectable", tag: "dispatcher.aware", method: "setDispatcher" }
 *
 *   myservice1:
 *     class: Acme\Bundle\Service\MyService
 *     tags:
 *       - { name: "dispatcher.aware" }
 *
 *   myservice2:
 *     class: Acme\Bundle\Service\OtherService
 *     tags:
 *       - { name: "dispatcher.aware", "method": "setEventDispatcher" }
 *
 */
class TagInjectablePass implements CompilerPassInterface
{
    /**
     * @var string
     *
     * Name of the tag to mark services as tag injectors
     */
    private $tagName;

    /**
     * Constructor
     *
     * @param string $tagName Name of the tag to mark services as tag injectors
     */
    public function __construct($tagName)
    {
        $this->tagName = $tagName;
    }

    /**
     * @param ContainerBuilder $container
     *
     * @throws \InvalidArgumentException
     */
    public function process(ContainerBuilder $container)
    {
        $taggedServices = $container->findTaggedServiceIds($this->tagName);
        foreach ($taggedServices as $id => $tags) {

            $reference = new Reference($id);
            foreach ($tags as $attr) {

                $tag = $this->getAttribute($id, $attr, 'tag');
                $method = $this->getAttribute($id, $attr, 'method');

                $this->injectInto($reference, $container, $tag, $method);
            }
        }
    }

    /**
     * Inject $injectable into each service tagged with $tag
     *
     * @param Reference $injectable Service to inject into others
     * @param ContainerBuilder $container Container where to find definitions
     * @param string $tag Services tagged with this will be injected
     * @param string $defaultMethod Name of the method to call, by default
     */
    protected function injectInto(Reference $injectable, ContainerBuilder $container, $tag, $defaultMethod)
    {
        $taggedServices = $container->findTaggedServiceIds($tag);
        foreach ($taggedServices as $serviceId => $serviceTags) {

            $definition = $container->getDefinition($serviceId);
            foreach ($serviceTags as $attr) {

                $method = isset($attr['method']) ? $attr['method'] : $defaultMethod;
                $definition->addMethodCall($method, array($injectable));
            }
        }
    }

    /**
     * Get attribute value
     *
     * @param string $id
     * @param array $attributes
     * @param string $attribute
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function getAttribute($id, array $attributes, $attribute)
    {
        if (isset($attributes[$attribute])) {
            return $attributes[$attribute];
        }

        throw new \InvalidArgumentException(sprintf(
            'Service "%s" must define the "%s" attribute on "%s" tags.',
            $id, $attribute, $this->tagName
        ));
    }
}

<?php

/**
 * This file is part of the Berny\TagBundle package
 *
 * (c) Berny Cantos <be@rny.cc>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Berny\Bundle\TagBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Collects all tag consumers and configures them like this:
 *
 * All services tagged "tag" are injected into the consumer through multiple "method" calls.
 * If "bulk" is true, calls "method" only once, but with all tagged services in an array.
 *
 * You can define a tag consumer with the "tag.consumer" tag. (by default)
 * Mandatory parameters: method, tag
 * Optional parameters: bulk (=false)
 *
 * Example:
 *
 * services:
 *   application:
 *     class: Symfony\Component\Console\Application
 *     tags:
 *       - { name: "tag.consumer", tag: "console.command", method: "addCommands", bulk: true }
 *
 *   command_1:
 *     class: Acme\Bundle\Command\MyCommand
 *     tags:
 *       - { name: "console.command" }
 *
 */
class TagConsumerPass implements CompilerPassInterface
{
    private $tag;

    /**
     * @param string $tag Name of the tag to mark services as tag consumers
     */
    public function __construct($tag = 'tag.consumer')
    {
        $this->tag = $tag;
    }

    /**
     * Process the container
     *
     * @param ContainerBuilder $container
     * @throws \InvalidArgumentException
     */
    public function process(ContainerBuilder $container)
    {
        foreach ($container->findTaggedServiceIds($this->tag) as $id => $tags) {
            $definition = $container->getDefinition($id);
            foreach ($tags as $attr) {
                $method = $this->getAttribute($id, $attr, 'method');
                $tag = $this->getAttribute($id, $attr, 'tag');
                $serviceIds = $container->findTaggedServiceIds($tag);
                $services = $this->getSortedReferences($serviceIds);
                if (isset($attr['bulk']) && $attr['bulk']) {
                    $this->injectBulk($definition, $services, $method);
                } else {
                    $this->injectEach($definition, $services, $method);
                }
            }
        }
    }

    /**
     * Injects $serviceIds into $definition with one call to $method for each service
     *
     * @param Definition $definition
     * @param Reference[] $services
     * @param string $method
     */
    protected function injectEach(Definition $definition, array $services, $method)
    {
        foreach ($services as $service) {
            $definition->addMethodCall($method, array($service));
        }
    }

    /**
     * Injects $serviceIds into $definition with one call to $method with all services in an array
     *
     * @param Definition $definition
     * @param Reference[] $services
     * @param string $method
     */
    protected function injectBulk(Definition $definition, array $services, $method)
    {
        $definition->addMethodCall($method, array($services));
    }

    /**
     * Return all tagged services, optionally ordered by 'order' attribute
     *
     * @param array $serviceIds
     * @return Reference[]
     */
    protected function getSortedReferences($serviceIds)
    {
        $ordered = $unordered = array();
        foreach ($serviceIds as $serviceId => $tags) {
            $service = new Reference($serviceId);
            foreach ($tags as $tag) {
                $order = isset($tag['order']) ? $tag['order'] : null;
                if ($order === null) {
                    $unordered[] = $service;
                } else {
                    $ordered[$order][] = $service;
                }
            }
        }
        if (empty($ordered)) {
            return $unordered;
        }
        ksort($ordered);
        $ordered[] = $unordered;
        return call_user_func_array('array_merge', $ordered);
    }

    /**
     * Get attribute value
     *
     * @param string $id
     * @param array $attributes
     * @param string $attribute
     * @throws \InvalidArgumentException
     */
    protected function getAttribute($id, array $attributes, $attribute)
    {
        if (isset($attributes[$attribute])) {
            return $attributes[$attribute];
        }

        throw new \InvalidArgumentException(sprintf(
            'Service "%s" must define the "%s" attribute on "%s" tags.',
            $id, $attribute, $this->tag
        ));
    }
}

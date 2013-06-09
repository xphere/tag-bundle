<?php

/**
 * This file is part of the Berny\TagBundle package
 *
 * (c) Berny Cantos <be@rny.cc>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Berny\Bundle\TagBundle\DependencyInjection;

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
                $serviceIds = array_keys($container->findTaggedServiceIds($tag));
                if (isset($attr['bulk']) && $attr['bulk']) {
                    $this->injectBulk($definition, $serviceIds, $method);
                } else {
                    $this->injectEach($definition, $serviceIds, $method);
                }
            }
        }
    }

    /**
     * Injects $serviceIds into $definition with one call to $method for each service
     *
     * @param Definition $definition
     * @param array $serviceIds
     * @param string $method
     */
    protected function injectEach(Definition $definition, array $serviceIds, $method)
    {
        foreach ($serviceIds as $serviceId) {
            $definition->addMethodCall($method, array(new Reference($serviceId)));
        }
    }

    /**
     * Injects $serviceIds into $definition with one call to $method with all services in an array
     *
     * @param Definition $definition
     * @param array $serviceIds
     * @param string $method
     */
    protected function injectBulk(Definition $definition, array $serviceIds, $method)
    {
        $services = array();
        foreach ($serviceIds as $serviceId) {
            $services[] = new Reference($serviceId);
        }
        $definition->addMethodCall($method, array($services));
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

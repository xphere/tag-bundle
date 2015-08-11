<?php

/**
 * This file is part of the xphere/tag-bundle package
 *
 * (c) Berny Cantos <be@rny.cc>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace xPheRe\Bundle\TagBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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
    /**
     * @var string
     *
     * Name of the tag to mark services as tag consumers
     */
    private $tagName;

    /**
     * Constructor
     *
     * @param string $tagName Name of the tag to mark services as tag consumers
     */
    public function __construct($tagName)
    {
        $this->tagName = $tagName;
    }

    /**
     * Find services tagged as consumer and process them
     *
     * @param ContainerBuilder $container
     *
     * @throws \InvalidArgumentException
     */
    public function process(ContainerBuilder $container)
    {
        $taggedServices = $container->findTaggedServiceIds($this->tagName);
        foreach ($taggedServices as $id => $tags) {

            $definition = $container->getDefinition($id);
            foreach ($tags as $attr) {

                $tag = $this->getAttribute($id, $attr, 'tag');
                $services = $this->getSortedReferences($container, $tag);

                if (isset($attr['method'])) {

                    if (isset($attr['bulk']) && $attr['bulk']) {
                        $services = array($services);
                    }

                    foreach ($services as $service) {
                        $definition->addMethodCall($attr['method'], array($service));
                    }

                } else {
                    $definition->addArgument($services);
                }
            }
        }
    }

    /**
     * Return all tagged services, optionally ordered by 'order' attribute.
     *
     * @param ContainerBuilder $container
     * @param string $tagName
     *
     * @return Reference[]
     */
    private function getSortedReferences(ContainerBuilder $container, $tagName)
    {
        $ordered = array();
        $unordered = array();

        $serviceIds = $container->findTaggedServiceIds($tagName);
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
    private function getAttribute($id, array $attributes, $attribute)
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

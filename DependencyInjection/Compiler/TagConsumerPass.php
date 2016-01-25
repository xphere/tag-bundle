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
 *       - { name: "tag.consumer", tag: "console.command", method: "addCommands", bulk: true, key: "alias" }
 *
 *   command_1:
 *     class: Acme\Bundle\Command\MyCommand
 *     tags:
 *       - { name: "console.command", alias: "acme_my_command" }
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
            foreach ($tags as $tag) {

                $references = $this->getSortedDependencies($container, [
                    'tag' => $this->getAttribute($id, $tag, 'tag'),
                    'key' => $this->getAttribute($id, $tag, 'key', false),
                    'reference' => $this->getAttribute($id, $tag, 'reference', true),
                    'instanceof' => $this->getAttribute($id, $tag, 'instanceof', false),
                ]);

                $this->configureConsumer($definition, $tag, $references);
            }
        }
    }

    /**
     * Configures a consumer with its dependencies
     *
     * @param Definition $definition
     * @param array      $tag
     * @param array      $references
     */
    private function configureConsumer(Definition $definition, array $tag, array $references)
    {
        if (isset($tag['method'])) {

            if (isset($tag['bulk']) && $tag['bulk']) {
                $definition->addMethodCall($tag['method'], array($references));
            } else {
                foreach ($references as $name => $service) {
                    $definition->addMethodCall($tag['method'], array($service, $name));
                }
            }

        } else {
            $definition->addArgument($references);
        }
    }

    /**
     * Return all tagged services, optionally ordered by 'order' attribute.
     *
     * @param ContainerBuilder $container
     * @param array            $options
     *
     * @return Reference[]|string[]
     */
    private function getSortedDependencies(ContainerBuilder $container, array $options)
    {
        $ordered = array();
        $unordered = array();

        $serviceIds = $container->findTaggedServiceIds($options['tag']);
        foreach ($serviceIds as $serviceId => $tags) {

            if ($options['instanceof']) {
                $class = $container->getDefinition($serviceId)->getClass();
                if (!is_a($class, $options['instanceof'], true)) {
                    throw new \UnexpectedValueException(sprintf(
                        'Service "%s" tagged as "%s" must be an instance of class "%s". Found "%s" instead.',
                        $serviceId, $options['tag'], $options['instanceof'], $class
                    ));
                }
            }

            $service = $options['reference'] ? new Reference($serviceId) : $serviceId;
            foreach ($tags as $tag) {

                if (isset($tag['order']) && is_numeric($tag['order'])) {
                    $order = $tag['order'];
                    $dependencies = &$ordered[$order];

                } else {
                    $dependencies = &$unordered;
                }

                if ($options['key']) {
                    $name = $this->getAttribute($serviceId, $tag, $options['key']);
                    $dependencies[$name] = $service;

                } else {
                    $dependencies[] = $service;
                }

                unset($dependencies);
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
     * @param array  $tag
     * @param string $attributeName
     * @param mixed  $default
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    private function getAttribute($id, array $tag, $attributeName, $default = null)
    {
        if (isset($tag[$attributeName])) {
            return $tag[$attributeName];
        }

        if (func_num_args() > 2) {
            return $default;
        }

        throw new \InvalidArgumentException(sprintf(
            'Service "%s" must define the "%s" attribute on "%s" tags.',
            $id, $attributeName, $this->tagName
        ));
    }
}

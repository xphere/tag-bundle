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
use Symfony\Component\DependencyInjection\Reference;

/**
 * Collects all tag consumers and configures them
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
     * @param ContainerBuilder $container
     * @throws \InvalidArgumentException
     */
    public function process(ContainerBuilder $container)
    {
        foreach ($container->findTaggedServiceIds($this->tag) as $id => $tags) {
            $definition = $container->getDefinition($id);
            foreach ($tags as $attr) {
                if (!isset($attr['method'])) {
                    throw $this->mustDefineAttributeException($id, 'method');
                }
                if (!isset($attr['tag'])) {
                    throw $this->mustDefineAttributeException($id, 'tag');
                }
                $taggedServiceIds = $container->findTaggedServiceIds($attr['tag']);
                if (isset($attr['bulk']) && $attr['bulk']) {
                    $services = array();
                    foreach ($taggedServiceIds as $subservice => $subtags) {
                        $services[] = new Reference($subservice);
                    }
                    $definition->addMethodCall($attr['method'], array($services));
                } else {
                    foreach ($taggedServiceIds as $subservice => $subtags) {
                        $definition->addMethodCall($attr['method'], array(new Reference($subservice)));
                    }
                }
            }
        }
    }

    protected function mustDefineAttributeException($id, $attribute)
    {
        return new \InvalidArgumentException(sprintf(
            'Service "%s" must define the "%s" attribute on "%s" tags.',
            $id, $attribute, $this->tag
        ));
    }
}

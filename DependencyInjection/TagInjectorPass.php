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
 * Collects all tag injectors and configures them
 */
class TagInjectorPass implements CompilerPassInterface
{
    private $tag;

    /**
     * @param string $tag Name of the tag to mark services as tag injectors
     */
    public function __construct($tag = 'tag.injector')
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
            $reference = new Reference($id);
            foreach ($tags as $attr) {
                if (!isset($attr['method'])) {
                    throw $this->mustDefineAttributeException($id, 'method');
                }
                if (!isset($attr['tag'])) {
                    throw $this->mustDefineAttributeException($id, 'tag');
                }
                $taggedServiceIds = $container->findTaggedServiceIds($attr['tag']);
                foreach ($taggedServiceIds as $subservice => $subtags) {
                    $container->getDefinition($subservice)
                              ->addMethodCall($attr['method'], array($reference));
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

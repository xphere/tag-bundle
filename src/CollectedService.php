<?php

/**
 * This file is part of the xphere/tag-bundle package
 *
 * (c) Berny Cantos <be@rny.cc>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xphere\Tag;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

class CollectedService
{
    private $container;
    private $identifier;
    private $attributes;
    private $resolvesTo;

    public function __construct(
        ContainerBuilder $container,
        $identifier,
        AttributeBag $attributes
    ) {
        $this->container = $container;
        $this->identifier = $identifier;
        $this->attributes = $attributes;
        $this->resolvesTo = null;
    }

    public function identifier()
    {
        return $this->identifier;
    }

    public function className()
    {
        $definition = $this->definition();
        $className = $definition->getClass();
        if (empty($className) && $definition instanceof DefinitionDecorator) {
            $definition = $this->container->findDefinition($definition->getParent());
            $className = $definition->getClass();
        }

        return $className;
    }

    public function definition()
    {
        return $this->container->findDefinition($this->identifier);
    }

    public function hasAttribute($name)
    {
        return $this->attributes->hasAttribute($name);
    }

    public function attribute($name)
    {
        return $this->attributes->attribute($name);
    }

    public function resolve($resolvesTo)
    {
        $this->resolvesTo = $resolvesTo;
    }

    public function resolvesTo()
    {
        return $this->resolvesTo;
    }
}

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

class Inject implements Stage
{
    /** @var Inject\Injector[] */
    private $injectors;

    public function __construct(array $injectors)
    {
        $this->injectors = $injectors;
    }

    public function process($iterator, ContainerBuilder $builder)
    {
        $dependencies = $this->getDependencies($this->iterate($iterator));
        foreach ($this->injectors as $serviceId => $injector) {
            $definition = $builder->findDefinition($serviceId);
            $injector->inject($dependencies, $definition);
        }
    }

    private function getDependencies($iterator)
    {
        $callback = function ($tag) {
            if (is_array($tag)) {
                return $this->getDependencies($tag);
            }

            if ($tag instanceof CollectedService) {
                return $tag->resolvesTo();
            }

            throw new \UnexpectedValueException();
        };

        return array_map($callback, $iterator);
    }

    private function iterate($iterator)
    {
        if ($iterator instanceof \Traversable) {
            return iterator_to_array($iterator);
        }

        if (is_array($iterator)) {
            return $iterator;
        }

        throw new \UnexpectedValueException(get_class($iterator));
    }
}

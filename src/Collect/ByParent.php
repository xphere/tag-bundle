<?php

/**
 * This file is part of the xphere/tag-bundle package
 *
 * (c) Berny Cantos <be@rny.cc>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xphere\Tag\Collect;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

class ByParent implements Collector
{
    private $parentServiceId;

    public function __construct($parentName)
    {
        $this->parentServiceId = $parentName;
    }

    public function find(ContainerBuilder $builder)
    {
        foreach ($builder->getDefinitions() as $serviceId => $definition) {
            if (!$definition instanceof DefinitionDecorator) {
                continue;
            }

            $parent = $definition->getParent();
            if ($parent === $this->parentServiceId) {
                yield $serviceId => [];
            }
        }
    }
}

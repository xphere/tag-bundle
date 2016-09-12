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

class ByTag implements Collector
{
    private $tagName;

    public function __construct($tagName, array $defaults = [])
    {
        $this->tagName = $tagName;
    }

    public function find(ContainerBuilder $builder)
    {
        foreach ($builder->findTaggedServiceIds($this->tagName) as $serviceId => $tags) {
            foreach ($tags as $tag) {
                yield $serviceId => $tag;
            }
        }
    }
}

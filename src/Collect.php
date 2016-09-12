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

class Collect implements Stage
{
    private $finder;
    private $defaults = [];

    public function __construct(Collect\Collector $finder, array $defaults = [])
    {
        $this->finder = $finder;
        $this->defaults = $defaults;
    }

    public function process($iterator, ContainerBuilder $builder)
    {
        foreach ($this->finder->find($builder) as $identifier => $attributes) {
            yield new CollectedService($builder, $identifier, new AttributeBag($attributes, $this->defaults));
        }
    }
}

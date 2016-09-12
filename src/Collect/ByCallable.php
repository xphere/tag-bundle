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

class ByCallable implements Collector
{
    private $callable;

    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }

    public function find(ContainerBuilder $builder)
    {
        $callable = $this->callable;

        return $callable($builder);
    }
}

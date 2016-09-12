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

class CallableStage implements Stage
{
    private $callable;

    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }

    public function process($iterator, ContainerBuilder $builder)
    {
        $callable = $this->callable;

        return $callable($iterator, $builder);
    }
}

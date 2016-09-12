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

class Resolve implements Stage
{
    private $resolver;

    public function __construct(Resolve\Resolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function process($iterator, ContainerBuilder $builder)
    {
        /** @var CollectedService $tag */
        foreach ($iterator as $tag) {
            $tag->resolve($this->resolver->resolve($tag));

            yield $tag;
        }
    }
}

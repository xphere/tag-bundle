<?php

/**
 * This file is part of the xphere/tag-bundle package
 *
 * (c) Berny Cantos <be@rny.cc>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xphere\Tag\Resolve;

use Symfony\Component\DependencyInjection\Reference;
use Xphere\Tag\CollectedService;

class AsReference implements Resolver
{
    public function resolve(CollectedService $tag)
    {
        return new Reference($tag->identifier());
    }
}

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

use Xphere\Tag\CollectedService;

class AsLazy implements Resolver
{
    public function resolve(CollectedService $tag)
    {
        $tag->definition()->setPublic(true);

        return $tag->identifier();
    }
}

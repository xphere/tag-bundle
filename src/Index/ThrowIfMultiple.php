<?php

/**
 * This file is part of the xphere/tag-bundle package
 *
 * (c) Berny Cantos <be@rny.cc>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xphere\Tag\Index;

use Xphere\Tag\CollectedService;

class ThrowIfMultiple extends Override
{
    /**
     * @param CollectedService $tag
     * @param string $index
     * @param CollectedService[] $result
     *
     * @throws \Exception
     */
    public function index(CollectedService $tag, $index, array &$result)
    {
        if (array_key_exists($index, $result)) {
            throw new \Exception(sprintf(
                'Multiple services ("%s", "%s") found with same index, but not allowed',
                $result[$index]->identifier(),
                $tag->identifier()
            ));
        }

        parent::index($tag, $index, $result);
    }
}

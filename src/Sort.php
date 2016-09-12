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

class Sort implements Stage
{
    private $by;

    public function __construct(callable $by)
    {
        $this->by = $by;
    }

    public function process($iterator, ContainerBuilder $builder)
    {
        $unsorted = [];
        $sorted = new \SplPriorityQueue();

        $by = $this->by;
        foreach ($iterator as $tag) {
            $order = $by($tag);
            if ($order === null) {
                $unsorted[] = $tag;
            } else {
                $sorted->insert($tag, $order);
            }
        }

        return array_merge(
            iterator_to_array($sorted),
            $unsorted
        );
    }
}

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
use Xphere\Tag\Index\Indexer;

class Index implements Stage
{
    private $by;
    private $indexer;

    public function __construct(callable $by, Indexer $indexer)
    {
        $this->by = $by;
        $this->indexer = $indexer;
    }

    public function process($iterator, ContainerBuilder $builder)
    {
        $result = [];
        $by = $this->by;
        foreach ($iterator as $tag) {
            $this->indexer->index($tag, $by($tag), $result);
        }

        return $result;
    }
}

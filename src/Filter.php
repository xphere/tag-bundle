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

class Filter implements Stage
{
    /** @var Filter\Filter[] */
    private $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function process($iterator, ContainerBuilder $builder)
    {
        foreach ($iterator as $tag) {
            if ($this->isValid($tag)) {
                yield $tag;
            }
        }
    }

    private function isValid(CollectedService $collectedService)
    {
        foreach ($this->filters as $filter) {
            if (!$filter->valid($collectedService)) {
                return false;
            }
        }

        return true;
    }
}

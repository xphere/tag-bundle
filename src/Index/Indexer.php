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

interface Indexer
{
    public function index(CollectedService $tag, $index, array &$result);
}
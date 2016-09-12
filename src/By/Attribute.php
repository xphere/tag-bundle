<?php

/**
 * This file is part of the xphere/tag-bundle package
 *
 * (c) Berny Cantos <be@rny.cc>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xphere\Tag\By;

use Xphere\Tag\CollectedService;

class Attribute
{
    private $attributeName;

    public function __construct($attributeName)
    {
        $this->attributeName = $attributeName;
    }

    public function __invoke(CollectedService $collectedService)
    {
        return $collectedService->attribute($this->attributeName);
    }
}

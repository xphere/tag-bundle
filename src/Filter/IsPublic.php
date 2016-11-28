<?php

/**
 * This file is part of the xphere/tag-bundle package
 *
 * (c) Berny Cantos <be@rny.cc>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xphere\Tag\Filter;

use Xphere\Tag\CollectedService;

class IsPublic implements Validation
{
    public function valid(CollectedService $collectedService)
    {
        return $collectedService->definition()->isPublic();
    }

    public function validationMessage(CollectedService $collectedService)
    {
        return sprintf(
            'Service "%s" must be public.',
            $collectedService->identifier()
        );
    }
}

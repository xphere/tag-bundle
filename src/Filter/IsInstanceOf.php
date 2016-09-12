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

class IsInstanceOf implements Validation
{
    private $className;

    public function __construct($className)
    {
        $this->className = $className;
    }

    public function valid(CollectedService $collectedService)
    {
        $className = $this->classNameOf($collectedService);

        return is_a($className, $this->className, true);
    }

    public function validationMessage(CollectedService $collectedService)
    {
        return sprintf(
            'Service "%s" must be an instance of "%s", found "%s" instead.',
            $collectedService->identifier(),
            $this->className,
            $this->classNameOf($collectedService)
        );
    }

    private function classNameOf(CollectedService $collectedService)
    {
        return $collectedService->definition()->getClass();
    }
}

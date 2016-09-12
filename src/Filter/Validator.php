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

class Validator implements Filter
{
    private $filter;

    public function __construct(Filter $filter)
    {
        $this->filter = $filter;
    }

    public function valid(CollectedService $collectedService)
    {
        if ($this->filter->valid($collectedService)) {
            return true;
        }

        if ($this->filter instanceof Validation) {
            $message = $this->filter->validationMessage($collectedService);
        } else {
            $message = sprintf(
                'Service definition "%s" is not valid: %s failed',
                $collectedService->identifier(),
                get_class($this->filter)
            );
        }

        throw new \UnexpectedValueException($message);
    }
}

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

use Xphere\Tag\Filter;

class Validate extends Filter
{
    public function __construct(array $filters)
    {
        parent::__construct(self::convertToValidators($filters));
    }

    private static function convertToValidators(array $filters)
    {
        $callback = function (Filter\Filter $filter) {
            return new Filter\Validator($filter);
        };

        return array_map($callback, $filters);
    }
}

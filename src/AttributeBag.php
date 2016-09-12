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

class AttributeBag
{
    private $attributes;
    private $defaults;

    public function __construct(array $attributes, array $defaults = [])
    {
        $this->attributes = $attributes;
        $this->defaults = $defaults;
    }

    public function hasAttribute($name)
    {
        return array_key_exists($name, $this->attributes) || array_key_exists($name, $this->defaults);
    }

    public function attribute($name)
    {
        if (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        }

        if (array_key_exists($name, $this->defaults)) {
            return $this->defaults[$name];
        }

        throw new \Exception(sprintf(
            'Attribute "%s" is required but missing',
            $name
        ));
    }
}

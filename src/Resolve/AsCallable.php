<?php

/**
 * This file is part of the xphere/tag-bundle package
 *
 * (c) Berny Cantos <be@rny.cc>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xphere\Tag\Resolve;

use Xphere\Tag\CollectedService;

class AsCallable implements Resolver
{
    private $method;
    private $resolver;

    public function __construct($method, Resolver $resolver)
    {
        $this->method = is_string($method)
            ? function () use ($method) { return $method; }
            : $method
        ;
        $this->resolver = $resolver;
    }

    public function resolve(CollectedService $tag)
    {
        return [
            call_user_func($this->method, $tag),
            $this->resolver->resolve($tag)
        ];
    }
}

<?php

/**
 * This file is part of the xphere/tag-bundle package
 *
 * (c) Berny Cantos <be@rny.cc>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xphere\Tag\Inject;

use Symfony\Component\DependencyInjection\Definition;

class Method implements Injector
{
    private $methodName;
    private $method;

    public function __construct($methodName, Method\Method $method)
    {
        $this->methodName = $methodName;
        $this->method = $method;
    }

    public function inject($dependencies, Definition $definition)
    {
        $this->method->apply($definition, $this->methodName, $dependencies);
    }
}

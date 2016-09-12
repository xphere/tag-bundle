<?php

/**
 * This file is part of the xphere/tag-bundle package
 *
 * (c) Berny Cantos <be@rny.cc>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xphere\Tag\Inject\Constructor;

use Symfony\Component\DependencyInjection\Definition;
use Xphere\Tag\Inject\Injector;

class AddArgument implements Injector
{
    public function inject($dependencies, Definition $definition)
    {
        $definition->addArgument($dependencies);
    }
}

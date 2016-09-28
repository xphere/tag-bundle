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

class ReplaceArgument implements Injector
{
    private $index;

    public function __construct($index)
    {
        $this->index = $index;
    }

    public function inject($dependencies, Definition $definition)
    {
        $index = $this->index;
        if (!is_numeric($index)) {
            $index = $this->findIndexByReflection($definition, $index);
        }

        $definition->replaceArgument($index, $dependencies);
    }

    private function findIndexByReflection(Definition $definition, $argumentName)
    {
        $className = $definition->getClass();
        $rc = new \ReflectionClass($className);

        foreach ($rc->getConstructor()->getParameters() as $parameter) {
            if ($parameter->getName() === $argumentName) {
                return $parameter->getPosition();
            }
        }

        throw new \InvalidArgumentException(sprintf(
            'Named argument "%s" not found in class "%s" while injecting dependencies',
            $argumentName,
            $className
        ));
    }
}

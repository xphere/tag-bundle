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

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CompilerPass implements CompilerPassInterface
{
    /** @var Stage[] */
    private $stages = [];

    public function __construct(array $stages)
    {
        $this->stages = $stages;
    }

    public function process(ContainerBuilder $container)
    {
        $result = [];
        foreach ($this->stages as $stage) {
            $result = $stage->process($result, $container);
        }
    }
}

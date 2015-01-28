<?php

/**
 * This file is part of the Berny\TagBundle package
 *
 * (c) Berny Cantos <be@rny.cc>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Berny\Bundle\TagBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

use Berny\Bundle\TagBundle\DependencyInjection\Compiler\TagConsumerPass;
use Berny\Bundle\TagBundle\DependencyInjection\Compiler\TagInjectablePass;

class TagBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container
            ->addCompilerPass(new TagConsumerPass('tag.consumer'))
            ->addCompilerPass(new TagInjectablePass('tag.injectable'))
        ;
    }
}

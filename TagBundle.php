<?php

/**
 * This file is part of the xphere/tag-bundle package
 *
 * (c) Berny Cantos <be@rny.cc>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xphere\Bundle\TagBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

use Xphere\Bundle\TagBundle\DependencyInjection\Compiler\TagConsumerPass;
use Xphere\Bundle\TagBundle\DependencyInjection\Compiler\TagInjectablePass;

/**
 * Class TagBundle
 *
 * @author Berny Cantos <be@rny.cc>
 */
class TagBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new TagConsumerPass('tag.consumer'));
        $container->addCompilerPass(new TagInjectablePass('tag.injectable'));
    }
}

<?php

/**
 * This file is part of the xphere/tag-bundle package
 *
 * (c) Berny Cantos <be@rny.cc>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use xPheRe\Bundle\TagBundle\DependencyInjection\Compiler\TagConsumerPass;
use xPheRe\Bundle\TagBundle\DependencyInjection\Compiler\TagInjectablePass;
use xPheRe\Bundle\TagBundle\TagBundle;

/**
 * Class TagBundleTest
 *
 * @author Berny Cantos <be@rny.cc>
 */
class TagBundleTest extends \PHPUnit_Framework_TestCase
{
    public function test_consumer_pass_was_added()
    {
        $bundle = new TagBundle();

        /**
         * @var ContainerBuilder|ObjectProphecy $container
         */
        $container = $this->prophesize(ContainerBuilder::class);

        $container->addCompilerPass(Argument::type(TagConsumerPass::class));
        $container->addCompilerPass(Argument::type(TagInjectablePass::class));

        $bundle->build($container->reveal());
    }
}

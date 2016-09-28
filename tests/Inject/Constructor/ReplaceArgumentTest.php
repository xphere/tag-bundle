<?php

/**
 * This file is part of the xphere/tag-bundle package
 *
 * (c) Berny Cantos <be@rny.cc>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TestTagBundle;

use Symfony\Component\DependencyInjection\Definition;

use Xphere\Tag\Inject\Constructor\ReplaceArgument;

class ReplaceArgumentTest extends \PHPUnit_Framework_TestCase
{
    public function test_replaces_indexed_arguments()
    {
        $sut = new ReplaceArgument(2);
        $definition = new Definition(ReplaceArgumentSubject::class, [ 1, 2, 3 ]);
        $sut->inject(10, $definition);

        $this->assertEquals([ 1, 2, 10 ], $definition->getArguments());
    }

    public function test_uses_reflection_for_named_arguments()
    {
        $sut = new ReplaceArgument('second');
        $definition = new Definition(ReplaceArgumentSubject::class, [ 1, 2, 3 ]);
        $sut->inject(10, $definition);

        $this->assertEquals([ 1, 10, 3 ], $definition->getArguments());
    }

    public function test_throws_when_named_argument_cant_be_found()
    {
        $sut = new ReplaceArgument('fourth');
        $definition = new Definition(ReplaceArgumentSubject::class);

        $this->setExpectedException(\InvalidArgumentException::class);

        $sut->inject(10, $definition);
    }
}

class ReplaceArgumentSubject
{
    public $first;
    public $second;
    private $third;

    public function __construct($first, $second, $third)
    {
        $this->first = $first;
        $this->second = $second;
        $this->third = $third;
    }
}

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

use Xphere\Tag\Inject\Injector;

class CompilerPassBuilder
{
    private $collector;
    private $attributeDefaults = [];
    private $validators = [];
    private $sorted = null;
    private $resolver = null;
    private $indexed = null;
    private $stages = [];
    private $injectors = [];

    static public function create()
    {
        return new self();
    }

    public function build()
    {
        $stages = [
            $this->collectionStage(),
        ];

        if (!empty($this->validators)) {
            $stages[] = $this->validationStage();
        }

        if ($this->sorted) {
            $stages[] = $this->sortedStage();
        }

        $stages = array_merge($stages, $this->stages);

        $stages[] = $this->resolverStage();

        if ($this->indexed) {
            $stages[] = $this->indexedStage();
        }

        if ($this->injectors) {
            $stages[] = $this->injectionStage();
        }

        return new CompilerPass($stages);
    }

    public function byTag($tagName)
    {
        $this->collector = new Collect\ByTag($tagName);

        return $this;
    }

    public function byParent($parentId)
    {
        $this->collector = new Collect\ByParent($parentId);

        return $this;
    }

    public function withDefaults(array $defaultOptions)
    {
        $this->attributeDefaults = $defaultOptions;

        return $this;
    }

    public function setDefault($attributeName, $value)
    {
        $this->attributeDefaults[$attributeName] = $value;

        return $this;
    }

    public function isInstanceOf($className)
    {
        $this->validators[] = new Filter\IsInstanceOf($className);

        return $this;
    }

    public function sortedBy($by)
    {
        $this->sorted = new By\Attribute($by);

        return $this;
    }

    public function addStage($stage)
    {
        if (is_callable($stage)) {
            $stage = new CallableStage($stage);
        }

        if (!$stage instanceof Stage) {
            throw new \UnexpectedValueException(sprintf(
                'Stage must be instance of "%s", but "%s" found.',
                Stage::class,
                get_class($stage)
            ));
        }

        $this->stages[] = $stage;

        return $this;
    }

    public function asLazy()
    {
        $this->resolver = new Resolve\AsLazy();

        return $this;
    }

    public function validate(Filter\Validation $validation)
    {
        $this->validators[] = $validation;

        return $this;
    }

    public function indexedBy($by, Index\Indexer $indexer = null)
    {
        $this->indexed = new Index(
            is_string($by) ? new By\Attribute($by) : $by,
            null === $indexer ? new Index\Override() : $indexer
        );

        return $this;
    }

    public function injectTo($serviceId, Injector $injector = null)
    {
        $this->injectors[$serviceId] = $injector ? $injector : new Inject\Constructor\AddArgument();

        return $this;
    }

    private function collectionStage()
    {
        return new Collect($this->collector, $this->attributeDefaults);
    }

    private function validationStage()
    {
        return new Validate($this->validators);
    }

    private function resolverStage()
    {
        $resolver = $this->resolver;
        if (null === $resolver) {
            $resolver = new Resolve\AsReference();
        }

        return new Resolve($resolver);
    }

    private function sortedStage()
    {
        return new Sort($this->sorted);
    }

    private function indexedStage()
    {
        return $this->indexed;
    }

    private function injectionStage()
    {
        return new Inject($this->injectors);
    }
}

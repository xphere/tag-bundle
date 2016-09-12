<?php

/**
 * This file is part of the xphere/tag-bundle package
 *
 * (c) Berny Cantos <be@rny.cc>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xphere\Bundle\TagBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Xphere\Tag;

/**
 * Collects all tag consumers and configures them like this:
 *
 * All services tagged "tag" are injected into the consumer through multiple "method" calls.
 * If "bulk" is true, calls "method" only once, but with all tagged services in an array.
 *
 * You can define a tag consumer with the "tag.consumer" tag. (by default)
 * Mandatory parameters: tag
 * Optional parameters: method, bulk, key, multiple, reference, instanceof
 *
 * Example:
 *
 * services:
 *   application:
 *     class: Symfony\Component\Console\Application
 *     tags:
 *       - { name: "tag.consumer", tag: "console.command", method: "addCommands", bulk: true, key: "alias" }
 *
 *   command_1:
 *     class: Acme\Bundle\Command\MyCommand
 *     tags:
 *       - { name: "console.command", alias: "acme_my_command" }
 *
 */
class TagConsumerPass implements CompilerPassInterface
{
    /**
     * @var string
     *
     * Name of the tag to mark services as tag consumers
     */
    private $tagName;

    /**
     * Constructor
     *
     * @param string $tagName Name of the tag to mark services as tag consumers
     */
    public function __construct($tagName)
    {
        $this->tagName = $tagName;
    }

    /**
     * Find services tagged as consumer and process them
     *
     * @param ContainerBuilder $container
     *
     * @throws \InvalidArgumentException
     */
    public function process(ContainerBuilder $container)
    {
        $builder = new Tag\CompilerPassBuilder();

        $builder
            ->byTag($this->tagName)
            ->withDefaults([
                'index-by' => false,
                'instanceof' => false,
                'key' => false,
                'method' => false,
                'multiple' => false,
                'order-by' => 'order',
                'reference' => true,
            ])
            ->addStage(function ($iterator, ContainerBuilder $builder) {
                foreach ($iterator as $collectedService) {
                    $this->processTaggedService($collectedService, $builder);
                }
            });

        $compilerPass = $builder->build();
        $compilerPass->process($container);
    }

    private function processTaggedService(Tag\CollectedService $service, ContainerBuilder $container)
    {
        $builder = new Tag\CompilerPassBuilder();

        $builder->byTag($service->attribute('tag'));
        $this->processIndex($service, $builder);
        $this->processReference($service, $builder);
        $this->processOrder($service, $builder);
        $this->processInstanceOf($service, $builder);
        $this->processInjection($service, $builder);

        $builder->build()->process($container);
    }

    private function processIndex(Tag\CollectedService $service, Tag\CompilerPassBuilder $builder)
    {
        $key = $service->attribute('key');
        $indexBy = $service->attribute('index-by');
        if(false === $indexBy && false !== $key) {
            $indexBy = 'key';
        }

        switch ($indexBy) {
            case false:
                return;

            case 'key':
                if (!is_string($key)) {
                    throw new \InvalidArgumentException(sprintf(
                        'Service "%s" tagged as "%s" defined "index-by" = "key" but no "key" field.',
                        $service->identifier(),
                        $this->tagName
                    ));
                }

                $indexBy = $key;
                break;

            case 'class':
                $indexBy = new Tag\By\ClassName();
                break;
        }

        if ($service->attribute('multiple')) {
            $indexer = new Tag\Index\ForceArray();
        } else {
            $indexer = new Tag\Index\Override();
        }

        $builder->indexedBy($indexBy, $indexer);
    }

    private function processReference(Tag\CollectedService $service, Tag\CompilerPassBuilder $builder)
    {
        if ($service->attribute('reference') === false) {
            $builder->asLazy();
        }
    }

    private function processInstanceOf(Tag\CollectedService $service, Tag\CompilerPassBuilder $builder)
    {
        $instanceOfClass = $service->attribute('instanceof');
        if ($instanceOfClass !== false) {
            $builder->isInstanceOf($instanceOfClass);
        }
    }

    private function processOrder(Tag\CollectedService $service, Tag\CompilerPassBuilder $builder)
    {
        $orderBy = $service->attribute('order-by');
        if ($orderBy !== false) {
            $builder
                ->sortedBy($orderBy)
                ->setDefault($orderBy, null)
            ;
        }
    }

    private function processInjection(Tag\CollectedService $service, Tag\CompilerPassBuilder $builder)
    {
        $methodName = $service->attribute('method');
        $bulk = $service->hasAttribute('bulk') ? $service->attribute('bulk') : ($methodName === false);

        $injector = null;
        if ($methodName === false) {
            if ($bulk === false) {
                $injector = new Tag\Inject\Constructor\AddArguments();
            } else {
                $injector = new Tag\Inject\Constructor\AddArgument();
            }
        } else {
            if ($bulk === false) {
                $method = new Tag\Inject\Method\Multiple();
            } else {
                $method = new Tag\Inject\Method\Single();
            }

            $injector = new Tag\Inject\Method($methodName, $method);
        }

        $builder->injectTo($service->identifier(), $injector);
    }
}

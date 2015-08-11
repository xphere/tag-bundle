xphere/tag-bundle
===============

Are you tired to add `CompilerPass`es just to collect some services tagged on your container?

Say NO to most of them with this bundle!

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/dccda8eb-884b-456b-adea-3a821a7ec1c3/small.png)](https://insight.sensiolabs.com/projects/dccda8eb-884b-456b-adea-3a821a7ec1c3)

⚠ Note ⚠
--------
Mind the namespace change
- Before `0.4.0`: `Berny\Bundle\TagBundle`
- After  `0.4.0`: `xPheRe\Bundle\TagBundle`

Why I would want that?
----------------------

More than often you want to search for services tagged with a particular tag and call some method in your service with them. This can be done with a custom `CompilerPass`.

```yml
services:
    my_plugin_enumerator:
        class: PluginEnumerator

    useless_plugin:
        class: UselessPlugin
        tag: - { name: my_plugin }

    even_more_useless_plugin:
        class: EvenMoreUselessPlugin
        tag: - { name: my_plugin }
```

```php
class PluginEnumeratorConsumerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('my_plugin_enumerator')) {
            return;
        }

        $definition = $container->findDefinition('my_plugin_enumerator');

        $taggedServices = $container->findTaggedServices('my_plugin');

        foreach ($taggedServices as $id => $attributes) {
            $definition->addMethodCall('addPlugin', array(new Reference($id)));
        }
    }
}
```

Another use case is to inject a service to every other that is tagged with a particular tag. An example:

```yml
services:
    my_event_dispatcher:
        class: MyEventDispatcher

    useless_service:
        class: UselessService
        tag: - { name: my_event_dispatcher.aware }

    even_more_useless_service:
        class: EvenMoreUselessService
        tag: - { name: my_event_dispatcher.aware }
```

```php
class MyEventDispatcherInjectableCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('my_event_dispatcher')) {
            return;
        }

        $reference = new Reference('my_event_dispatcher');
        $taggedServices = $container->findTaggedServices('my_event_dispatcher.aware');

        foreach ($taggedServices as $id => $attributes) {
            $definition = $container->findDefinition($id);
            $definition->addMethodCall('setMyEventDispatcher', array($reference));
        }
    }
}
```

This boilerplate is repeated once and again in every project I've seen.
With this bundle you can say goodbye to most of this compiler passes.

Features
--------

With this bundle you can:
- Tag a service as a consumer of another tag.
- Tag a service as injectable into others.

Compatibility
-------------

Tested under Symfony2, from 2.0.10 to 2.6.3

Installation
------------

### From [composer/packagist](https://getcomposer.org)
- Require `xphere/tag-bundle` package in your composer
- Add the bundle to your `AppKernel.php`

Usage
-----

### Consumer

You can define a service as a "tag consumer" of another tag, and let the bundle make the relationship between them.
Just tag your service as a `tag.consumer` and specify which `tag` to collect and which `method` to call.

The first example using this bundle is just configuration:

```yml
services:
    my_plugin_enumerator:
        class: PluginEnumerator
        tags:
            - { name: tag.consumer, tag: my_plugin, method: addPlugin }

    useless_plugin:
        class: UselessPlugin
        tag: - { name: my_plugin }

    even_more_useless_plugin:
        class: EvenMoreUselessPlugin
        tag: - { name: my_plugin }
```

The only change is the tag in `my_plugin_enumerator`. The `CompilerPass` boilerplate is gone.

This calls `PluginEnumerator::addPlugin` with each `my_plugin`, but you can also call this once with all services using the `bulk` parameter.

```yml
services:
    my_plugin_enumerator:
        class: PluginEnumerator
        tags:
            - { name: tag.consumer, tag: my_plugin, method: addPlugins, bulk: true }
```

This is calling `PluginEnumerator::addPlugins` just once, with an array of the services.

### Injectable

You can define a service as a "tag injectable" from another tag, and let the bundle do the hard work.
Just tag your service as a `tag.injectable` and specify which `tag` to collect and which `method` to call in each service.

The second example in the introduction will be like this:

```yml
services:
    my_event_dispatcher:
        class: MyEventDispatcher
        tag: - { name: tag.injectable, tag: my_event_dispatcher.aware, method: setMyEventDispatcher }

    useless_service:
        class: UselessService
        tag: - { name: my_event_dispatcher.aware }

    even_more_useless_service:
        class: EvenMoreUselessService
        tag: - { name: my_event_dispatcher.aware }
```

The only change is the tag in `my_event_dispatcher`. The `CompilerPass` boilerplate is also gone.

This forces all `my_event_dispatcher.aware` to have a `setMyEventDispatcher` method. But you can change that for a particular service with the `method` parameter.

```yml
services:
    my_event_dispatcher:
        class: MyEventDispatcher
        tag: - { name: tag.injectable, tag: my_event_dispatcher.aware, method: setMyEventDispatcher }

    useless_service:
        class: UselessService
        tag: - { name: my_event_dispatcher.aware, method: setEventDispatcher }

    even_more_useless_service:
        class: EvenMoreUselessService
        tag: - { name: my_event_dispatcher.aware }
```

Now it's calling `setEventDispatcher` for `UselessService`, and the default method for the others.

### Advanced usage

You can add manually `TagConsumerPass` or `TagInjectablePass` (or both) without adding the "whole" bundle to your `Kernel`, even customize the tag names used to apply them.

In your `Kernel`:
```php

[...]
use xPheRe\Bundle\TagBundle\DependencyInjection\Compiler\TagConsumerPass;
use xPheRe\Bundle\TagBundle\DependencyInjection\Compiler\TagInjectablePass;
[...]

class AppKernel extends Kernel
{
    [...]
    protected function prepareContainer(ContainerBuilder $container)
    {
        parent::prepareContainer($container);

        $container->addCompilerPass(new TagConsumerPass('tag_collector'));
        $container->addCompilerPass(new TagInjectablePass('tag_injectable'));
    }
    [...]
}
```

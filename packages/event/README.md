Charcoal Event
==============

The `Charcoal\Event` module (`charcoal/event`) provides a [`Psr-14`](https://www.php-fig.org/psr/psr-14/) compliant event system using [`League\Event`](https://event.thephpleague.com/3.0/).


# How to install

The preferred (and only supported) way of installing charcoal-event is with **composer**:

```shell
$ composer require charcoal/event
```
To install a full Charcoal project, which includes `charcoal-event`:

```shell
$ composer create-project charcoalphp/boilerplate:@dev --prefer-source
```

> Note that charcoal-event is intended to be run along a `charcoal-app` based project. To start from a boilerplate:
>
> ```shell
> $ composer create-project locomotivemtl/charcoal-project-boilerplate

## Dependencies

- `PHP 7.4+`


## Service Provider

The following services are provided with the use of [_event_](https://github.com/charcoalphp/event)

### Services

* [$container['event/dispatcher']](src/Charcoal/Event/ServiceProvider/EventServiceProvider.php) instance of `\League\Event\Dispatcher`


## Configuration

The configuration of the event module is done via the `event` key of the project configuration.


There is two ways to bind listeners to events : 

- The first one is a direct mapping between `Event` classes and listeners :


```json
{
    "events": {
        "listeners": {
            "Namespace\\For\\My\\EventClass": {
                "Namespace\\For\\My\\ListenerClass": {...}
            }
        }
    }
}
```

- The second one is through a `ListenerSubscriber` which is a class that registers listeners internally.
See the [Subscribers](#subscribers) section for more details.

```json
{
    "events": {
        "subscribers": [
            "Namespace\\For\\My\\ListenerSubscriber"
        ]
    }
}
```

# Usage

## Events

An event is a class that can be anything you want. Although, for consistency purposes, certain guidelines can be 
applied to ensure ease of use: 

- The [`Charcoal/Event/Event`](src/Charcoal/Event/Event.php) class can be used as a base for a new Event. This base event ensures the event is [Stoppable](https://www.php-fig.org/psr/psr-14/#stoppable-events).
- The class name should be composed of a context and an action applied to it. (e.g) `FileWasUploaded`, `ModelWasUpdated`.
- Since the class implies a context, an event should be able to set said context in its constructor : 
```php
class FileWasUploaded extends Event
{
    public function __construct(string $file)
    {
        $this->file = $file;

    }
}
```

## Listeners

A Listener may be any PHP callable. A Listener MUST have one and only one parameter, which is the Event to which it responds.
See [Psr-14](https://www.php-fig.org/psr/psr-14/#listeners) documentation for more info about listeners.

In charcoal's context, listeners that are destined to be loaded through json config files should : 
- extend [`AbstractEventListener`](src/Charcoal/Event/AbstractEventListener.php) or implement [`EventListenerInterface`](src/Charcoal/Event/EventListenerInterface.php)
- have the `Listener` suffix in its class name

Config injected listeners are instantiated through a factory and are provided with a `setDependencies()` method for dependency injection.
The `__invoke` method receives the `$event` object as sole parameter.

If a listener is to be subscribed outside the config, manually, it can be a mere callable function that receives the `event` object. 

To bind a listener to an event, one can manually subscribe the listener using the `event/dispatcher` container key, or use the app config system to attach listeners to events.
By doing so, options can be passed to the listener to dictate its behaviour : 

```json
{
    "events": {
        "Namespace\\For\\Some\\Event": {
            "Namespace\\For\\Some\\Listener": {
                "priority": -1000,
                "once": true
            }
        }
    }
}
```

### options

- `priority` : Define the listener priority. Higher priority means the listener will be triggered before lower priority listeners.
[Default: 0]
- `once` : Only trigger the listener once [Default: false]

## Subscribers

Listener subscribers are a convenient way to subscribe multiple listeners at once. They allow grouping listener
registrations by concern. Usually, a package will provide a `ListenerSubscriber` to group event listeners and streamline
the registration process. See [League\Event\ListenerSubscriber](https://event.thephpleague.com/3.0/extra-utilities/listener-subscriber/)
for more details about subscribers.

The [`AbstractListenerSubscriber`](src/Charcoal/Event/AbstractListenerSubscriber.php) class can be extended to create 
a listener subscriber. Implement the method `subscribeListeners` and subscribe the listeners on `$acceptor`

```php
public function subscribeListeners(ListenerRegistry $acceptor): void
{
    $acceptor->subscribeTo(MyEvent::class, $this->createListener(MyListener::class));
}
```

# Development

To install the development environment:

```shell
$ composer install --prefer-source
```

To run the tests:

```shell
$ composer test
```

## Coding style

The Charcoal-Admin module follows the Charcoal coding-style:

-	[_PSR-1_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md), except for
	-	Method names MUST be declared in `snake_case`.
-	[_PSR-2_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md), except for the PSR-1 requirement.q
-	[_PSR-4_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md), autoloading is therefore provided by _Composer_
-	[_phpDocumentor_](http://phpdoc.org/)
	-	Add DocBlocks for all classes, methods, and functions;
	-	For type-hinting, use `boolean` (instead of `bool`), `integer` (instead of `int`), `float` (instead of `double` or `real`);
	-	Omit the `@return` tag if the method does not return anything.
-	Naming conventions
	-	Read the [phpcs.xml.dist](phpcs.xml.dist) file for all the details.

> Coding style validation / enforcement can be performed with `composer phpcs`. An auto-fixer is also available with `composer phpcbf`.


Every classes, methods and functions should be covered by unit tests. PHP code can be tested with _PHPUnit_ and Javascript code with _QUnit_.

# Authors

-	Joel Alphonso <joel@locomotive.ca>

# License

Charcoal is licensed under the MIT license. See [LICENSE](LICENSE) for details.



## Report Issues

In case you are experiencing a bug or want to request a new feature head over to the [Charcoal monorepo issue tracker](https://github.com/charcoalphp/charcoal/issues)



## Contribute

The sources of this package are contained in the Charcoal monorepo. We welcome contributions for this package on [charcoalphp/charcoal](https://github.com/charcoalphp/charcoal).


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

For Javascript, the following coding style is enforced:

-	**todo**

Every classes, methods and functions should be covered by unit tests. PHP code can be tested with _PHPUnit_ and Javascript code with _QUnit_.

# Authors

-	Joel Alphonso <joel@locomotive.ca>

# License

Charcoal is licensed under the MIT license. See [LICENSE](LICENSE) for details.



## Report Issues

In case you are experiencing a bug or want to request a new feature head over to the [Charcoal monorepo issue tracker](https://github.com/charcoalphp/charcoal/issues)



## Contribute

The sources of this package are contained in the Charcoal monorepo. We welcome contributions for this package on [charcoalphp/charcoal](https://github.com/charcoalphp/charcoal).


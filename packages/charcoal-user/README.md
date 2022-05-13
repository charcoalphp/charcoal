Charcoal User
=============

User defintion (as Charcoal Model), authentication and authorization (with Laminas ACL).


# Table of content
-   [How to install](#how-to-install)
    -   [Dependencies](#dependencies)
-   [The User object](#the-user-object)
-   [Authentication](#authentication)
-   [Authorization](#authorization)
-   [Development](#development)
    -   [Development dependencies](#development-dependencies)
    -   [Continuous Integration](#continuous-integration)
    -   [Coding Style](#coding-style)
    -   [Authors](#authors)

# How to install

The preferred (and only supported) way of installing _charcoal-user_ is with **composer**:

```shell
★ composer require locomotivemtl/charcoal-user
```

## Dependencies

- PHP 7.1+
- `laminas/laminas-permissions-acl`
- `locomotivemtl/charcoal-object`

# The User object

At the core of this module is the definition of a "User" object. The contract can be found as `\Charcoal\User\UserInterface`. This interfaces extends `\Charcoal\Object\ContentInterface` (from `locomotivemtl/charcoal-object`), which extends `\Charcoal\Model\ModelInterface` (from `locomotivemtl/charcoal-core`).

The preferred way of using this module is by defining your own User class in your project and extending the provided `\Charcoal\User\AbstractUser` class.

For quick prototypes or small projects, a full concrete class is provided as `\Charcoal\User\GenericUser`.

## User properties

| Property                  | Type        | Default     | Description |
| ------------------------- | ----------- | ----------- | ----------- |
| **username**              | `string`    | `true`      | …           |
| **password**              | `string`    | `null`      | …           |
| **email**                 | `string`    | `null`      | …           |
| **roles**                 | `string[]`  | `[]`        | ACL roles, which define user permissions. |
| **last\_login\_date**     | `date-time` | `null`      | …           |
| **last\_login\_ip**       | `string`    | `''`        | …           |
| **last\_password\_date**  | `date-time` | `null`      | …           |
| **last\_password\_ip**    | `string`    | `''`        | …           |
| **login\_token**          | `string`    | `null`      | …           |

> Note that the `key` of the User is the `username`. Therefore, `id()` returns the username. It must be unique.

**Properties inherited from `Content-Interface`:**

| Property                  | Type        | Default     | Description |
| ------------------------- | ----------- | ----------- | ----------- |
| **active**                | `boolean`   | `true`      | …           |
| **position**              | `number`    | `null`      | …           |
| **created**               | `date-time` | `null`      | …           |
| **created\_by**           | `string`    | `''`        | …           |
| **last\_modified**        | `date-time` | `null`      | …           |
| **last\_modified\_by**    | `string`    | `''`        | …           |

# Authentication

...

## Authentication Examples

...

# Authorization

User authorization is managed with a role-based _Access Control List_ (ACL). Internally, it uses [`laminas/laminas-permissions-acl`](https://github.com/laminas/laminas-permissions-acl) for the ACL logic. It is recommended to read the  [Laminas ACL documentation](https://docs.laminas.dev/laminas-permissions-acl/) to learn more about how it all works.

There are 2 main concepts that must be managed, either from JSON config files or in the database (which works well with `locomotivemtl/charcoal-admin`), **roles** and **permissions**.

## ACL Configuration

To set up ACL, it is highly recommended to use the `\Charcoal\User\Acl\Manager`.

## ACL Example

```json
{
    "acl": {
        "permissions": {
            "superuser": {
                "superuser": true
            },
            "author": {
                "allowed": {},
                "denied": {}
            }
        }
    }
}
```

```php
use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Resource\GenericResource as AclResource;

// Dependencies from `charcoal-user`
use Charcoal\User\Acl\Manager as AclManager;

$acl = new Acl();

 // Add resource for ACL
$acl->addResource(new AclResource($resourceName));

$aclManager = new AclManager([
    'logger' => $logger,
]);
$aclManager->loadPermissions($acl, $config['acl.permissions'], $resourceName);

$authorizer = new Authorizer([
    'logger'   => $logger,
    'acl'      => $acl,
    'resource' => $resourceName,
]);

$isAllowed = $authorizer->userAllowed($user, [ 'permssion' ]);
```

# Development

To install the development environment:

```shell
★ composer install --prefer-source
```

To run the scripts (phplint, phpcs and phpunit):

```shell
★ composer test
```

## API documentation

-   The auto-generated `phpDocumentor` API documentation is available at [https://locomotivemtl.github.io/charcoal-user/docs/master/](https://locomotivemtl.github.io/charcoal-user/docs/master/)
-   The auto-generated `apigen` API documentation is available at [https://codedoc.pub/locomotivemtl/charcoal-user/master/](https://codedoc.pub/locomotivemtl/charcoal-user/master/index.html)

## Development dependencies

-   `phpunit/phpunit`
-   `squizlabs/php_codesniffer`
-   `satooshi/php-coveralls`

## Continuous Integration

| Service | Badge | Description |
| ------- | ----- | ----------- |
| [Travis](https://travis-ci.org/locomotivemtl/charcoal-user) | [![Build Status](https://travis-ci.org/locomotivemtl/charcoal-user.svg?branch=master)](https://travis-ci.org/locomotivemtl/charcoal-user) | Runs code sniff check and unit tests. Auto-generates API documentation. |
| [Scrutinizer](https://scrutinizer-ci.com/g/locomotivemtl/charcoal-user/) | [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/locomotivemtl/charcoal-user/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/locomotivemtl/charcoal-user/?branch=master) | Code quality checker. Also validates API documentation quality. |
| [Coveralls](https://coveralls.io/github/locomotivemtl/charcoal-user) | [![Coverage Status](https://coveralls.io/repos/github/locomotivemtl/charcoal-user/badge.svg?branch=master)](https://coveralls.io/github/locomotivemtl/charcoal-user?branch=master) | Unit Tests code coverage. |
| [Sensiolabs](https://insight.sensiolabs.com/projects/533b5796-7e69-42a7-a046-71342146308a) | [![SensioLabsInsight](https://insight.sensiolabs.com/projects/5b05fad5-5e2d-41d3-acd3-12822b354892/mini.png)](https://insight.sensiolabs.com/projects/5b05fad5-5e2d-41d3-acd3-12822b354892) | Another code quality checker, focused on PHP. |

## Coding Style

The charcoal-user module follows the Charcoal coding-style:

-   [_PSR-1_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md)
-   [_PSR-2_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
-   [_PSR-4_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md), autoloading is therefore provided by _Composer_.
-   [_phpDocumentor_](http://phpdoc.org/) comments.
-   Read the [phpcs.xml](phpcs.xml) file for all the details on code style.

> Coding style validation / enforcement can be performed with `composer phpcs`. An auto-fixer is also available with `composer phpcbf`.


> This module should also throw no error when running `phpstan analyse -l7 src/` 👍.

# Authors

-   Mathieu Ducharme, mat@locomotive.ca
-   Chauncey McAskill
-   [Locomotive, a Montreal Web agency](https://locomotive.ca)

# License

Charcoal is licensed under the MIT license. See [LICENSE](LICENSE) for details.

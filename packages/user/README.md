Charcoal User
=============

The User package provides abstract tools for defining user models, authenticating and authorizating users from an integration with [Laminas Permissions ACL](https://github.com/laminas/laminas-permissions-acl).

## Installation

```shell
composer require charcoal/user
```

## Overview

### The User object

At the core of this module is the definition of a "User" object. The contract can be found as `\Charcoal\User\UserInterface`. This interfaces extends `\Charcoal\Object\ContentInterface` (from `charcoal/object`), which extends `\Charcoal\Model\ModelInterface` (from `charcoal/core`).

The preferred way of using this module is by defining your own User class in your project and extending the provided `\Charcoal\User\AbstractUser` class.

For quick prototypes or small projects, a full concrete class is provided as `\Charcoal\User\GenericUser`.

#### User properties

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

### Authentication

TODO

### Authorization

User authorization is managed with a role-based _Access Control List_ (ACL). Internally, it uses [`laminas/laminas-permissions-acl`](https://github.com/laminas/laminas-permissions-acl) for the ACL logic. It is recommended to read the  [Laminas ACL documentation](https://docs.laminas.dev/laminas-permissions-acl/) to learn more about how it all works.

There are 2 main concepts that must be managed, either from JSON config files or in the database (which works well with `charcoal/admin`), **roles** and **permissions**.

#### ACL Configuration

To set up ACL, it is highly recommended to use the `\Charcoal\User\Acl\Manager`.

#### ACL Example

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
use Charcoal\User\Acl\Manager as AclManager;
use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Resource\GenericResource as AclResource;

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

## Resources

* [Contributing](https://github.com/charcoalphp/.github/blob/main/CONTRIBUTING.md)
* [Report issues](https://github.com/charcoalphp/charcoal/issues) and
  [send pull requests](https://github.com/charcoalphp/charcoal/pulls)
  in the [main Charcoal repository](https://github.com/charcoalphp/charcoal)

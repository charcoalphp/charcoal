<div align="center">
    <br/>
    <img alt="Charcoal" width="300" src="assets/docs/images/charcoal-logo-full.svg" />
    <br/>
    by <a href="https://locomotive.ca">Locomotive</a> 🚂
</div>

---

[![License][badge-license]](LICENSE)
[![Latest Stable Version][badge-version]][charcoal-pkg]
[![Uses Semantic Release with Conventional Commits][badge-semantic-release]][semantic-release]
[![Commitizen-friendly][badge-commitizen]][commitizen]
[![Supported PHP Version][badge-php]](composer.json)

Charcoal is a web framework and content management system that adapts to all of
your project's needs.

This repository is a monorepo containing the entirety of the web framework.
Charcoal can be used as a full stack framework or as standalone packages which
can be used independently.

## Charcoal packages

Core packages can be found in the [`packages`](packages/) directory.

| Directory                           | Distribution          | Description                                                                           |
|:------------------------------------|:----------------------|:--------------------------------------------------------------------------------------|
| [`admin`](packages/admin)           | [charcoal/admin]      | The administration dashboard for Charcoal.                                            |
| [`app`](packages/app)               | [charcoal/app]        | The web application layer (container, routing, controllers,…), based on [Slim][slim]. |
| [`attachment`](packages/attachment) | [charcoal/attachment] | Provides support for working with relationships between models.                       |
| [`cache`](packages/cache)           | [charcoal/cache]      | The cache layer, based on [Stash][tedivm/stash].                                      |
| [`cms`](packages/cms)               | [charcoal/cms]        | Provides content management system (CMS) tools and extensions.                        |
| [`config`](packages/config)         | [charcoal/config]     | The base configuration and entity layer most packages build upon.                     |
| [`core`](packages/core)             | [charcoal/core]       | The model, repository, and database layer.                                            |
| [`email`](packages/email)           | [charcoal/email]      | Provides support for sending emails, based on [PHPMailer][phpmailer].                 |
| [`factory`](packages/factory)       | [charcoal/factory]    | Provides support for object creation (factory, builder, class resolution,…).          |
| [`image`](packages/image)           | [charcoal/image]      | Provides support for image handling and manipulation.                                 |
| [`object`](packages/object)         | [charcoal/object]     | Provides support for advanced modeling (routable, revisionable, authoriship,…).       |
| [`property`](packages/property)     | [charcoal/property]   | The model metadata layer.                                                             |
| [`queue`](packages/queue)           | [charcoal/queue]      | Provides support for building queues.                                                 |
| [`translator`](packages/translator) | [charcoal/translator] | Provides support for internationalization, based on [Symfony][symfony/translation].   |
| [`ui`](packages/ui)                 | [charcoal/ui]         | Provides layout tools (dashboards, layouts, forms, menus,…).                          |
| [`user`](packages/user)             | [charcoal/user]       | The user modeling, authentication, and authorization layer.                           |
| [`view`](packages/view)             | [charcoal/view]       | The view layer with support for [Mustache][mustache], [Twig][twig],…                  |


## Installation

The preferred (and only supported) method is with Composer:

```shell
composer require charcoal/charcoal
```

To start from a working skeleton:

```shell
composer create-project charcoal/boilerplate
```

### Standalone packages

The Charcoal framework is split into standalone packages which can be used
independently. For example, a project might not need an administration panel,
a queue system, or to send emails.

```shell
composer require charcoal/app charcoal/api custom/admin 
```

### Migrate a project to `charcoal/charcoal`

The following will aide with converting a project from
`locomotivemtl/charcoal-*` to `charcoal/*`.

> ℹ️ Previously all core packages maintained their own version numbering independently.
>
> The monorepo framework uses a shared version number for all core packages
> for consistent and expected interoperability.

> ℹ️ The contrib packages continue to maintain their existing independent version numbering.

<details>
    <summary>Option A — If you want to replace all packages with the full-stack framework package:</summary>

1. Remove requirements for core packages (`locomotivemtl/charcoal-*`)
   in your `composer.json` file.
2. Replace requirements for contrib packages  (`locomotivemtl/charcoal-contrib-*`),
   in your `composer.json` file, with equivalents from [`charcoal/contrib-*`][charcoal-org].
3. Run `composer require charcoal/charcoal` to install the framework.
4. Run `composer update` to ensure all requirements are up-to-date.
5. Run the following migration script:

   ```shell
   ./vendor/charcoal/charcoal/build/script/migrate-project
   ```
   
   The `migrate-project` script will update all metadata paths in your project's
   configuration files. Afterwards, it will edit or create a `.env` environment
   variable file with the key `PACKAGES_PATH` set to:
   `vendor/charcoal/charcoal/packages`.

   This allows the `%packages.path%` string template to expand to the packages
   location within `charcoal/charcoal`, otherwise it will lead in the `vendor`
   directory.

</details>

<details>
    <summary>Option B — If you want to replace all packages with new standalone packages:</summary>

1. Replace requirements for core packages (`locomotivemtl/charcoal-*`),
   in your `composer.json` file.
2. Replace requirements for contrib packages  (`locomotivemtl/charcoal-contrib-*`),
   in your `composer.json` file, with equivalents from [`charcoal/contrib-*`][charcoal-org].
3. Run `composer require charcoal/config charcoal/core…` to install the packages.
4. Run `composer update` to ensure all requirements are up-to-date.
5. Replace occurrences of `vendor/locomotivemtl/charcoal-*` in your configuration
   files with `vendor/charcoal/*`.

</details>

### Dependencies

#### ⚠️ Required

* [PHP](https://php.net) 7.4 or 8.0

## ⚙️ Configuration

[TODO]

## Usage

[TODO]

## Development

Development is made in a seperate branch from the `main` branch. 

> ⚠️ The `main` branch is protected and doesn't allow pushing changes directly into.

To install the development environment:

```shell
composer install
```

To run the scripts (phplint, phpcs, and phpunit):

```shell
composer test
```

### Commit message format

Charcoal uses [semantic-release] to handle the release process.

It uses the commit messages to determine the consumer impact of changes
in the codebase. Following formalized conventions for commit messages,
[semantic-release] automatically determines the next [Semantic Version][semver]
number, generates a changelog, and publishes the release.

The current setup uses the [Conventional Commits][conventional-commits] for
commit messages. You can consult it for further information.

This repository is [Commitizen][commitizen] friendly and is configured to use
the [Conventional Commits][conventional-commits] standard, therfore you can
install it globally to ease the process of writting commits.

Alternatively, there is some code editor plugins that can help with the creation
of conventional commits:

* `vscode`
    * [VSCode Conventional Commits](https://marketplace.visualstudio.com/items?itemName=vivaxy.vscode-conventional-commits#:~:text=You%20can%20access%20VSCode%20Conventional,on%20the%20Source%20Control%20menu.)
* `phpstrom`
    * [Conventional Commit](https://plugins.jetbrains.com/plugin/13389-conventional-commit)
    * [Git Commit Template](https://plugins.jetbrains.com/plugin/9861-git-commit-template)

Here is an example of release types based on some commit messages:

* Patch (Fix) release:

    ```yaml
    fix(pencil): stop graphite breaking when too much pressure applied
    ```

* Minor (Feature) release:

    ```yaml
    feat(pencil): add 'graphiteWidth' option
    ```

* Major (Breaking) release:

    ```yaml
    perf(pencil): remove graphiteWidth option

    BREAKING CHANGE: The graphiteWidth option has been removed.
    The default graphite width of 10mm is always used for performance reasons.
    ```

    > ✍🏻 Note that the `BREAKING CHANGE: ` token must be in the foot
    > of the commit.

### Development guidelines

Development should be branch-based and commit messages should following
Conventional Commits.

| Steps                                                                                                                                     | Notes                                                                                                                                                                                                                                       |
|:------------------------------------------------------------------------------------------------------------------------------------------|:--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| 1. Branch from `main` or checkout `develop`                                                                                               | Make sure the `develop` branch is up to date with `main`. You should favor a new branch if the needed work time is not short. On a personal branch, favor using the `rebase` method to keep up to date with the `main` branch               |
| 2. Do your thing                                                                                                                          | Write some code                                                                                                                                                                                                                             |
| 3. Commit your changes using the [Conventional Commits][conventional-commits] standard                                                    | You can use [Commitizen][commitizen] or a code editor plugin to help with this process. See the [Commit message format](#commit-message-format) section for more information.                                                               |
| 4. Push to a remote branch and run the `./create-pr` script.                                                                              | Using the `./create-pr` to script to create a PR is not mandatory. You could always create it manually, but the script will be faster, generates a changelog message and assigns a reviewer from the [@charcoalphp/reviewers][gh-reviewers] |
| 5. Wait for a review and a merge to happen                                                                                                | Drink ☕️ and eat 🍕                                                                                                                                                                                                                         |
| 6. After the merge is done, github workflows will handle the release process, tagging, updating dependencies and updatting the changelog. | Good Job ! 🤘                                                                                                                                                                                                                               |


### Maintenance and automations

Symplify's [MonorepoBuilder][symplify/monorepo-builder] is used to handle the
conformity between the core repo and it's packages. It will sync `composer.json`
files and packages versions.

[TODO] Semantic release config in .releaserc

[TODO] [Conventional Commits][conventional-commits]

[TODO] [Commitizen][commitizen]

### Development Dependencies

* [symplify/monorepo-builder]
    * Keeps packages versions in sync.
    * Config is located in [**monorepo-builder**](monorepo-builder.php).
      It allows to define more dependencies
* [semantic-release]
    * Handle the release process from a [Github action](https://github.com/cycjimmy/semantic-release-action).

### Development History

This monorepo was created with a many to mono aproach using this guide and tool:

* [hraban/tomono](https://github.com/hraban/tomono)

### Github Actions

| Actions                                                           | Trigger                        | Description                                                                                                                                                                                                        |
|:------------------------------------------------------------------|:-------------------------------|:-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| [Release](.github/workflows/release.yml)                          | Push on supported branches     | Trigger a Github release using [semantic-release](https://github.com/marketplace/actions/action-for-semantic-release)                                                                                              |
| [Split Monorepo](.github/workflows/split_monorepo.yml)            | Release on `main`              | The split action splits the packages into individual repositories. Only triggered when a tag is pushed. Based on [symplify/monorepo-split-github-action](https://github.com/symplify/monorepo-split-github-action) |
| [Update Changelog](.github/workflows/update-changelog.yml)        | Release on `main`              | Uses [changelog-updater-action](https://github.com/stefanzweifel/changelog-updater-action) to update the changelog of the `main` branch                                                                            |

### Scripts

#### __create-pr__

This script streamlines the process of creating a Pull Request. When your branch is ready to be pulled into the `main` or 
another `[target]` branch, this tool will generate it for you, request review form [@charcoalphp/reviewers][gh-reviewers] and add
a beautiful and readable release note generated from the differences between the two breanches. 

Documentation

```shell
Description
  Create a pull request on the github repository on the requested branch.
  Default branch: main

Usage
  ./create-pr

Options
  -b, --base          The base branch to merge into for the pull request. [Default: main]
  -h, --head          The branch to compare against the base branch. [Default: The current branch]
```

Example
```shell
# target: the target branch for the pull request. Defaults to [main]
./create-pr -b main -h user:feat-branch
```

#### __create-release-notes__ (_optional tool_)

This script generates release notes on request, returning a changelog based on the requested `range of commits` or `branches`.

Documentation:

```shell
./build/script/create-release-notes --help
```

Example:

```shell
./build/script/create-release-notes -g --from main
```

Output:

> ## Changes:
> 
> ### Features
> 
> * **create-pr:** add a script to trigger a pull request on the remote ([3016115](https://github.com/charcoalphp/charcoal/commit/3016115d4f7c919261c54e3a17ae6c36552e532a))
> 
> 
> ### Bug Fixes
> 
> * **create-pr:** remove Personal access token from script and replace with `$GITHUB_TOKEN` instead ([f2aaac6](https://github.com/charcoalphp/charcoal/commit/f2aaac6dbd630f0f8fa759e49f9f41c957e3868a))
> * **package:** add missing semantic-release plugin ([59bd1b1](https://github.com/charcoalphp/charcoal/commit/59bd1b1798e4e7b6bf874c7ba8ecbae19d76342b))

## Contributing

Please see [CONTRIBUTION.md](CONTRIBUTION.md) for guidelines on how to contribute to the **Charcoal** framework.

## ✍🏻 Authors

* [Locomotive][locomotive] 🚂
* [Mathieu Ducharme](mailto:mat@locomotive.ca) 👨🏻‍💻
* [Chauncey McAskill](mailto:chauncey@locomotive.ca) 👨🏻‍💻
* [Joel Alphonso](mailto:joel@locomotive.ca) 👨🏻‍💻
* [Dominic Lord](mailto:dom@locomotive.ca) 👨🏻‍💻
* [Benjamin Roch](mailto:ben@locomotive.ca) 👨🏻‍💻

## 🎉 Contributors

[![List of contributors](https://contrib.rocks/image?repo=charcoalphp/charcoal)][gh-contributors]

Made with [contrib.rocks](https://contrib.rocks).

##  Changelog

View [CHANGELOG](CHANGELOG.md).

The changelog is compliant with [*Keep a Changelog*][keepachangelog] and is autogenerated from autoreleases.

## License

Charcoal is licensed under the MIT license. See [LICENSE](LICENSE) for details.

[charcoal-org]:              https://github.com/charcoalphp
[charcoal-pkg]:              https://packagist.org/packages/charcoal/charcoal
[charcoal/admin]:            https://github.com/charcoalphp/admin
[charcoal/app]:              https://github.com/charcoalphp/app
[charcoal/attachment]:       https://github.com/charcoalphp/attachment
[charcoal/cache]:            https://github.com/charcoalphp/cache
[charcoal/charcoal]:         https://github.com/charcoalphp/charcoal
[charcoal/cms]:              https://github.com/charcoalphp/cms
[charcoal/config]:           https://github.com/charcoalphp/config
[charcoal/core]:             https://github.com/charcoalphp/core
[charcoal/email]:            https://github.com/charcoalphp/email
[charcoal/factory]:          https://github.com/charcoalphp/factory
[charcoal/image]:            https://github.com/charcoalphp/image
[charcoal/object]:           https://github.com/charcoalphp/object
[charcoal/property]:         https://github.com/charcoalphp/property
[charcoal/queue]:            https://github.com/charcoalphp/queue
[charcoal/translator]:       https://github.com/charcoalphp/translator
[charcoal/ui]:               https://github.com/charcoalphp/ui
[charcoal/user]:             https://github.com/charcoalphp/user
[charcoal/view]:             https://github.com/charcoalphp/view

[commitizen]:                https://github.com/commitizen/cz-cli
[conventional-commits]:      https://www.conventionalcommits.org/en/v1.0.0/
[gh-contributors]:           https://github.com/charcoalphp/charcoal/graphs/contributors
[gh-reviewers]:              https://github.com/orgs/charcoalphp/teams/reviewers
[keepachangelog]:            https://keepachangelog.com/en/1.0.0/
[locomotive]:                https://locomotive.ca
[mustache]:                  https://github.com/bobthecow/mustache.php
[phpmailer]:                 https://github.com/PHPMailer/PHPMailer
[semantic-release]:          https://github.com/semantic-release/semantic-release
[semver]:                    https://semver.org
[slim]:                      https://github.com/slimphp/slim
[symfony/translation]:       https://github.com/symfony/translation
[symplify/monorepo-builder]: https://github.com/symplify/monorepo-builder
[tedivm/stash]:              https://github.com/tedious/Stash
[twig]:                      https://github.com/twigphp/Twig

[badge-commitizen]:          https://img.shields.io/badge/commitizen-friendly-brightgreen.svg?style=flat-square
[badge-license]:             https://img.shields.io/packagist/l/charcoal/charcoal.svg?style=flat-square
[badge-php]:                 https://img.shields.io/packagist/php-v/charcoal/charcoal?style=flat-square&logo=php
[badge-semantic-release]:    https://img.shields.io/badge/semantic--release-conventionalcommits-e10079?logo=semantic-release&style=flat-square
[badge-version]:             https://img.shields.io/packagist/v/charcoal/charcoal.svg?style=flat-square&logo=packagist

[psr-1]:  https://www.php-fig.org/psr/psr-1/
[psr-2]:  https://www.php-fig.org/psr/psr-2/
[psr-3]:  https://www.php-fig.org/psr/psr-3/
[psr-4]:  https://www.php-fig.org/psr/psr-4/
[psr-6]:  https://www.php-fig.org/psr/psr-6/
[psr-7]:  https://www.php-fig.org/psr/psr-7/
[psr-11]: https://www.php-fig.org/psr/psr-11/
[psr-12]: https://www.php-fig.org/psr/psr-12/

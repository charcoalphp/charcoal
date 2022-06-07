<div align="center">
    <img alt="charcoal" src="charcoal-logo.png"/>
    <h1>Charcoal Framework - Web Fuel</h1>
</div>

[![License][badge-license]][charcoal]
[![Latest Stable Version][badge-version]][charcoal]
[![semantic-release: conventionalcommits][badge-semantic-release]][semantic-release]
[![Commitizen friendly][badge-commitizen]][commitizen]
[![Php version][badge-php]][charcoal]

## A Monorepo for the Charcoal Application suite

This monorepo contains the integrality of the Charcoal Framework that can be used directly within a website project.
You'll find all the different packages in [`/packages`](./packages/) directory. These packages all also individually hosted in `READONLY` format under the [charcoal][charcoal-git].

## Charcoal packages

| Package                                                                             | Description |
|-------------------------------------------------------------------------------------|-------------|
| [`charcoal-admin`](https://github.com/locomotive-charcoal/charcoal-admin)           |             |
| [`charcoal-app`](https://github.com/locomotive-charcoal/charcoal-app)               |             |
| [`charcoal-attachment`](https://github.com/locomotive-charcoal/charcoal-attachment) |             |
| [`charcoal-cache`](https://github.com/locomotive-charcoal/charcoal-cache)           |             |
| [`charcoal-cms`](https://github.com/locomotive-charcoal/charcoal-cms)               |             |
| [`charcoal-config`](https://github.com/locomotive-charcoal/charcoal-config)         |             |
| [`charcoal-core`](https://github.com/locomotive-charcoal/charcoal-core)             |             |
| [`charcoal-email`](https://github.com/locomotive-charcoal/charcoal-email)           |             |
| [`charcoal-factory`](https://github.com/locomotive-charcoal/charcoal-factory)       |             |
| [`charcoal-image`](https://github.com/locomotive-charcoal/charcoal-image)           |             |
| [`charcoal-object`](https://github.com/locomotive-charcoal/charcoal-object)         |             |
| [`charcoal-property`](https://github.com/locomotive-charcoal/charcoal-property)     |             |
| [`charcoal-queue`](https://github.com/locomotive-charcoal/charcoal-queue)           |             |
| [`charcoal-translator`](https://github.com/locomotive-charcoal/charcoal-translator) |             |
| [`charcoal-ui`](https://github.com/locomotive-charcoal/charcoal-ui)                 |             |
| [`charcoal-user`](https://github.com/locomotive-charcoal/charcoal-user)             |             |
| [`charcoal-view`](https://github.com/locomotive-charcoal/charcoal-view)             |             |


## Installation

The preferred (and only supported) method is with Composer:

```shell
$ composer require locomotive-charcoal/charcoal
```
> Note that `charcoal` is intended to be run along a `charcoal-app` based project. To start from a boilerplate:
>
> ```shell
> $ composer create-project locomotive-charcoal/boilerplate

### À la carte methode

If possible, allow custom composer require. (TODO)

### Dependencies

#### Required

- [**PHP ^7.4**](https://php.net) || [**PHP ^8.0**](https://php.net)

## Configuration

## Usage

## Development

Development is made in a seperate branch from the ``main`` branch. 

To install the development environment:

```shell
$ composer install
```

To run the scripts (phplint, phpcs, and phpunit):

```shell
$ composer test
```

### Maintenance and Automations

https://github.com/symplify/monorepo-builder monorepo-builder is used to handle the conformity between the core repo and it's packages. It will sync composer.json files and packages versions.

[TODO] Semantic release config in .releaserc

[TODO] Commit convention : https://www.conventionalcommits.org/en/v1.0.0/

[TODO] commitizen https://github.com/commitizen/cz-cli


### Development Dependencies

- [symplify/monorepo-builder](https://github.com/symplify/monorepo-builder)
    - Keeps packages versions in sync.
- [semantic-release](https://github.com/semantic-release/semantic-release) 
    - Handle the release process from a [Github action](https://github.com/cycjimmy/semantic-release-action).


### Development History

This monorepo was created with a many to mono aproach using this guide and tool :

[hraban/tomono](https://github.com/hraban/tomono)


### Github Actions

| Actions                                                                     | Trigger                        | Description                                                                                                                                                                                                        |
|-----------------------------------------------------------------------------|--------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| [Release](.github/workflows/release.yaml)                                   | Push on supported branches     | Trigger a Github release using [semantic-release](https://github.com/marketplace/actions/action-for-semantic-release)                                                                                              |
| [Split&nbsp;Monorepo](.github/workflows/split_monorepo.yaml)                | Release on `main`              | The split action splits the packages into individual repositories. Only triggered when a tag is pushed. Based on [symplify/monorepo-split-github-action](https://github.com/symplify/monorepo-split-github-action) |
| [Update&nbsp;Changelog](.github/workflows/update-changelog.yaml)            | Release on `main`              | Uses [changelog-updater-action](https://github.com/stefanzweifel/changelog-updater-action) to update the changelog of the `main` branch                                                                            |
| [Create&nbsp;Pull&nbsp;Request](.github/workflows/create-pull-request.yaml) | Run the `./create-pr` command  | Create a new _Pull Request_ on the current remote branch with a release note automatically generated.                                                                                                              |


### Scripts

#### __create-pr__

This script streamlines the process of creating a Pull Request. When your branch is ready to be pulled into the `main` or 
another `[target]` branch, this tool will generate it for you, request review form [@charcoalphp/reviewers][reviewers] and add
a beautiful and readable release note generated from the differences between the 2 breanches. 

```shell
# target: the target branch for the pull request. Defaults to [main]
$ ./create-pr [target]
```

#### __create-release-notes__ (_optional tool_)

This script generates release notes on request, returning a changelog based on the requested `range of commits` or `branches`

Documentation:

```shell
$ ./build/script/create-release-notes --help
```

Example:

```shell
$ ./build/script/create-release-notes -g --from main
```

Output:

>## Changes :
>
>### Features
>
>* **create-pr:** add a script to trigger a pull request on the remote ([3016115](https://github.com/locomotive-charcoal/charcoal/commit/3016115d4f7c919261c54e3a17ae6c36552e532a))
>
>
>### Bug Fixes
>
>* **create-pr:** remove Personal access token from script and replace with $GITHUB_TOKEN instead ([f2aaac6](https://github.com/locomotive-charcoal/charcoal/commit/f2aaac6dbd630f0f8fa759e49f9f41c957e3868a))
>* **package:** add missing semantic-release plugin ([59bd1b1](https://github.com/locomotive-charcoal/charcoal/commit/59bd1b1798e4e7b6bf874c7ba8ecbae19d76342b))

## Credits

- [Locomotive](https://locomotive.ca/)
- [Joel Alphonso](mailto:joel@locomotive.ca)


## Contributors

[![contributors](https://contrib.rocks/image?repo=Locomotive-Charcoal/charcoal)](https://github.com/Locomotive-Charcoal/charcoal/graphs/contributors)

Made with [contrib.rocks](https://contrib.rocks).

## Changelog

View [CHANGELOG](docs/CHANGELOG.md).

The changelog is compliant with [*keepachangelog*](https://keepachangelog.com/en/1.0.0/) and is autogenerated from autoreleases.

## License

Charcoal is licensed under the MIT license. See [LICENSE](LICENSE) for details.

[charcoal]:         https://packagist.org/packages/locomotive-charcoal/charcoal
[charcoal-git]:     https://github.com/locomotive-charcoal
[semantic-release]: https://github.com/semantic-release/semantic-release
[commitizen]:       http://commitizen.github.io/cz-cli/
[reviewers]:        https://github.com/orgs/locomotive-charcoal/teams/reviewers

[badge-license]:            https://img.shields.io/packagist/l/locomotive-charcoal/charcoal.svg?style=flat-square
[badge-version]:            https://img.shields.io/packagist/v/locomotive-charcoal/charcoal.svg?style=flat-square&logo=packagist
[badge-php]:                https://img.shields.io/packagist/php-v/locomotive-charcoal/charcoal?style=flat-square&logo=php
[badge-semantic-release]:   https://img.shields.io/badge/semantic--release-conventionalcommits-e10079?logo=semantic-release&style=flat-square
[badge-commitizen]:         https://img.shields.io/badge/commitizen-friendly-brightgreen.svg?style=flat-square

[psr-1]:  https://www.php-fig.org/psr/psr-1/
[psr-2]:  https://www.php-fig.org/psr/psr-2/
[psr-3]:  https://www.php-fig.org/psr/psr-3/
[psr-4]:  https://www.php-fig.org/psr/psr-4/
[psr-6]:  https://www.php-fig.org/psr/psr-6/
[psr-7]:  https://www.php-fig.org/psr/psr-7/
[psr-11]: https://www.php-fig.org/psr/psr-11/
[psr-12]: https://www.php-fig.org/psr/psr-12/

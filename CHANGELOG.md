# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

> ⚠️ This `CHANGELOG` file is automatically generated through Github actions from Github release notes.

## [Unreleased](https://github.com/locomotive-charcoal/Charcoal/compare/v2.1.0...main)

Please do not update the unreleased notes.

## [v2.1.0](https://github.com/locomotive-charcoal/Charcoal/compare/v2.0.1...v2.1.0) - 2022-06-21

### [2.1.0](https://github.com/charcoalphp/charcoal/compare/v2.0.1...v2.1.0) (2022-06-21)

#### Features

- add template tags supports for configurable paths where needed ([f3afb94](https://github.com/charcoalphp/charcoal/commit/f3afb94bfd0e563b043e315bb9c34a50d2c4c40d))
- **app:** add %packages.path% string template ([3efadb9](https://github.com/charcoalphp/charcoal/commit/3efadb91c99dc63a5e1fc1c37ae33fc0c3e98fc5))
- **dotenv:** add dotenv support to App.php ([70b6bd7](https://github.com/charcoalphp/charcoal/commit/70b6bd73a6cf946629fd2d1c721da4469188d622))
- **migration:** add a migration script for port charcoal projects to new framework ([c9cc66a](https://github.com/charcoalphp/charcoal/commit/c9cc66ad72ecc1adc44669edc5be2f69edf9a825))

#### Bug Fixes

- fix missing return type ([24c7f57](https://github.com/charcoalphp/charcoal/commit/24c7f5774826080f5bb7eec01c71c4b450810d66))
- **translator:** add missing directory separator for translation files ([83d5a30](https://github.com/charcoalphp/charcoal/commit/83d5a30ab10c745639076cd9ca3bec78e971bb0f))
- update and improve migrate-project script ([d234b26](https://github.com/charcoalphp/charcoal/commit/d234b2645064662fe1729f9d20a4533a468c451b))

## [v2.0.1](https://github.com/locomotive-charcoal/Charcoal/compare/v2.0.0...v2.0.1) - 2022-06-13

### [2.0.1](https://github.com/charcoalphp/charcoal/compare/v2.0.0...v2.0.1) (2022-06-13)

#### Bug Fixes

- add missing arguments aliases in create-release-notes ([eac7d4e](https://github.com/charcoalphp/charcoal/commit/eac7d4e520abc8b83cbea4133c42d404f8a3a6c8))
- **composer:** rename all missing composer packages names ([e879f14](https://github.com/charcoalphp/charcoal/commit/e879f140f96840aa8c8631114ded09039580f381))

## [v2.0.0](https://github.com/locomotive-charcoal/Charcoal/compare/v1.3.4...v2.0.0) - 2022-06-08

### Changes :

#### ⚠ BREAKING CHANGES

- The location of the packages must be changed everywhere they are called.

#### Code Refactoring

- remove the "charcoal-" prefix from the package names ([3302354](https://github.com/locomotive-charcoal/Charcoal/commit/3302354378025fe038f5b8091d8b54dfb50ba54a))

## [v1.3.4](https://github.com/locomotive-charcoal/Charcoal/compare/v1.3.3...v1.3.4) - 2022-06-08

### Changes :

#### Bug Fixes

- **action:** add token to checkout in update-changelog.yaml ([20a51aa](https://github.com/locomotive-charcoal/Charcoal/commit/20a51aa09fbd8eff49100d68654a3fddf84f5bc0))
- Regenerate composer.lock from PHP 7.4 ([4d1eddd](https://github.com/locomotive-charcoal/Charcoal/commit/4d1edddccf66630b5582ef7e7897df4e8f9acc4a))

## [v1.3.3](https://github.com/locomotive-charcoal/Charcoal/compare/v1.1.1...v1.3.3) - 2022-06-08

### Changes :

#### Bug Fixes

- **action:** add token to checkout in update-changelog.yaml ([f491bbd](https://github.com/locomotive-charcoal/Charcoal/commit/f491bbd8456f19882b3ee1bd6358b8681b62e8d8))

## [v1.1.1](https://github.com/locomotive-charcoal/Charcoal/compare/v1.1.0...v1.1.1) - 2022-06-01

### Bug Fixes

- **gitignore:** remove CHANGELOG from gitignore ([d695b92](https://github.com/locomotive-charcoal/Charcoal/commit/d695b927cba3ae2fd7033c58db36b4b0956cb3bc))
- **release:** remove pull_request event from release action ([1b64881](https://github.com/locomotive-charcoal/Charcoal/commit/1b648818bd7f828cd03b0747e9318ee386b54982))
- **release:** remove pull_request generation from semantic release workflow ([3027fcb](https://github.com/locomotive-charcoal/Charcoal/commit/3027fcbfbf34dc813c73b1d6ba54591e9d6817ee))

## [v1.1.0](https://github.com/locomotive-charcoal/Charcoal/compare/v1.0.0...v1.1.0) - 2022-05-31

### Bug Fixes

- **changelog:** add title and description to changelog generation ([792cfbf](https://github.com/locomotive-charcoal/Charcoal/commit/792cfbfcdfb9755494321fbd82908ffd8ae2a2b7))
- **changelog:** remove duplicated blocks ([4bb817f](https://github.com/locomotive-charcoal/Charcoal/commit/4bb817f2218b1a0e2c72e56dfb207e50fd9743ad))
- **readme:** fix some html errors/typos ([abb0e9f](https://github.com/locomotive-charcoal/Charcoal/commit/abb0e9fa4e2b7540d691e953c3125cbc8ea2f0a5))
- **readme:** fix some html errors/typos ([7c16128](https://github.com/locomotive-charcoal/Charcoal/commit/7c1612873e91836126ec23ee8c405b80f14047a6))
- **release:** test commit for release ([20d9990](https://github.com/locomotive-charcoal/Charcoal/commit/20d9990990835255d69a09be12c91fae24f04e89))
- **release:** test commit for release ([a0a0c55](https://github.com/locomotive-charcoal/Charcoal/commit/a0a0c55b7110f76d202a01d19061a82e07d26112))
- **release:** test commit for release ([a327480](https://github.com/locomotive-charcoal/Charcoal/commit/a327480d9892d7c2c9d0f400fcd21b29ebd4b0be))
- **release:** test commit for release ([9021792](https://github.com/locomotive-charcoal/Charcoal/commit/90217923b5a445bfe49460fa748ea52392224416))
- **typehint:** fix a missing return type conflict ([b9d31c1](https://github.com/locomotive-charcoal/Charcoal/commit/b9d31c1f89f59cc122db39490abfdcf6474cce16))
- **typehint:** fix a missing return type conflict ([df8938c](https://github.com/locomotive-charcoal/Charcoal/commit/df8938c740afcbc6b2e3d616ad264d2d6c5153e0))
- **typo:** fix typo for Translator mustache helper ([dc7d20a](https://github.com/locomotive-charcoal/Charcoal/commit/dc7d20af5c23e5d03cf1e952eb67390db0fd6e02))
- **typo:** fix typo for Translator mustache helper ([3e82bb7](https://github.com/locomotive-charcoal/Charcoal/commit/3e82bb77dd9fd8ae492503ff960a3dbc255af85e))

### Features

- **changelog:** add title and description to changelog generation ([93b8f2a](https://github.com/locomotive-charcoal/Charcoal/commit/93b8f2ae9b01c82a714c87b8b9257daa42e60288))
- **release:** add github pull-request ([79e6915](https://github.com/locomotive-charcoal/Charcoal/commit/79e69154876231c292e0c1c30c37a4a3dc15197d))
- **release:** add github pull-request to .releaserc ([8a5f33e](https://github.com/locomotive-charcoal/Charcoal/commit/8a5f33eafe6f9f7e252c4ee75b9b7d24c21f00e6))

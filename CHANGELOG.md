# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

> ⚠️ This `CHANGELOG` file is automatically generated through Github actions from Github release notes.

## [Unreleased](https://github.com/locomotive-charcoal/Charcoal/compare/v3.0.10...main)

Please do not update the unreleased notes.

## [v3.0.10](https://github.com/locomotive-charcoal/Charcoal/compare/v3.0.9...v3.0.10) - 2022-08-01

### [3.0.10](https://github.com/charcoalphp/charcoal/compare/v3.0.9...v3.0.10) (2022-08-01)

#### Bug Fixes

- **action:** try to fix split-monorepo.yaml ([57d3cea](https://github.com/charcoalphp/charcoal/commit/57d3cea47d8bc8139f64bdf9f09dfedf06324a54))

## [v3.0.9](https://github.com/locomotive-charcoal/Charcoal/compare/v3.0.8...v3.0.9) - 2022-08-01

### [3.0.9](https://github.com/charcoalphp/charcoal/compare/v3.0.8...v3.0.9) (2022-08-01)

#### Bug Fixes

- **action:** try to fix split-monorepo.yaml ([e08454d](https://github.com/charcoalphp/charcoal/commit/e08454dd0874dc46074dd32bc3d1d0233a05a839))

## [v3.0.8](https://github.com/locomotive-charcoal/Charcoal/compare/v3.0.7...v3.0.8) - 2022-08-01

### [3.0.8](https://github.com/charcoalphp/charcoal/compare/v3.0.7...v3.0.8) (2022-08-01)

#### Bug Fixes

- **action:** try to fix split-monorepo.yaml ([8b4d735](https://github.com/charcoalphp/charcoal/commit/8b4d735374ec874e196e9706278ecb919bd25045))

## [v3.0.7](https://github.com/locomotive-charcoal/Charcoal/compare/v3.0.6...v3.0.7) - 2022-08-01

### [3.0.7](https://github.com/charcoalphp/charcoal/compare/v3.0.6...v3.0.7) (2022-08-01)

#### Bug Fixes

- **action:** try to fix split-monorepo.yaml ([5bde1e2](https://github.com/charcoalphp/charcoal/commit/5bde1e2ced79c9b92b5aa009a786c0c1fd636c8b))

## [v3.0.6](https://github.com/locomotive-charcoal/Charcoal/compare/v3.0.5...v3.0.6) - 2022-08-01

### [3.0.6](https://github.com/charcoalphp/charcoal/compare/v3.0.5...v3.0.6) (2022-08-01)

#### Bug Fixes

- **action:** try to fix split-monorepo.yaml ([12bc8a5](https://github.com/charcoalphp/charcoal/commit/12bc8a5e6bdaf8bbb94cc1800d39d2b750dea2f9))

## [v3.0.5](https://github.com/locomotive-charcoal/Charcoal/compare/v3.0.4...v3.0.5) - 2022-08-01

### [3.0.5](https://github.com/charcoalphp/charcoal/compare/v3.0.4...v3.0.5) (2022-08-01)

#### Bug Fixes

- **action:** try to fix split-monorepo.yaml ([31c12ff](https://github.com/charcoalphp/charcoal/commit/31c12ff56710c2b74c6a377c47b7d9147541f825))

## [v3.0.4](https://github.com/locomotive-charcoal/Charcoal/compare/v3.0.3...v3.0.4) - 2022-08-01

### [3.0.4](https://github.com/charcoalphp/charcoal/compare/v3.0.3...v3.0.4) (2022-08-01)

#### Bug Fixes

- **action:** try to fix split-monorepo.yaml ([6ab2aee](https://github.com/charcoalphp/charcoal/commit/6ab2aeea1f20fe617af54b484890fb3967a3abc3))

## [v3.0.3](https://github.com/locomotive-charcoal/Charcoal/compare/v3.0.2...v3.0.3) - 2022-08-01

### [3.0.3](https://github.com/charcoalphp/charcoal/compare/v3.0.2...v3.0.3) (2022-08-01)

#### Bug Fixes

- **trigger:** trigger release ([b0fc93f](https://github.com/charcoalphp/charcoal/commit/b0fc93f666ea307a547af5eacde062b68ac47860))

## [v3.0.2](https://github.com/locomotive-charcoal/Charcoal/compare/v3.0.1...v3.0.2) - 2022-07-27

### [3.0.2](https://github.com/charcoalphp/charcoal/compare/v3.0.1...v3.0.2) (2022-07-27)

#### Bug Fixes

- **user:** fix __toString compatibility with AbstractModel on Acl Permission and Acl Role ([7ade0c0](https://github.com/charcoalphp/charcoal/commit/7ade0c05f6d6dd047d3320ff157e6b47b829bb58))

## [v3.0.1](https://github.com/locomotive-charcoal/Charcoal/compare/v3.0.0...v3.0.1) - 2022-07-20

### [3.0.1](https://github.com/charcoalphp/charcoal/compare/v3.0.0...v3.0.1) (2022-07-20)

#### Bug Fixes

- **admin:** Typo with property inputPrefix ([28b5554](https://github.com/charcoalphp/charcoal/commit/28b55545ca8ab1689cdb0de898375b9dd99329de))

## [v3.0.0](https://github.com/locomotive-charcoal/Charcoal/compare/v2.3.0...v3.0.0) - 2022-07-20

### [3.0.0](https://github.com/charcoalphp/charcoal/compare/v2.3.0...v3.0.0) (2022-07-20)

#### ⚠ BREAKING CHANGES

- (BC):
- 
- Method `widgetOptions()` now returns an array instead of a JSON serialized string to allow for easier overriding in subclasses.
- 

Added:

- Methods `widgetOptionsAsJson()` and `escapedWidgetOptionsAsJson()` for rendering widget options as JSON.
- Method `widgetDataForJs()` to replace hardcoded widget data options in view file.

Changed:

- Stringify the widget's title.

#### Features

- **admin:** Add new group display mode for quick form widget ([524ec25](https://github.com/charcoalphp/charcoal/commit/524ec25d7e65b93b2bd3625f9dd0cb0d41561e36)), closes [locomotivemtl/charcoal-admin#63](https://github.com/locomotivemtl/charcoal-admin/issues/63) [locomotivemtl/charcoal-admin#d27a30e44b0e6e06899a481322015b09e743dc82](https://github.com/locomotivemtl/charcoal-admin/issues/d27a30e44b0e6e06899a481322015b09e743dc82)
- **admin:** Improve dialog in widget.js ([1855d3b](https://github.com/charcoalphp/charcoal/commit/1855d3bb71e417a0016844e65bbabfda198defbd)), closes [locomotivemtl/charcoal-admin#8eb005a375875a7158537898c357b050ab066050](https://github.com/locomotivemtl/charcoal-admin/issues/8eb005a375875a7158537898c357b050ab066050)
- **admin:** Improve error handling in form.js ([8340eb6](https://github.com/charcoalphp/charcoal/commit/8340eb6378dc38ef54094a2d0acd18148e4cc9b0)), closes [locomotivemtl/charcoal-admin#6659001b72230e62f353ac533e449ffec40221d4](https://github.com/locomotivemtl/charcoal-admin/issues/6659001b72230e62f353ac533e449ffec40221d4)
- **admin:** Improve generic graph widgets ([b8a03f6](https://github.com/charcoalphp/charcoal/commit/b8a03f6993f2f40468cc0b15181a67afe6c57530)), closes [locomotivemtl/charcoal-admin#8e482bd13688c8b3d2770078424b6117eba3d0](https://github.com/locomotivemtl/charcoal-admin/issues/8e482bd13688c8b3d2770078424b6117eba3d0)
- **admin:** Improve reloading in widget.js ([f409aac](https://github.com/charcoalphp/charcoal/commit/f409aac22b752fcb762c0e76a829cf1c6e0f8922)), closes [locomotivemtl/charcoal-admin#d8d977c1609515b144837d1d1ca3f11677972](https://github.com/locomotivemtl/charcoal-admin/issues/d8d977c1609515b144837d1d1ca3f11677972)
- **admin:** Improve showLanguageSwitch integration ([41f341f](https://github.com/charcoalphp/charcoal/commit/41f341f51ef6351ba269cb8cc3a2e5fe01074c89)), closes [locomotivemtl/charcoal-admin#9c436ea57c5a37e90f97f90cf78994ab66cd0083](https://github.com/locomotivemtl/charcoal-admin/issues/9c436ea57c5a37e90f97f90cf78994ab66cd0083)
- **admin:** Improve switch language in form.js ([22c30ec](https://github.com/charcoalphp/charcoal/commit/22c30ec406cfac51983cbbf144bf7af14cb66aac)), closes [locomotivemtl/charcoal-admin#e9d29556820f0b2f4e6a6adc127c31dad86fae04](https://github.com/locomotivemtl/charcoal-admin/issues/e9d29556820f0b2f4e6a6adc127c31dad86fae04)
- **admin:** Improve validate/save in component_manager.js ([423c84b](https://github.com/charcoalphp/charcoal/commit/423c84ba4e2a995c09b9c81131bbf02b21ff94bf)), closes [locomotivemtl/charcoal-admin#1759d2bb90297ff7f03c5c94a0211b22dd2796](https://github.com/locomotivemtl/charcoal-admin/issues/1759d2bb90297ff7f03c5c94a0211b22dd2796) [locomotivemtl/charcoal-admin#e5e0741666cb52ca84a09cabce6619a8ec61c7c2](https://github.com/locomotivemtl/charcoal-admin/issues/e5e0741666cb52ca84a09cabce6619a8ec61c7c2)

#### Bug Fixes

- **admin:** Add missing localizations ([9496922](https://github.com/charcoalphp/charcoal/commit/9496922339b674da2baef6a619d0cee6f597d7be)), closes [locomotivemtl/charcoal-admin#47012a22e696bd090adb08ded9954ac1a50e84](https://github.com/locomotivemtl/charcoal-admin/issues/47012a22e696bd090adb08ded9954ac1a50e84)
- **admin:** Ensure widgetL10n is available with attachment.js ([8cea251](https://github.com/charcoalphp/charcoal/commit/8cea2510f62a19bacd19375e83ca6a639e3c8aec)), closes [locomotivemtl/charcoal-attachment#496b9bec978bb01065e59c36e4291fe8e3384](https://github.com/locomotivemtl/charcoal-attachment/issues/496b9bec978bb01065e59c36e4291fe8e3384)
- **admin:** Improve hasL10nFormProperties resolution ([3be40b0](https://github.com/charcoalphp/charcoal/commit/3be40b0acfd6b9a95ac9c552e7071bedeb286d68)), closes [locomotivemtl/charcoal-admin#2c4c3797bb8463241e723e6d157589291c832a0](https://github.com/locomotivemtl/charcoal-admin/issues/2c4c3797bb8463241e723e6d157589291c832a0)
- **admin:** Improve internals of attachment.js ([a387db3](https://github.com/charcoalphp/charcoal/commit/a387db36ff0b3cbc20e1c2e0efa6b2bafaaa736d)), closes [locomotivemtl/charcoal-admin#45135ef015723f319ed246bf28eac4d4d2dccbe4](https://github.com/locomotivemtl/charcoal-admin/issues/45135ef015723f319ed246bf28eac4d4d2dccbe4)
- **admin:** Improve resolveSimpleJsonXhr in charcoal.js ([1eee836](https://github.com/charcoalphp/charcoal/commit/1eee836cf5eecd1035f05776945a2f08a83c0746)), closes [locomotivemtl/charcoal-admin#bc8327e7aadeb57ea6e1ec931051aba4d630d409](https://github.com/locomotivemtl/charcoal-admin/issues/bc8327e7aadeb57ea6e1ec931051aba4d630d409)
- **admin:** Update Bootstrap to v4.6.2 ([1fb8b4a](https://github.com/charcoalphp/charcoal/commit/1fb8b4afa418b6dcbeb30eac8e4e8505efa0222e)), closes [locomotivemtl/charcoal-admin#e6ac7b6f3b447cf08b7d9578b3d888d178a3639](https://github.com/locomotivemtl/charcoal-admin/issues/e6ac7b6f3b447cf08b7d9578b3d888d178a3639)
- **property:** Fix parsing + error handling in Sprite Property ([831f16d](https://github.com/charcoalphp/charcoal/commit/831f16d9fa84392278a9143ecde767785a7bde14)), closes [locomotivemtl/charcoal-property#d1ef3c66122781581a856721fe233a816fc5b0b6](https://github.com/locomotivemtl/charcoal-property/issues/d1ef3c66122781581a856721fe233a816fc5b0b6) [locomotivemtl/charcoal-property#8f3403145b55c43190e447dae1c8f4dea249cc33](https://github.com/locomotivemtl/charcoal-property/issues/8f3403145b55c43190e447dae1c8f4dea249cc33)

#### Performance Improvements

- **admin:** Fix and improve event namespacing in JS components ([db46534](https://github.com/charcoalphp/charcoal/commit/db46534d23e3e281d0ca74a8103fe229d7f5a57d)), closes [locomotivemtl/charcoal-admin#738ae375ed4fa92cdd2ddbdadb2dca3cab8bbe96](https://github.com/locomotivemtl/charcoal-admin/issues/738ae375ed4fa92cdd2ddbdadb2dca3cab8bbe96)
- **admin:** Improve elements in form.js and quickform.js ([81b9cad](https://github.com/charcoalphp/charcoal/commit/81b9cad39e241c4483e8400ccb9a5121efc4159f)), closes [locomotivemtl/charcoal-admin#bee1d393d313fdfc0a8b9a5918a7678a46b33d21](https://github.com/locomotivemtl/charcoal-admin/issues/bee1d393d313fdfc0a8b9a5918a7678a46b33d21)
- **admin:** Improve internals of feedback.js ([cb6a4f6](https://github.com/charcoalphp/charcoal/commit/cb6a4f6484aa1f10b76d3f77f02ed913a2bb617f)), closes [locomotivemtl/charcoal-admin#45b76606fc945ab2526ccdd9db8219b11fb8ca10](https://github.com/locomotivemtl/charcoal-admin/issues/45b76606fc945ab2526ccdd9db8219b11fb8ca10)
- **export:** improve performance of exporter ([9a1de4c](https://github.com/charcoalphp/charcoal/commit/9a1de4ccf2238530c7ae7d18719d228d61ad69aa)), closes [locomotivemtl/charcoal-admin#19192817861a2faed50ad7aeb8b10dfbcc63ce25](https://github.com/locomotivemtl/charcoal-admin/issues/19192817861a2faed50ad7aeb8b10dfbcc63ce25)

#### Reverts

- **admin:** Disable `will_save` constraint in attachment.js ([3e78bcd](https://github.com/charcoalphp/charcoal/commit/3e78bcdd9f6a8efc07778f0db9db5148bf07896f)), closes [locomotivemtl/charcoal-admin#1759d2bb90297ff7f03c5c94a0211b22dd2796](https://github.com/locomotivemtl/charcoal-admin/issues/1759d2bb90297ff7f03c5c94a0211b22dd2796) [locomotivemtl/charcoal-admin#6fb62c401db2192693d09cd42b8b2250d7af57b6](https://github.com/locomotivemtl/charcoal-admin/issues/6fb62c401db2192693d09cd42b8b2250d7af57b6)

#### Code Refactoring

- Integration of Attachment widget data and options ([c24ebc7](https://github.com/charcoalphp/charcoal/commit/c24ebc7f54926d68874a862953812e539330220f)), closes [locomotivemtl/charcoal-attachment#994dcc357626c0fd716b864812dffdc1ca742d93](https://github.com/locomotivemtl/charcoal-attachment/issues/994dcc357626c0fd716b864812dffdc1ca742d93)

## [v2.3.0](https://github.com/locomotive-charcoal/Charcoal/compare/v2.2.5...v2.3.0) - 2022-07-14

### [2.3.0](https://github.com/charcoalphp/charcoal/compare/v2.2.5...v2.3.0) (2022-07-14)

#### Features

- **twig:** first version of Twig implementation in Charcoal using Twig 3.4 ([c1ffed3](https://github.com/charcoalphp/charcoal/commit/c1ffed3c725260364a7d78c70c27ccaf9b88e625))

#### Bug Fixes

- **twig:** fix condition getCacheKey ([01d78c3](https://github.com/charcoalphp/charcoal/commit/01d78c3e17b1e33cade413f023cc9d2a3386e909))
- **twig:** fix Twig UrlHelpers and split functionnality with new DebugHelpers ([bf4056f](https://github.com/charcoalphp/charcoal/commit/bf4056f0f886fe1ccb2e97fdb43ce240f48eef63))
- **twig:** update twig version for dev dependencies ([563f59f](https://github.com/charcoalphp/charcoal/commit/563f59f83dfc62944ac6674702ae8eb40fd4a847))

#### Performance Improvements

- **twig:** apply suggestions from code review ([63a9c4f](https://github.com/charcoalphp/charcoal/commit/63a9c4f46917af30ea2ea015994e8f3078a727cc))

## [v2.2.5](https://github.com/locomotive-charcoal/Charcoal/compare/v2.2.4...v2.2.5) - 2022-07-07

### [2.2.5](https://github.com/charcoalphp/charcoal/compare/v2.2.4...v2.2.5) (2022-07-07)

#### Bug Fixes

- **php-coveralls:** remove old satooshi/php-coveralls dependency ([e890acf](https://github.com/charcoalphp/charcoal/commit/e890acf97c47dced5febd492d4915501a7530962))

## [v2.2.4](https://github.com/locomotive-charcoal/Charcoal/compare/v2.2.3...v2.2.4) - 2022-06-25

### [2.2.4](https://github.com/charcoalphp/charcoal/compare/v2.2.3...v2.2.4) (2022-06-25)

#### Bug Fixes

- **packages-path:** change env var for packages path ([ae369ec](https://github.com/charcoalphp/charcoal/commit/ae369ec29a39fe7fa5b632c079d8e35698b1320d))
- **workflow:** fix a typo in workflow file ([d5d95b5](https://github.com/charcoalphp/charcoal/commit/d5d95b5443f76942b3962a1f7f878de7b2b38864))
- **workflow:** fix workflow token ([0b5db3b](https://github.com/charcoalphp/charcoal/commit/0b5db3b546ec66b14d649b04d32e2fba1949ee5c))

## [v2.2.3](https://github.com/locomotive-charcoal/Charcoal/compare/v2.2.2...v2.2.3) - 2022-06-23

### [2.2.3](https://github.com/charcoalphp/charcoal/compare/v2.2.2...v2.2.3) (2022-06-23)

#### Bug Fixes

- **packages:** :building_construction: add replace options in all packages composer files ([9f1777a](https://github.com/charcoalphp/charcoal/commit/9f1777a74a5ca84755fa38eb671cc42451137798))

## [v2.2.2](https://github.com/locomotive-charcoal/Charcoal/compare/v2.2.1...v2.2.2) - 2022-06-23

### [2.2.2](https://github.com/charcoalphp/charcoal/compare/v2.2.1...v2.2.2) (2022-06-23)

#### Bug Fixes

- **charcoal:** add a TODO for a feature request ([45f59b8](https://github.com/charcoalphp/charcoal/commit/45f59b887c5a36fd76a5350592d781a3b2c969c7))

## [v2.2.1](https://github.com/locomotive-charcoal/Charcoal/compare/v2.2.0...v2.2.1) - 2022-06-22

### [2.2.1](https://github.com/charcoalphp/charcoal/compare/v2.2.0...v2.2.1) (2022-06-22)

#### Bug Fixes

- **composer:** add replace packages for all old charcoal packages ([8bec034](https://github.com/charcoalphp/charcoal/commit/8bec034c5aa379559ad5ea98ddc5d929a09222fd))

## [v2.2.0](https://github.com/locomotive-charcoal/Charcoal/compare/v2.1.2...v2.2.0) - 2022-06-21

### [2.2.0](https://github.com/charcoalphp/charcoal/compare/v2.1.2...v2.2.0) (2022-06-21)

#### Features

- **script:** add .env file creation in migrate-project script ([b302511](https://github.com/charcoalphp/charcoal/commit/b302511559c0ab35dfc7d4c67662aa644352b944))
- **script:** change repo owner and add token validation to create-pr script ([536fabd](https://github.com/charcoalphp/charcoal/commit/536fabdce7f9ef12dabc93922f730d2b0c8dda67))

## [v2.1.2](https://github.com/locomotive-charcoal/Charcoal/compare/v2.1.1...v2.1.2) - 2022-06-21

### [2.1.2](https://github.com/charcoalphp/charcoal/compare/v2.1.1...v2.1.2) (2022-06-21)

#### Bug Fixes

- **action:** fix repository references ([3f1fba2](https://github.com/charcoalphp/charcoal/commit/3f1fba21a5071af651a7525e57ff0edd6f00c580))

## [v2.1.1](https://github.com/locomotive-charcoal/Charcoal/compare/v2.1.0...v2.1.1) - 2022-06-21

### [2.1.1](https://github.com/charcoalphp/charcoal/compare/v2.1.0...v2.1.1) (2022-06-21)

#### Bug Fixes

- **action:** add PAT to split-monorepo.yaml ([171b9bf](https://github.com/charcoalphp/charcoal/commit/171b9bfbd30f4b36a85d521f6393db9e53cc3ae5))

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

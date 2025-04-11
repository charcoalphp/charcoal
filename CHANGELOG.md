# Changelog

## [5.1.0](https://github.com/charcoalphp/charcoal/compare/v5.0.0...v5.1.0) (2025-04-11)


### Features

* **image:** Add compression and format effects for images ([7c49cab](https://github.com/charcoalphp/charcoal/commit/7c49cab8deff4d299c023b3b77e8ab45f7443e41))


### Bug Fixes

* **admin:** Sort Selectize values in order filtered for loading ([69ed8ea](https://github.com/charcoalphp/charcoal/commit/69ed8eaa3efc95c987482a2fe10df234092d7d7e))
* **image:** Fix php units tests for image package ([1f41413](https://github.com/charcoalphp/charcoal/commit/1f414135928e94bb583b8dde36e3595c903ad61b))

## [5.0.0](https://github.com/charcoalphp/charcoal/compare/v4.1.0...v5.0.0) (2024-03-13)


### ⚠ BREAKING CHANGES

* **admin:** The parent model property now receives both its structure/storage property and the structure's sub-property as a dot-delimited key path: `<storageProperty>.<childProperty>`. This fixes the risk of conflicting property identifiers where a structure's child property inherits the parent model's property mutations and fixes the missing context for the structure's property.

### Features

* **admin:** Add custom dialog titles for Selectize input ([ffc3382](https://github.com/charcoalphp/charcoal/commit/ffc338225c86a5788d61bc1c675ec18678e3d14e))
* **admin:** Add JS debounce factory ([7a25409](https://github.com/charcoalphp/charcoal/commit/7a25409e7bf318734cc2ccd497ec3a576b5e9e83))
* **admin:** Add support to customize form widget used by Selectize input ([b407dfb](https://github.com/charcoalphp/charcoal/commit/b407dfb9e9bfc45d8e4c7498498316075f60e796)), closes [#88](https://github.com/charcoalphp/charcoal/issues/88)
* **admin:** Add validation message to TinyMCE on save ([46af62b](https://github.com/charcoalphp/charcoal/commit/46af62baec68713c13c837a8123ce3294bcf4254))
* **admin:** Change default options of TinyMCE Basic Input ([26018e9](https://github.com/charcoalphp/charcoal/commit/26018e9082681a00be706a04f43da0db0852dcca))
* **admin:** Improve Selectize LoadAction controller ([21057b7](https://github.com/charcoalphp/charcoal/commit/21057b773731b51b0f661d2fe23e7b830430a0d2)), closes [#85](https://github.com/charcoalphp/charcoal/issues/85)
* **admin:** Improve structure property metadata filtering ([8eb96cb](https://github.com/charcoalphp/charcoal/commit/8eb96cb69a4b71ae9ce1b3b98fda54d40e3728d6))
* **admin:** Improve Tabulator Input ([fc33ab0](https://github.com/charcoalphp/charcoal/commit/fc33ab00aaeb8a4a9ed135630d06aff7345d6f8e))
* **admin:** Improve validation/requirements in Tabulator Input ([13b33d4](https://github.com/charcoalphp/charcoal/commit/13b33d4532ab5bd877ef09cea99cbb0c57235b9c))
* **admin:** Sort admin secondary and system menu items ([e6c3493](https://github.com/charcoalphp/charcoal/commit/e6c34935177fe3b4327fadb67112c811b462c6fe))
* **admin:** Use Bootstrap 4 theme for Tabulator Input ([b93cd5d](https://github.com/charcoalphp/charcoal/commit/b93cd5dbb2b5975699eab753a7b5ce0e6b89e0da))
* **input:** add getInputValOptions to allow input classes to define their own set of options. ([8d3ce52](https://github.com/charcoalphp/charcoal/commit/8d3ce52828b9526b88172a1a2ca088d92936b759))
* **model-structure-property:** improve ModelStructureProperty.php ([47c1ae0](https://github.com/charcoalphp/charcoal/commit/47c1ae095dee4ba97e7f851cac260a4d0e8c393e))
* **module:** Improve module app config loader ([8871115](https://github.com/charcoalphp/charcoal/commit/88711151b82ff26ebc73744a25162adb7070da2f))
* **property:** Add support for dynamic type field to Object Property ([d153868](https://github.com/charcoalphp/charcoal/commit/d15386855dab40c56296a756a5c46fb9a5b9c7b9)), closes [#86](https://github.com/charcoalphp/charcoal/issues/86)
* **structure-form-group:** improve StructureFormGroup.php ([c2f385e](https://github.com/charcoalphp/charcoal/commit/c2f385ef70e40d84defd3124bcbe3ce545b4a5a2))
* **structure:** add TranslatableValue.php awareness to AbstractProperty.php ([ca67612](https://github.com/charcoalphp/charcoal/commit/ca6761247cc32fc7dcb5274d818e4b9ec8a8e289))
* **tabulator:** add tabulator front-end lib and templates ([b2cda4a](https://github.com/charcoalphp/charcoal/commit/b2cda4af3c60a34f42e0053e37bc639b94ef40f8))
* **tabulator:** add TabulatorInput.php ([bbccfc6](https://github.com/charcoalphp/charcoal/commit/bbccfc650ce5b2456d6900cf6ffa9bd11723116f))
* **tabulator:** implement tabulator.js as Charcoal js module ([0626c61](https://github.com/charcoalphp/charcoal/commit/0626c6116a42da7bfd96025c6f5320b6252d0279))
* **translator:** rework the translator and translation systems to standardize translatables with TranslatableInterface.php and add a new TranslatableValue.php enabling dissociating translation from the core translator ([a29d0f3](https://github.com/charcoalphp/charcoal/commit/a29d0f3ffbbef5a0703cc76dda12360cdcff4ca3))


### Bug Fixes

* **abstract-selectable:** add check for `is_object` before calling `method_exists` on potential integer value ([42876e3](https://github.com/charcoalphp/charcoal/commit/42876e325ac07dab5a0ca2b0ae50aa1e5de88675))
* **admin:** Add fallback layout for form group properties ([04169c8](https://github.com/charcoalphp/charcoal/commit/04169c861a30d8b2510e06700393df4c11ed4eb8))
* **admin:** Change Object UpdateAction ([d2aba67](https://github.com/charcoalphp/charcoal/commit/d2aba6722fd9eb37b558786be0068be1f18e141d))
* **admin:** Fix admin template menu building ([1221167](https://github.com/charcoalphp/charcoal/commit/1221167df9cc43daf7bb240422f4964c3845c9d6))
* **admin:** Fix appearance of Selectize List ([6ec39df](https://github.com/charcoalphp/charcoal/commit/6ec39dfff1133e8125e3e13aaa0a57edfce61f0f))
* **admin:** Fix block comments of Tabulator input ([d86a2b7](https://github.com/charcoalphp/charcoal/commit/d86a2b79a6a4a2596015a44c255184c8ee833ffe))
* **admin:** Fix handling of property type metadata in CollectionContainerTrait ([aa465e7](https://github.com/charcoalphp/charcoal/commit/aa465e70b737149401828527bbc69969bb067acb))
* **admin:** Fix margins for switch and file inputs ([e582431](https://github.com/charcoalphp/charcoal/commit/e5824318fb695baec5eecbb4b5d24b8bea3ac991))
* **admin:** Fix quick forms, form tabs, and L10N inputs ([cd65f2c](https://github.com/charcoalphp/charcoal/commit/cd65f2cc2ecd3a3c846f63c4ce1c0b2c084bbffe))
* **admin:** Fix referenced version of Tabulator in NPM manifest ([cf506d4](https://github.com/charcoalphp/charcoal/commit/cf506d4deb980e7ee630b737ba41c9c245da3632))
* **admin:** Improve error handling in ObjectContainerTrait ([5e00185](https://github.com/charcoalphp/charcoal/commit/5e0018509d704f7b17cd614177c6e261c468d3e1))
* **admin:** Improve structure card header ([2e0c6a7](https://github.com/charcoalphp/charcoal/commit/2e0c6a752a9f619b679db646bdc42673c4ac9878))
* **admin:** Improve styles and logic of Tabulator Input ([b3971c0](https://github.com/charcoalphp/charcoal/commit/b3971c0cf1b3e59497b0b5616e23a518b9d69bee))
* **admin:** Localize Tabulator Input ([f7e1666](https://github.com/charcoalphp/charcoal/commit/f7e16666cba01201df0d4617cb910606090630f6))
* **bin:** Search working directory for Composer autoloader ([4226199](https://github.com/charcoalphp/charcoal/commit/4226199cf63f947522468692b0663ed42b662d49))
* **cache:** Ignore broken cache drivers in tests ([1316be1](https://github.com/charcoalphp/charcoal/commit/1316be1e9d329f69e1a6d26602ab3003c9471704))
* **json-editor:** use inputVal instead of jsonVal for json-editor.mustache since it's no longer needed following changes to TranslatableValue and AbstractProperty/Input ([013573b](https://github.com/charcoalphp/charcoal/commit/013573b4b1789cd3c78f901694463e8439897eee))
* **property:** Fix handling of `l10nVal()` on Structure Property ([d3c71b9](https://github.com/charcoalphp/charcoal/commit/d3c71b9d0e56dece57bd5823f5f0db6367b97448))
* **translatable:** make TranslatableValue.php ArrayAccess compliant ([4c40ea0](https://github.com/charcoalphp/charcoal/commit/4c40ea092a21d68b4d5acc7b5cb6f87e700d72df))
* **translatable:** update the `TranslatableInterface` to change the `trans` method signature and update `TranslatableValue` with said method and deprecate `each` and `sanitize` for future version of charcoal ([a728e07](https://github.com/charcoalphp/charcoal/commit/a728e0788e8b83d3e5a54ae8e2320e294fafa76e))
* **translation:** revert construct to use manager and trans method no longer relay to `translator->translate()` ([6723433](https://github.com/charcoalphp/charcoal/commit/672343302f6511bbe8687832e14409a48da6dd87))
* **translator:** Revert `trans()` method of `TranslatableInterface` ([81393f6](https://github.com/charcoalphp/charcoal/commit/81393f60ca603cc4a68296e46a90996fd0c562f6))

## [4.1.0](https://github.com/charcoalphp/charcoal/compare/v4.0.8...v4.1.0) (2024-03-05)


### Features

* **admin:** Fix and improve base path in AssetsBuilder ([116a9ce](https://github.com/charcoalphp/charcoal/commit/116a9cea6473c93d34bf36a205a3a05f9f0d6c66))
* **object:** Fix missing orphaned descendants in `HierarchicalCollection` ([798901f](https://github.com/charcoalphp/charcoal/commit/798901f5c14cea9738556211d781f40e020d786f))
* **object:** Improve Hierarchical mixin ([71a96e4](https://github.com/charcoalphp/charcoal/commit/71a96e460d146b7290f75b65f066a5de8763e3f2))


### Bug Fixes

* **admin:** Ensure master object exists in HierarchicalOjectProperty ([f153dba](https://github.com/charcoalphp/charcoal/commit/f153dba5b0152a3653010ef3e9095109ca7ab5a5))
* **cms:** Clean-up SectionInterface admin metadata ([86cfb74](https://github.com/charcoalphp/charcoal/commit/86cfb74fa36ec403ca8ed3b38b6c05b97e27856e))
* **cms:** Ensure object is hierarchical in HierarchicalSectionTableWidget ([f4d1ebe](https://github.com/charcoalphp/charcoal/commit/f4d1ebebd602fca74f3d6468c0ea1f2f0551cfcc))
* **cms:** Fix missing filter for menu items in SectionInterface admin metadata ([d4a523d](https://github.com/charcoalphp/charcoal/commit/d4a523d93fdd18a4a2d445fe8a8d994daefb5b30))
* **image:** Fix Imagemagick resize effect ([afbbff5](https://github.com/charcoalphp/charcoal/commit/afbbff51cb3a7d927086c0508a2c7c78daac1ce8))
* **object:** Filter argument of `HierarchicalTrait::setMaster()` ([fa7a7b3](https://github.com/charcoalphp/charcoal/commit/fa7a7b37899465c0a976c32ae793361a052e9381))
* **object:** Fix bad hierarchy repair in `HierarchicalCollection` ([7d13a08](https://github.com/charcoalphp/charcoal/commit/7d13a08cc9e9255fb3086302aa09a50ec2cb7858))

## [4.0.8](https://github.com/charcoalphp/charcoal/compare/v4.0.7...v4.0.8) (2024-01-13)


### Bug Fixes

* **admin:** Fix Clear Cache action when view engine unavailable ([1f74083](https://github.com/charcoalphp/charcoal/commit/1f7408393f9f13293a5a6cbb92108f1852ce8590))

## [4.0.7](https://github.com/charcoalphp/charcoal/compare/v4.0.6...v4.0.7) (2024-01-13)


### Bug Fixes

* **admin:** Fix Clear Cache template when view engine unavailable ([8f794e2](https://github.com/charcoalphp/charcoal/commit/8f794e2f3b994647f068b1a33708bd2306524ed0))

## [4.0.6](https://github.com/charcoalphp/charcoal/compare/v4.0.5...v4.0.6) (2024-01-12)


### Performance Improvements

* **admin:** Update elFinder v2.1.62 → v2.1.64 ([b55a916](https://github.com/charcoalphp/charcoal/commit/b55a91661aa329ebff26755cc2ccb0d071ee7efe))
* **admin:** Upgrade jQuery + jQuery UI ([5baa78d](https://github.com/charcoalphp/charcoal/commit/5baa78dc9b805af93ef79179d3bcfc075f5cdbe0))

## [4.0.5](https://github.com/charcoalphp/charcoal/compare/v4.0.4...v4.0.5) (2023-12-18)


### Bug Fixes

* **composer:** Update elFinder in root composer.json ([cdc6a13](https://github.com/charcoalphp/charcoal/commit/cdc6a134d4e5aba87be9ec4741f9873652385748))
* **composer:** Update PHPMailer ([43049e4](https://github.com/charcoalphp/charcoal/commit/43049e4c60fcd994a817b902c2ed0a1c54dbe370))

## [4.0.4](https://github.com/charcoalphp/charcoal/compare/v4.0.3...v4.0.4) (2023-12-18)


### Bug Fixes

* **admin:** Update elFinder ([e64cc47](https://github.com/charcoalphp/charcoal/commit/a941f159bd8fd8216ab42b68f286e47bf957c706))
* **admin:** Update TinyMCE ([e64cc47](https://github.com/charcoalphp/charcoal/commit/510fc062038ccbfd3f556873e4b9304f85765a88))

## [4.0.3](https://github.com/charcoalphp/charcoal/compare/v4.0.2...v4.0.3) (2023-11-23)


### Bug Fixes

* **composer:** Update composer.lock file ([9142dda](https://github.com/charcoalphp/charcoal/commit/9142dda7861acc73435074ed197718b9ab990016))
* **composer:** Update composer.lock file ([3e69ce8](https://github.com/charcoalphp/charcoal/commit/3e69ce872178d242e5a8a0656a5df08d17a0357a))

## [4.0.2](https://github.com/charcoalphp/charcoal/compare/v4.0.1...v4.0.2) (2023-01-19)


### Bug Fixes

* **admin:** Fix admin URL by replacing directory separator with forward slash ([9aa3a8f](https://github.com/charcoalphp/charcoal/commit/9aa3a8f5c089604951a74e48030dc815b53baad9))

## [4.0.1](https://github.com/charcoalphp/charcoal/compare/v4.0.0...v4.0.1) (2022-11-10)


### Bug Fixes

* **release:** Fix semantic-release and monorepo-builder ([240492a](https://github.com/charcoalphp/charcoal/commit/240492aeeddf3e80192c0c3cb7e0419139da3551))

## [v4.0.0](https://github.com/charcoalphp/charcoal/compare/v3.1.8...v4.0.0) - 2022-09-21

#### ⚠ BREAKING CHANGES

- **elfinder:** removed default base_path and public_path. These config keys should be defined in the AppConfig initialization. `new AppConfig(['base_path' => '...']);`

#### Bug Fixes

- **base-path:** fix base_path concatenation issues since changes made to AppConfig see https://github.com/charcoalphp/charcoal/pull/28#issue-1267893850 ([84441a3](https://github.com/charcoalphp/charcoal/commit/84441a3832fff47ee780d15457778e9e3423bb3f)), closes [/github.com/charcoalphp/charcoal/pull/28#issue-1267893850](https://github.com/charcoalphp//github.com/charcoalphp/charcoal/pull/28/issues/issue-1267893850)
- **bin:** fix charcoal binary appConfig's basePath ([95bd3a5](https://github.com/charcoalphp/charcoal/commit/95bd3a53fc1712762392376e3b744e3ac0a2b925))
- **elfinder:** fix issues with elfinder paths ([a476327](https://github.com/charcoalphp/charcoal/commit/a476327234b563ba70b4b6fa92e2a10c6df2989e))
- **releaserc:** add parserOpts for commit analyser ([b004e04](https://github.com/charcoalphp/charcoal/commit/b004e04b7143445e4167196be6eed92b12ed9687))

## [v3.1.8](https://github.com/charcoalphp/charcoal/compare/v3.1.7...v3.1.8) - 2022-09-15

#### ⚠ BREAKING CHANGES

- **elfinder:** removed default base_path and public_path. These config keys should be defined in the AppConfig initialization. `new AppConfig(['base_path' => '...']);`

#### Bug Fixes

- **base-path:** fix base_path concatenation issues since changes made to AppConfig see https://github.com/charcoalphp/charcoal/pull/28#issue-1267893850 ([84441a3](https://github.com/charcoalphp/charcoal/commit/84441a3832fff47ee780d15457778e9e3423bb3f)), closes [/github.com/charcoalphp/charcoal/pull/28#issue-1267893850](https://github.com/charcoalphp//github.com/charcoalphp/charcoal/pull/28/issues/issue-1267893850)
- **bin:** fix charcoal binary appConfig's basePath ([95bd3a5](https://github.com/charcoalphp/charcoal/commit/95bd3a53fc1712762392376e3b744e3ac0a2b925))
- **elfinder:** fix issues with elfinder paths ([a476327](https://github.com/charcoalphp/charcoal/commit/a476327234b563ba70b4b6fa92e2a10c6df2989e))

## [v3.1.7](https://github.com/charcoalphp/charcoal/compare/v3.1.6...v3.1.7) - 2022-09-15

#### Bug Fixes

- **view twig helper:** fix the debug and isDebug Twig functions helpers using the debug container ([8ccf37b](https://github.com/charcoalphp/charcoal/commit/8ccf37bd15c328a513ba26af9717e00e792a5fe2))

## [v3.1.6](https://github.com/charcoalphp/charcoal/compare/v3.1.5...v3.1.6) - 2022-09-13

#### Bug Fixes

- **bin:** Improve logic, comments, fix coding style ([894261a](https://github.com/charcoalphp/charcoal/commit/894261a3bc1119fd598f31415d389f1c1c9e8934))

## [v3.1.5](https://github.com/charcoalphp/charcoal/compare/v3.1.4...v3.1.5) - 2022-09-09

### Bug Fixes

- **monorepo:** implement a custom UpdateReplace release worker to prevent overwriting the whole composer.json 'replace' section ([1c8c66b](https://github.com/charcoalphp/charcoal/commit/1c8c66bd3c79dc388b6fa5f3751d6cbdfa03da17))

## [v3.1.4](https://github.com/charcoalphp/charcoal/compare/v3.1.3...v3.1.4) - 2022-09-08

### Bug Fixes

- **app:** fix deleted method visibility ([7383b5f](https://github.com/charcoalphp/charcoal/commit/7383b5fb2f97886e887a2351faeec9b6db8afc15))
- **cache:** fix composer dev requirement for slim ([a7a44fb](https://github.com/charcoalphp/charcoal/commit/a7a44fb3cd05196352c1481bdd3a7e6d1d882f3a))
- **charcoal:** fix bad find and replace ([577e414](https://github.com/charcoalphp/charcoal/commit/577e414fc9a9c5f64ac06395a4cfc86b7b588c0a))
- **charcoal:** fix bad find and replace ([2db7a17](https://github.com/charcoalphp/charcoal/commit/2db7a17de36fd51aa22279ea6efd8157c0accfbb))
- **cms:** fix missing property type ([fc8d6bf](https://github.com/charcoalphp/charcoal/commit/fc8d6bfb99a5f20c15ee0288554daab4445b7086))
- **cms:** fix TemplateableTrait.php templateIdent property to be defaulted to an empty string like in ViewableTrait.php ([01b8e5a](https://github.com/charcoalphp/charcoal/commit/01b8e5a2c3fc2f27436e42224d384c981c51d265))
- **composer:** fix some composer conflicting packages ([3f94314](https://github.com/charcoalphp/charcoal/commit/3f9431459afc9378e30adee34e0f02ed119cdeac))
- **composer:** move composer.json replace section for locomotivemtl packages to monorepo-builder.php to prevent overwriting ([0d48576](https://github.com/charcoalphp/charcoal/commit/0d485764b939daa9b1890527db77363c43558463))
- **core:** add exception thrown when metadata file cannot be loaded ([6fadceb](https://github.com/charcoalphp/charcoal/commit/6fadceb30f704091452e15847543e60d4d8a8158))
- **core:** add exception thrown when source ident is not defined ([5f76cec](https://github.com/charcoalphp/charcoal/commit/5f76cec96f2db0265ab0ab9734954753b34534e5))
- **core:** revert loadfile() changes because it caused unexpected issues ([04a0391](https://github.com/charcoalphp/charcoal/commit/04a039175db89990d26dce3bed908cb1d6becbd5))
- **image:** fix radial blur effect renamed to rotational blur ([8293e99](https://github.com/charcoalphp/charcoal/commit/8293e994afc3dbd06bf3789746a6867d5f53379b))
- **php:** add redis extension to php github action ([49e755f](https://github.com/charcoalphp/charcoal/commit/49e755fd994b84e06bbb8e648625903f2b0ef223))
- **phpcs:** exit execution after -l option ([e6ad662](https://github.com/charcoalphp/charcoal/commit/e6ad662d9f8a96067d7517af70d41d19ab03d493))
- **phpunit.xml.dist:** add xsi:noNamespaceSchemaLocation ([e13e43e](https://github.com/charcoalphp/charcoal/commit/e13e43e94759095960a242464f50f414e28af722))
- **template-ident:** uniformize template-ident return types ([819f5a0](https://github.com/charcoalphp/charcoal/commit/819f5a0835dbddd8dd2f58fba3f5cb0a018057ca))
- **view:** the view prop and method should be allowed to be null ([c97c2e3](https://github.com/charcoalphp/charcoal/commit/c97c2e3222dccdbe3eaec71eeb236e6a40d296e7))

### Reverts

- Revert "refactor(view): fix templateIdent return type" ([02ef2be](https://github.com/charcoalphp/charcoal/commit/02ef2be4400ec65125ca794fb91ccaea338370f9))
- Revert "refactor(templateable-trait): remove default value for templateIdent" ([314e1c9](https://github.com/charcoalphp/charcoal/commit/314e1c954347fac3f6adc7d8edc6d47ffea5cc16))

## [v3.1.3](https://github.com/charcoalphp/charcoal/compare/v3.1.2...v3.1.3) - 2022-09-08

### Bug Fixes

- fix use of renderTemplate and render view functions ([3f06541](https://github.com/charcoalphp/charcoal/commit/3f06541faa782dd805068020bba127a205d6df06))

## [v3.1.2](https://github.com/charcoalphp/charcoal/compare/v3.1.1...v3.1.2) - 2022-08-11

### Bug Fixes

- **migrate-project:** fix the charcoal project migration script ([2770ef7](https://github.com/charcoalphp/charcoal/commit/2770ef7366dcd0cbf6030287f15b94387c481d61))

## [v3.1.1](https://github.com/charcoalphp/charcoal/compare/v3.1.0...v3.1.1) - 2022-08-10

### Bug Fixes

- **app:** fix an issue with the filesystem path tokens parsing ([5b7a2a3](https://github.com/charcoalphp/charcoal/commit/5b7a2a31039671406318b157e30d39d1832b9733))

## [v3.1.0](https://github.com/charcoalphp/charcoal/compare/v3.0.16...v3.1.0) - 2022-08-08

### Features

- **script:** add a pull request script using the github api ([5ad068d](https://github.com/charcoalphp/charcoal/commit/5ad068dd15379ce48c7cd4a2cf99e33b275d73af))
- **script:** rename and move create-pull-request script and add a success message ([d77cb1b](https://github.com/charcoalphp/charcoal/commit/d77cb1babd4144f40656814a8c905823d0fa92f2))

### Bug Fixes

- **create-pr:** fix success message url ([2125186](https://github.com/charcoalphp/charcoal/commit/21251861510509067ccaffe36733780f303fcabd))
- **create-pr:** fix success message url was pointing to the api response ([1bd59a4](https://github.com/charcoalphp/charcoal/commit/1bd59a44f10ece74569c25a1afba3ca83ac6cc2a))
- **create-pr:** update relative path to create-release-notes script ([11c54f5](https://github.com/charcoalphp/charcoal/commit/11c54f50d7a385cf2e7f5b1bf941588cd96e337a))

## [v3.0.16](https://github.com/charcoalphp/charcoal/compare/v3.0.15...v3.0.16) - 2022-08-02

### Bug Fixes

- **action:** try to fix split-monorepo.yaml ([1e976b5](https://github.com/charcoalphp/charcoal/commit/1e976b5805a8462cef802c2ff2ffc76d24fcaa6e))

## [v3.0.15](https://github.com/charcoalphp/charcoal/compare/v3.0.14...v3.0.15) - 2022-08-01

### Bug Fixes

- **action:** try to fix split-monorepo.yaml ([16d300a](https://github.com/charcoalphp/charcoal/commit/16d300a3ec79387dfcaf30072648b37304fd53a4))

## [v3.0.14](https://github.com/charcoalphp/charcoal/compare/v3.0.13...v3.0.14) - 2022-08-01

### Bug Fixes

- **action:** try to fix split-monorepo.yaml ([721e86b](https://github.com/charcoalphp/charcoal/commit/721e86b8ef552d3c92ee70d769d6ac206f1fd305))

## [v3.0.13](https://github.com/charcoalphp/charcoal/compare/v3.0.12...v3.0.13) - 2022-08-01

### Bug Fixes

- **action:** try to fix split-monorepo.yaml ([c0ec343](https://github.com/charcoalphp/charcoal/commit/c0ec343e7f81854cbbad7f74e09a3255a3d9c7e9))

## [v3.0.12](https://github.com/charcoalphp/charcoal/compare/v3.0.11...v3.0.12) - 2022-08-01

### Bug Fixes

- **action:** try to fix split-monorepo.yaml ([db5b97f](https://github.com/charcoalphp/charcoal/commit/db5b97f39fdeec7c7b096601433f06e52280d1dc))

## [v3.0.11](https://github.com/charcoalphp/charcoal/compare/v3.0.10...v3.0.11) - 2022-08-01

### Bug Fixes

- **action:** try to fix split-monorepo.yaml ([e77ac76](https://github.com/charcoalphp/charcoal/commit/e77ac766cda4ff8c7624d6e4e6653cf3197459aa))

## [v3.0.10](https://github.com/charcoalphp/charcoal/compare/v3.0.9...v3.0.10) - 2022-08-01

### Bug Fixes

- **action:** try to fix split-monorepo.yaml ([57d3cea](https://github.com/charcoalphp/charcoal/commit/57d3cea47d8bc8139f64bdf9f09dfedf06324a54))

## [v3.0.9](https://github.com/charcoalphp/charcoal/compare/v3.0.8...v3.0.9) - 2022-08-01

### Bug Fixes

- **action:** try to fix split-monorepo.yaml ([e08454d](https://github.com/charcoalphp/charcoal/commit/e08454dd0874dc46074dd32bc3d1d0233a05a839))

## [v3.0.8](https://github.com/charcoalphp/charcoal/compare/v3.0.7...v3.0.8) - 2022-08-01

### Bug Fixes

- **action:** try to fix split-monorepo.yaml ([8b4d735](https://github.com/charcoalphp/charcoal/commit/8b4d735374ec874e196e9706278ecb919bd25045))

## [v3.0.7](https://github.com/charcoalphp/charcoal/compare/v3.0.6...v3.0.7) - 2022-08-01

### Bug Fixes

- **action:** try to fix split-monorepo.yaml ([5bde1e2](https://github.com/charcoalphp/charcoal/commit/5bde1e2ced79c9b92b5aa009a786c0c1fd636c8b))

## [v3.0.6](https://github.com/charcoalphp/charcoal/compare/v3.0.5...v3.0.6) - 2022-08-01

### Bug Fixes

- **action:** try to fix split-monorepo.yaml ([12bc8a5](https://github.com/charcoalphp/charcoal/commit/12bc8a5e6bdaf8bbb94cc1800d39d2b750dea2f9))

## [v3.0.5](https://github.com/charcoalphp/charcoal/compare/v3.0.4...v3.0.5) - 2022-08-01

### Bug Fixes

- **action:** try to fix split-monorepo.yaml ([31c12ff](https://github.com/charcoalphp/charcoal/commit/31c12ff56710c2b74c6a377c47b7d9147541f825))

## [v3.0.4](https://github.com/charcoalphp/charcoal/compare/v3.0.3...v3.0.4) - 2022-08-01

### Bug Fixes

- **action:** try to fix split-monorepo.yaml ([6ab2aee](https://github.com/charcoalphp/charcoal/commit/6ab2aeea1f20fe617af54b484890fb3967a3abc3))

## [v3.0.3](https://github.com/charcoalphp/charcoal/compare/v3.0.2...v3.0.3) - 2022-08-01

### Bug Fixes

- **trigger:** trigger release ([b0fc93f](https://github.com/charcoalphp/charcoal/commit/b0fc93f666ea307a547af5eacde062b68ac47860))

## [v3.0.2](https://github.com/charcoalphp/charcoal/compare/v3.0.1...v3.0.2) - 2022-07-27

### Bug Fixes

- **user:** fix __toString compatibility with AbstractModel on Acl Permission and Acl Role ([7ade0c0](https://github.com/charcoalphp/charcoal/commit/7ade0c05f6d6dd047d3320ff157e6b47b829bb58))

## [v3.0.1](https://github.com/charcoalphp/charcoal/compare/v3.0.0...v3.0.1) - 2022-07-20

### Bug Fixes

- **admin:** Typo with property inputPrefix ([28b5554](https://github.com/charcoalphp/charcoal/commit/28b55545ca8ab1689cdb0de898375b9dd99329de))

## [v3.0.0](https://github.com/charcoalphp/charcoal/compare/v2.3.0...v3.0.0) - 2022-07-20

### ⚠ BREAKING CHANGES

- (BC):
- 
- Method `widgetOptions()` now returns an array instead of a JSON serialized string to allow for easier overriding in subclasses.
- 

Added:

- Methods `widgetOptionsAsJson()` and `escapedWidgetOptionsAsJson()` for rendering widget options as JSON.
- Method `widgetDataForJs()` to replace hardcoded widget data options in view file.

Changed:

- Stringify the widget's title.

### Features

- **admin:** Add new group display mode for quick form widget ([524ec25](https://github.com/charcoalphp/charcoal/commit/524ec25d7e65b93b2bd3625f9dd0cb0d41561e36)), closes [locomotivemtl/charcoal-admin#63](https://github.com/locomotivemtl/charcoal-admin/issues/63) [locomotivemtl/charcoal-admin#d27a30e44b0e6e06899a481322015b09e743dc82](https://github.com/locomotivemtl/charcoal-admin/issues/d27a30e44b0e6e06899a481322015b09e743dc82)
- **admin:** Improve dialog in widget.js ([1855d3b](https://github.com/charcoalphp/charcoal/commit/1855d3bb71e417a0016844e65bbabfda198defbd)), closes [locomotivemtl/charcoal-admin#8eb005a375875a7158537898c357b050ab066050](https://github.com/locomotivemtl/charcoal-admin/issues/8eb005a375875a7158537898c357b050ab066050)
- **admin:** Improve error handling in form.js ([8340eb6](https://github.com/charcoalphp/charcoal/commit/8340eb6378dc38ef54094a2d0acd18148e4cc9b0)), closes [locomotivemtl/charcoal-admin#6659001b72230e62f353ac533e449ffec40221d4](https://github.com/locomotivemtl/charcoal-admin/issues/6659001b72230e62f353ac533e449ffec40221d4)
- **admin:** Improve generic graph widgets ([b8a03f6](https://github.com/charcoalphp/charcoal/commit/b8a03f6993f2f40468cc0b15181a67afe6c57530)), closes [locomotivemtl/charcoal-admin#8e482bd13688c8b3d2770078424b6117eba3d0](https://github.com/locomotivemtl/charcoal-admin/issues/8e482bd13688c8b3d2770078424b6117eba3d0)
- **admin:** Improve reloading in widget.js ([f409aac](https://github.com/charcoalphp/charcoal/commit/f409aac22b752fcb762c0e76a829cf1c6e0f8922)), closes [locomotivemtl/charcoal-admin#d8d977c1609515b144837d1d1ca3f11677972](https://github.com/locomotivemtl/charcoal-admin/issues/d8d977c1609515b144837d1d1ca3f11677972)
- **admin:** Improve showLanguageSwitch integration ([41f341f](https://github.com/charcoalphp/charcoal/commit/41f341f51ef6351ba269cb8cc3a2e5fe01074c89)), closes [locomotivemtl/charcoal-admin#9c436ea57c5a37e90f97f90cf78994ab66cd0083](https://github.com/locomotivemtl/charcoal-admin/issues/9c436ea57c5a37e90f97f90cf78994ab66cd0083)
- **admin:** Improve switch language in form.js ([22c30ec](https://github.com/charcoalphp/charcoal/commit/22c30ec406cfac51983cbbf144bf7af14cb66aac)), closes [locomotivemtl/charcoal-admin#e9d29556820f0b2f4e6a6adc127c31dad86fae04](https://github.com/locomotivemtl/charcoal-admin/issues/e9d29556820f0b2f4e6a6adc127c31dad86fae04)
- **admin:** Improve validate/save in component_manager.js ([423c84b](https://github.com/charcoalphp/charcoal/commit/423c84ba4e2a995c09b9c81131bbf02b21ff94bf)), closes [locomotivemtl/charcoal-admin#1759d2bb90297ff7f03c5c94a0211b22dd2796](https://github.com/locomotivemtl/charcoal-admin/issues/1759d2bb90297ff7f03c5c94a0211b22dd2796) [locomotivemtl/charcoal-admin#e5e0741666cb52ca84a09cabce6619a8ec61c7c2](https://github.com/locomotivemtl/charcoal-admin/issues/e5e0741666cb52ca84a09cabce6619a8ec61c7c2)

### Bug Fixes

- **admin:** Add missing localizations ([9496922](https://github.com/charcoalphp/charcoal/commit/9496922339b674da2baef6a619d0cee6f597d7be)), closes [locomotivemtl/charcoal-admin#47012a22e696bd090adb08ded9954ac1a50e84](https://github.com/locomotivemtl/charcoal-admin/issues/47012a22e696bd090adb08ded9954ac1a50e84)
- **admin:** Ensure widgetL10n is available with attachment.js ([8cea251](https://github.com/charcoalphp/charcoal/commit/8cea2510f62a19bacd19375e83ca6a639e3c8aec)), closes [locomotivemtl/charcoal-attachment#496b9bec978bb01065e59c36e4291fe8e3384](https://github.com/locomotivemtl/charcoal-attachment/issues/496b9bec978bb01065e59c36e4291fe8e3384)
- **admin:** Improve hasL10nFormProperties resolution ([3be40b0](https://github.com/charcoalphp/charcoal/commit/3be40b0acfd6b9a95ac9c552e7071bedeb286d68)), closes [locomotivemtl/charcoal-admin#2c4c3797bb8463241e723e6d157589291c832a0](https://github.com/locomotivemtl/charcoal-admin/issues/2c4c3797bb8463241e723e6d157589291c832a0)
- **admin:** Improve internals of attachment.js ([a387db3](https://github.com/charcoalphp/charcoal/commit/a387db36ff0b3cbc20e1c2e0efa6b2bafaaa736d)), closes [locomotivemtl/charcoal-admin#45135ef015723f319ed246bf28eac4d4d2dccbe4](https://github.com/locomotivemtl/charcoal-admin/issues/45135ef015723f319ed246bf28eac4d4d2dccbe4)
- **admin:** Improve resolveSimpleJsonXhr in charcoal.js ([1eee836](https://github.com/charcoalphp/charcoal/commit/1eee836cf5eecd1035f05776945a2f08a83c0746)), closes [locomotivemtl/charcoal-admin#bc8327e7aadeb57ea6e1ec931051aba4d630d409](https://github.com/locomotivemtl/charcoal-admin/issues/bc8327e7aadeb57ea6e1ec931051aba4d630d409)
- **admin:** Update Bootstrap to v4.6.2 ([1fb8b4a](https://github.com/charcoalphp/charcoal/commit/1fb8b4afa418b6dcbeb30eac8e4e8505efa0222e)), closes [locomotivemtl/charcoal-admin#e6ac7b6f3b447cf08b7d9578b3d888d178a3639](https://github.com/locomotivemtl/charcoal-admin/issues/e6ac7b6f3b447cf08b7d9578b3d888d178a3639)
- **property:** Fix parsing + error handling in Sprite Property ([831f16d](https://github.com/charcoalphp/charcoal/commit/831f16d9fa84392278a9143ecde767785a7bde14)), closes [locomotivemtl/charcoal-property#d1ef3c66122781581a856721fe233a816fc5b0b6](https://github.com/locomotivemtl/charcoal-property/issues/d1ef3c66122781581a856721fe233a816fc5b0b6) [locomotivemtl/charcoal-property#8f3403145b55c43190e447dae1c8f4dea249cc33](https://github.com/locomotivemtl/charcoal-property/issues/8f3403145b55c43190e447dae1c8f4dea249cc33)

### Performance Improvements

- **admin:** Fix and improve event namespacing in JS components ([db46534](https://github.com/charcoalphp/charcoal/commit/db46534d23e3e281d0ca74a8103fe229d7f5a57d)), closes [locomotivemtl/charcoal-admin#738ae375ed4fa92cdd2ddbdadb2dca3cab8bbe96](https://github.com/locomotivemtl/charcoal-admin/issues/738ae375ed4fa92cdd2ddbdadb2dca3cab8bbe96)
- **admin:** Improve elements in form.js and quickform.js ([81b9cad](https://github.com/charcoalphp/charcoal/commit/81b9cad39e241c4483e8400ccb9a5121efc4159f)), closes [locomotivemtl/charcoal-admin#bee1d393d313fdfc0a8b9a5918a7678a46b33d21](https://github.com/locomotivemtl/charcoal-admin/issues/bee1d393d313fdfc0a8b9a5918a7678a46b33d21)
- **admin:** Improve internals of feedback.js ([cb6a4f6](https://github.com/charcoalphp/charcoal/commit/cb6a4f6484aa1f10b76d3f77f02ed913a2bb617f)), closes [locomotivemtl/charcoal-admin#45b76606fc945ab2526ccdd9db8219b11fb8ca10](https://github.com/locomotivemtl/charcoal-admin/issues/45b76606fc945ab2526ccdd9db8219b11fb8ca10)
- **export:** improve performance of exporter ([9a1de4c](https://github.com/charcoalphp/charcoal/commit/9a1de4ccf2238530c7ae7d18719d228d61ad69aa)), closes [locomotivemtl/charcoal-admin#19192817861a2faed50ad7aeb8b10dfbcc63ce25](https://github.com/locomotivemtl/charcoal-admin/issues/19192817861a2faed50ad7aeb8b10dfbcc63ce25)

### Reverts

- **admin:** Disable `will_save` constraint in attachment.js ([3e78bcd](https://github.com/charcoalphp/charcoal/commit/3e78bcdd9f6a8efc07778f0db9db5148bf07896f)), closes [locomotivemtl/charcoal-admin#1759d2bb90297ff7f03c5c94a0211b22dd2796](https://github.com/locomotivemtl/charcoal-admin/issues/1759d2bb90297ff7f03c5c94a0211b22dd2796) [locomotivemtl/charcoal-admin#6fb62c401db2192693d09cd42b8b2250d7af57b6](https://github.com/locomotivemtl/charcoal-admin/issues/6fb62c401db2192693d09cd42b8b2250d7af57b6)

### Code Refactoring

- Integration of Attachment widget data and options ([c24ebc7](https://github.com/charcoalphp/charcoal/commit/c24ebc7f54926d68874a862953812e539330220f)), closes [locomotivemtl/charcoal-attachment#994dcc357626c0fd716b864812dffdc1ca742d93](https://github.com/locomotivemtl/charcoal-attachment/issues/994dcc357626c0fd716b864812dffdc1ca742d93)

## [v2.3.0](https://github.com/charcoalphp/charcoal/compare/v2.2.5...v2.3.0) - 2022-07-14

### Features

- **twig:** first version of Twig implementation in Charcoal using Twig 3.4 ([c1ffed3](https://github.com/charcoalphp/charcoal/commit/c1ffed3c725260364a7d78c70c27ccaf9b88e625))

### Bug Fixes

- **twig:** fix condition getCacheKey ([01d78c3](https://github.com/charcoalphp/charcoal/commit/01d78c3e17b1e33cade413f023cc9d2a3386e909))
- **twig:** fix Twig UrlHelpers and split functionnality with new DebugHelpers ([bf4056f](https://github.com/charcoalphp/charcoal/commit/bf4056f0f886fe1ccb2e97fdb43ce240f48eef63))
- **twig:** update twig version for dev dependencies ([563f59f](https://github.com/charcoalphp/charcoal/commit/563f59f83dfc62944ac6674702ae8eb40fd4a847))

### Performance Improvements

- **twig:** apply suggestions from code review ([63a9c4f](https://github.com/charcoalphp/charcoal/commit/63a9c4f46917af30ea2ea015994e8f3078a727cc))

## [v2.2.5](https://github.com/charcoalphp/charcoal/compare/v2.2.4...v2.2.5) - 2022-07-07

### Bug Fixes

- **php-coveralls:** remove old satooshi/php-coveralls dependency ([e890acf](https://github.com/charcoalphp/charcoal/commit/e890acf97c47dced5febd492d4915501a7530962))

## [v2.2.4](https://github.com/charcoalphp/charcoal/compare/v2.2.3...v2.2.4) - 2022-06-25

### Bug Fixes

- **packages-path:** change env var for packages path ([ae369ec](https://github.com/charcoalphp/charcoal/commit/ae369ec29a39fe7fa5b632c079d8e35698b1320d))
- **workflow:** fix a typo in workflow file ([d5d95b5](https://github.com/charcoalphp/charcoal/commit/d5d95b5443f76942b3962a1f7f878de7b2b38864))
- **workflow:** fix workflow token ([0b5db3b](https://github.com/charcoalphp/charcoal/commit/0b5db3b546ec66b14d649b04d32e2fba1949ee5c))

## [v2.2.3](https://github.com/charcoalphp/charcoal/compare/v2.2.2...v2.2.3) - 2022-06-23

### Bug Fixes

- **packages:** :building_construction: add replace options in all packages composer files ([9f1777a](https://github.com/charcoalphp/charcoal/commit/9f1777a74a5ca84755fa38eb671cc42451137798))

## [v2.2.2](https://github.com/charcoalphp/charcoal/compare/v2.2.1...v2.2.2) - 2022-06-23

### Bug Fixes

- **charcoal:** add a TODO for a feature request ([45f59b8](https://github.com/charcoalphp/charcoal/commit/45f59b887c5a36fd76a5350592d781a3b2c969c7))

## [v2.2.1](https://github.com/charcoalphp/charcoal/compare/v2.2.0...v2.2.1) - 2022-06-22

### Bug Fixes

- **composer:** add replace packages for all old charcoal packages ([8bec034](https://github.com/charcoalphp/charcoal/commit/8bec034c5aa379559ad5ea98ddc5d929a09222fd))

## [v2.2.0](https://github.com/charcoalphp/charcoal/compare/v2.1.2...v2.2.0) - 2022-06-21

### Features

- **script:** add .env file creation in migrate-project script ([b302511](https://github.com/charcoalphp/charcoal/commit/b302511559c0ab35dfc7d4c67662aa644352b944))
- **script:** change repo owner and add token validation to create-pr script ([536fabd](https://github.com/charcoalphp/charcoal/commit/536fabdce7f9ef12dabc93922f730d2b0c8dda67))

## [v2.1.2](https://github.com/charcoalphp/charcoal/compare/v2.1.1...v2.1.2) - 2022-06-21

### Bug Fixes

- **action:** fix repository references ([3f1fba2](https://github.com/charcoalphp/charcoal/commit/3f1fba21a5071af651a7525e57ff0edd6f00c580))

## [v2.1.1](https://github.com/charcoalphp/charcoal/compare/v2.1.0...v2.1.1) - 2022-06-21

### Bug Fixes

- **action:** add PAT to split-monorepo.yaml ([171b9bf](https://github.com/charcoalphp/charcoal/commit/171b9bfbd30f4b36a85d521f6393db9e53cc3ae5))

## [v2.1.0](https://github.com/charcoalphp/charcoal/compare/v2.0.1...v2.1.0) - 2022-06-21

### Features

- add template tags supports for configurable paths where needed ([f3afb94](https://github.com/charcoalphp/charcoal/commit/f3afb94bfd0e563b043e315bb9c34a50d2c4c40d))
- **app:** add %packages.path% string template ([3efadb9](https://github.com/charcoalphp/charcoal/commit/3efadb91c99dc63a5e1fc1c37ae33fc0c3e98fc5))
- **dotenv:** add dotenv support to App.php ([70b6bd7](https://github.com/charcoalphp/charcoal/commit/70b6bd73a6cf946629fd2d1c721da4469188d622))
- **migration:** add a migration script for port charcoal projects to new framework ([c9cc66a](https://github.com/charcoalphp/charcoal/commit/c9cc66ad72ecc1adc44669edc5be2f69edf9a825))

### Bug Fixes

- fix missing return type ([24c7f57](https://github.com/charcoalphp/charcoal/commit/24c7f5774826080f5bb7eec01c71c4b450810d66))
- **translator:** add missing directory separator for translation files ([83d5a30](https://github.com/charcoalphp/charcoal/commit/83d5a30ab10c745639076cd9ca3bec78e971bb0f))
- update and improve migrate-project script ([d234b26](https://github.com/charcoalphp/charcoal/commit/d234b2645064662fe1729f9d20a4533a468c451b))

## [v2.0.1](https://github.com/charcoalphp/charcoal/compare/v2.0.0...v2.0.1) - 2022-06-13

### Bug Fixes

- add missing arguments aliases in create-release-notes ([eac7d4e](https://github.com/charcoalphp/charcoal/commit/eac7d4e520abc8b83cbea4133c42d404f8a3a6c8))
- **composer:** rename all missing composer packages names ([e879f14](https://github.com/charcoalphp/charcoal/commit/e879f140f96840aa8c8631114ded09039580f381))

## [v2.0.0](https://github.com/charcoalphp/charcoal/compare/v1.3.4...v2.0.0) - 2022-06-08

### Changes :

### ⚠ BREAKING CHANGES

- The location of the packages must be changed everywhere they are called.

### Code Refactoring

- remove the "charcoal-" prefix from the package names ([3302354](https://github.com/charcoalphp/charcoal/commit/3302354378025fe038f5b8091d8b54dfb50ba54a))

## [v1.3.4](https://github.com/charcoalphp/charcoal/compare/v1.3.3...v1.3.4) - 2022-06-08

### Changes :

### Bug Fixes

- **action:** add token to checkout in update-changelog.yaml ([20a51aa](https://github.com/charcoalphp/charcoal/commit/20a51aa09fbd8eff49100d68654a3fddf84f5bc0))
- Regenerate composer.lock from PHP 7.4 ([4d1eddd](https://github.com/charcoalphp/charcoal/commit/4d1edddccf66630b5582ef7e7897df4e8f9acc4a))

## [v1.3.3](https://github.com/charcoalphp/charcoal/compare/v1.1.1...v1.3.3) - 2022-06-08

### Changes :

### Bug Fixes

- **action:** add token to checkout in update-changelog.yaml ([f491bbd](https://github.com/charcoalphp/charcoal/commit/f491bbd8456f19882b3ee1bd6358b8681b62e8d8))

## [v1.1.1](https://github.com/charcoalphp/charcoal/compare/v1.1.0...v1.1.1) - 2022-06-01

### Bug Fixes

- **gitignore:** remove CHANGELOG from gitignore ([d695b92](https://github.com/charcoalphp/charcoal/commit/d695b927cba3ae2fd7033c58db36b4b0956cb3bc))
- **release:** remove pull_request event from release action ([1b64881](https://github.com/charcoalphp/charcoal/commit/1b648818bd7f828cd03b0747e9318ee386b54982))
- **release:** remove pull_request generation from semantic release workflow ([3027fcb](https://github.com/charcoalphp/charcoal/commit/3027fcbfbf34dc813c73b1d6ba54591e9d6817ee))

## [v1.1.0](https://github.com/charcoalphp/charcoal/compare/v1.0.0...v1.1.0) - 2022-05-31

### Bug Fixes

- **changelog:** add title and description to changelog generation ([792cfbf](https://github.com/charcoalphp/charcoal/commit/792cfbfcdfb9755494321fbd82908ffd8ae2a2b7))
- **changelog:** remove duplicated blocks ([4bb817f](https://github.com/charcoalphp/charcoal/commit/4bb817f2218b1a0e2c72e56dfb207e50fd9743ad))
- **readme:** fix some html errors/typos ([abb0e9f](https://github.com/charcoalphp/charcoal/commit/abb0e9fa4e2b7540d691e953c3125cbc8ea2f0a5))
- **readme:** fix some html errors/typos ([7c16128](https://github.com/charcoalphp/charcoal/commit/7c1612873e91836126ec23ee8c405b80f14047a6))
- **release:** test commit for release ([20d9990](https://github.com/charcoalphp/charcoal/commit/20d9990990835255d69a09be12c91fae24f04e89))
- **release:** test commit for release ([a0a0c55](https://github.com/charcoalphp/charcoal/commit/a0a0c55b7110f76d202a01d19061a82e07d26112))
- **release:** test commit for release ([a327480](https://github.com/charcoalphp/charcoal/commit/a327480d9892d7c2c9d0f400fcd21b29ebd4b0be))
- **release:** test commit for release ([9021792](https://github.com/charcoalphp/charcoal/commit/90217923b5a445bfe49460fa748ea52392224416))
- **typehint:** fix a missing return type conflict ([b9d31c1](https://github.com/charcoalphp/charcoal/commit/b9d31c1f89f59cc122db39490abfdcf6474cce16))
- **typehint:** fix a missing return type conflict ([df8938c](https://github.com/charcoalphp/charcoal/commit/df8938c740afcbc6b2e3d616ad264d2d6c5153e0))
- **typo:** fix typo for Translator mustache helper ([dc7d20a](https://github.com/charcoalphp/charcoal/commit/dc7d20af5c23e5d03cf1e952eb67390db0fd6e02))
- **typo:** fix typo for Translator mustache helper ([3e82bb7](https://github.com/charcoalphp/charcoal/commit/3e82bb77dd9fd8ae492503ff960a3dbc255af85e))

### Features

- **changelog:** add title and description to changelog generation ([93b8f2a](https://github.com/charcoalphp/charcoal/commit/93b8f2ae9b01c82a714c87b8b9257daa42e60288))
- **release:** add github pull-request ([79e6915](https://github.com/charcoalphp/charcoal/commit/79e69154876231c292e0c1c30c37a4a3dc15197d))
- **release:** add github pull-request to .releaserc ([8a5f33e](https://github.com/charcoalphp/charcoal/commit/8a5f33eafe6f9f7e252c4ee75b9b7d24c21f00e6))

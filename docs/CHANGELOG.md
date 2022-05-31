# [1.0.0-beta.3](https://github.com/locomotive-charcoal/Charcoal/compare/v1.0.0-beta.2...v1.0.0-beta.3) (2022-05-31)


### Bug Fixes

* **readme:** fix some html errors/typos ([7c16128](https://github.com/locomotive-charcoal/Charcoal/commit/7c1612873e91836126ec23ee8c405b80f14047a6))
* **typehint:** fix a missing return type conflict ([df8938c](https://github.com/locomotive-charcoal/Charcoal/commit/df8938c740afcbc6b2e3d616ad264d2d6c5153e0))
* **typo:** fix typo for Translator mustache helper ([3e82bb7](https://github.com/locomotive-charcoal/Charcoal/commit/3e82bb77dd9fd8ae492503ff960a3dbc255af85e))

# 1.0.0 (2022-05-27)


### Bug Fixes

* a user email validation error when only changing the case of some caracters. Make a comparison with lowercase converted strings. ([b512a0e](https://github.com/Locomotive-Charcoal/Charcoal/commit/b512a0ed82a5eabd5e537d37a85553e2207653d3))
* **accordion:** remove 'u-truncate' css class from accordion preview ([917da63](https://github.com/Locomotive-Charcoal/Charcoal/commit/917da63e76d77d043d7ebfc60e025ac103692bbb))
* **action:** fix syntax in github action ([46b5894](https://github.com/Locomotive-Charcoal/Charcoal/commit/46b5894d920b380206c0eb9ace71b5e28f39e521))
* **admin@0.23:** update composer with conflict for charcoal-admin@<0.23.0 and fix the form group widgets id prefixes ([804cc50](https://github.com/Locomotive-Charcoal/Charcoal/commit/804cc50d7a23f12edb2c4203259e9af1e9154916))
* **arrayAccess:** fix some broken methods to arrayAccess ([d87f22f](https://github.com/Locomotive-Charcoal/Charcoal/commit/d87f22f056f843f2d6526889e7707ed97ae6e43d))
* **asset-manage:** fix merging issues by allowing namespaced files array ([7ed4d1c](https://github.com/Locomotive-Charcoal/Charcoal/commit/7ed4d1c9ab01e706d52e9e514370248c2e598580))
* **attachment-container-trait:** remove reference to removed function 'attachmentWidget()' ([d25da91](https://github.com/Locomotive-Charcoal/Charcoal/commit/d25da91ad3ef6e192845dedc4ef70e5b72d02b79))
* **attachment-widget:** set the js widget type from the actual widget type ([a9531e9](https://github.com/Locomotive-Charcoal/Charcoal/commit/a9531e9b061503271eb588e914cb0b555fe29d09))
* **audio-widget:** fix the elfinder url not bound correctly when used from audio-widget ([ac37929](https://github.com/Locomotive-Charcoal/Charcoal/commit/ac379291f0cee31e7188b364a13b2d85f4f729cb))
* **bad-paste:** replace undefined variable with the intended one. ([e40bb61](https://github.com/Locomotive-Charcoal/Charcoal/commit/e40bb61b52cd569d9cdeb6e26efe028e458ceb9d))
* **bbgmap:** update bbgmap to 0.3.1 and use the minified file when concatenating ([a787854](https://github.com/Locomotive-Charcoal/Charcoal/commit/a787854694cd7b2cb83b523727f2f461da1301ed))
* **bin:** add a bin to composer.json to link with charcoal-app bin file ([a7b4be5](https://github.com/Locomotive-Charcoal/Charcoal/commit/a7b4be5adb29ef4e18dc4d64d865e6e7e6666353))
* **bin:** create a new charcoal bin file in the project because of relative file paths conflicts ([e502d1e](https://github.com/Locomotive-Charcoal/Charcoal/commit/e502d1e7311465a676b10cb0d296ee2f4b8541cb))
* **bin:** create a new charcoal bin file in the project because of relative file paths conflicts ([9108050](https://github.com/Locomotive-Charcoal/Charcoal/commit/910805099b9686f716484aad1d40dd7d4b3180a9))
* **build:concat:** Fix the concat task to require de non-minified version of bb-gamp ([63c3857](https://github.com/Locomotive-Charcoal/Charcoal/commit/63c3857eff648861e3b33fc38350abccf774a160))
* **cache-facade:** fix splat operator parameter type ([93ddb6d](https://github.com/Locomotive-Charcoal/Charcoal/commit/93ddb6dd91409bc8f075276ec912323ee5fddca7))
* **cache-service-provider:** remove obsolete container key ([0b47a23](https://github.com/Locomotive-Charcoal/Charcoal/commit/0b47a23e4a9a471b5b9e3baa3bad0ae67391456a))
* **charcoal-admin:** fix some missing return type ([ee493a5](https://github.com/Locomotive-Charcoal/Charcoal/commit/ee493a50037b2245260d8d0afa82456ff43ef7fe))
* **charcoal-admin:** fix some missing return type et wrong method name ([2df9b0d](https://github.com/Locomotive-Charcoal/Charcoal/commit/2df9b0da513d3798dccc282092991bb06ff30edf))
* **charcoal-app:** fix issues with missing return types on some method implementations ([550411c](https://github.com/Locomotive-Charcoal/Charcoal/commit/550411cd4497d197f311447c9bb979225fddb4a7))
* **charcoal-view:** fix issues with missing return types on some method implementations ([dfecc38](https://github.com/Locomotive-Charcoal/Charcoal/commit/dfecc381c2e9aa92e70e4a60b4be498620c2bc2c))
* **composer:** fix duplicate entry ([11db546](https://github.com/Locomotive-Charcoal/Charcoal/commit/11db546434290cb92b9d5c3ebefa7ae2e561e0ef))
* **composer:** fix name ([57fe269](https://github.com/Locomotive-Charcoal/Charcoal/commit/57fe2696fac4bbce2fe2529bf56d61ecb7ff5743))
* **copy-assets:** fix admin directory permissions ([8041829](https://github.com/Locomotive-Charcoal/Charcoal/commit/8041829763f973c8660b4c2416ec5e61688fdc9f))
* **date-helper:** fix date range detection ([e8e6522](https://github.com/Locomotive-Charcoal/Charcoal/commit/e8e6522aa54582cb50299b91a445ce868e453e91))
* **dependencies:** bump php version to 7.3 ([8b73cc1](https://github.com/Locomotive-Charcoal/Charcoal/commit/8b73cc1c868b01204247b404abf4d0f30ff964e2))
* **email:** fix error catching for email::send() ([4d10ab3](https://github.com/Locomotive-Charcoal/Charcoal/commit/4d10ab33ba570616ca2aa688f68076e1d14ab2da))
* **eslint:** fix an issue where eslint was scoped to the project's root instead of being locked to charcoal-admin ([fcf6dd0](https://github.com/Locomotive-Charcoal/Charcoal/commit/fcf6dd036d3d4b3fa7e5ed3fe21ae0bb2bac0e96))
* **form_group_:** remove 'form_group_' prefixes for form group widgets ([105d0e7](https://github.com/Locomotive-Charcoal/Charcoal/commit/105d0e73295d1a32807fefcf30ebf88452b324c1))
* **geometry-widget:** fix an issue where the widget might send an empty array when saving ([7218bff](https://github.com/Locomotive-Charcoal/Charcoal/commit/7218bffbe2e5ccb8929452444a0b3a946ca6ac18))
* **hierarchical-object-property:** fix a master reference to the new 'masterObject' ([9b34dbf](https://github.com/Locomotive-Charcoal/Charcoal/commit/9b34dbfd77ac692c72ac4f8c736039a9c16364ce))
* **hierarchical-trait:** move the loading of a master object to its own function to prevent hasty object load and allow access to the actual master object's id without the need to load it first ([b435981](https://github.com/Locomotive-Charcoal/Charcoal/commit/b43598126b5f8765e039d7c40ad022444901b0ed))
* **hierarchical-trait:** remove error_log and mode resetHierarchy to proper location ([09376aa](https://github.com/Locomotive-Charcoal/Charcoal/commit/09376aa1acac607304a3681b4fee872c3430b73b))
* **menu-footer:** Fix the footer menu not staying at the bottom of the page when there was no secondary menu ([d874e98](https://github.com/Locomotive-Charcoal/Charcoal/commit/d874e988bcd7a3f47f039ff9071043830cd2814d))
* **migration:** add extra logging to Migration20200827131900.php ([19e9163](https://github.com/Locomotive-Charcoal/Charcoal/commit/19e9163d9853b548ccc6405ff424bcded4d2626b))
* **migration:** add migration for last commit ([33da36c](https://github.com/Locomotive-Charcoal/Charcoal/commit/33da36ce43fd86dbcf5ef26d2d18c9367af51364))
* **multiGroupWidget:** Fix a missing setLayoutBuilder while setting dependencies ([bff460e](https://github.com/Locomotive-Charcoal/Charcoal/commit/bff460e703ab6f47fc940a3c114811e26f84c041))
* **php tests:** fix a failing php test caused by assuming an unfailing sql query ([16d41eb](https://github.com/Locomotive-Charcoal/Charcoal/commit/16d41eb959ad98d5a4555479d63f166ddf0a3945))
* **php tests:** fix some failing php tests ([da4039a](https://github.com/Locomotive-Charcoal/Charcoal/commit/da4039abb76171db91d3e0e75c5cd628d7765617))
* **phpcs:** fix a phpcs warning ([9b43a39](https://github.com/Locomotive-Charcoal/Charcoal/commit/9b43a39f7e2c2daa4f0f94e9bb0bc63ac8df3ab1))
* **property-widgets:** fix some missing parent constructor calls for some property widgets, preventing them to be stored correctly in the component mangager ([12d0965](https://github.com/Locomotive-Charcoal/Charcoal/commit/12d0965c815f0719c8b560e1027bf554e771143e))
* **queue-manager:** fix a loop logic causing an extra recursion ([1953b64](https://github.com/Locomotive-Charcoal/Charcoal/commit/1953b64eb576e5c55dcf90f73d1f7f4403e19d40))
* **quick-form-ident:** fix the quick-form-ident not being passed correctly to form widget ([ff9688d](https://github.com/Locomotive-Charcoal/Charcoal/commit/ff9688dc2fa6db2fc05baeca2e37568d4a7d21ca))
* **secondary-menu:** fix an issue with display options merging ([121fd60](https://github.com/Locomotive-Charcoal/Charcoal/commit/121fd609b7c36a8c8b5dd270bff7fb377d65b18c))
* **selectize:** Fix a missing setter for selectize:setOptgroupObjMap ([e07be38](https://github.com/Locomotive-Charcoal/Charcoal/commit/e07be38ba62c685f692fcde8af867070e36b3a9d))
* **selectize:** fix an issue with property name when using selectize input in multiple strings mode. ([3208b55](https://github.com/Locomotive-Charcoal/Charcoal/commit/3208b55c2c94c6ca5ec9b4099dee321442c43f35))
* **tests:** add missing comma ([51de84d](https://github.com/Locomotive-Charcoal/Charcoal/commit/51de84d4646afcddcf3d46243923faac98bf700d))
* **tests:** add missing data for tests ([cd1f4b7](https://github.com/Locomotive-Charcoal/Charcoal/commit/cd1f4b78a7c67bd5bd1f4cbdfff0ccfdd299ba80))
* **tests:** disgracefully fix unit tests ([49dcb5d](https://github.com/Locomotive-Charcoal/Charcoal/commit/49dcb5d336f25f337f3887c2a40683aaaf1d46aa))
* **tests:** fix failed tests ([fbec50d](https://github.com/Locomotive-Charcoal/Charcoal/commit/fbec50d3f66b636219bc145dcea3d33d63e4bd26))
* **tests:** fix failed tests caused by wrong bracketing ([89212f3](https://github.com/Locomotive-Charcoal/Charcoal/commit/89212f3ce71a64da22cce05efd0b34f9c6c36ac1))
* **tests:** fix identation ([7499e1d](https://github.com/Locomotive-Charcoal/Charcoal/commit/7499e1d47dbfbfc7001da3453754fd3728c4415b))
* **tests:** remove test for float in 'provideTtlOnSave()' method as it doesn't seem to be supported ([ed6d3da](https://github.com/Locomotive-Charcoal/Charcoal/commit/ed6d3da451aac71ae0d3375fc14694edb3c60b1e))
* typo Oject = Object ([9313ec9](https://github.com/Locomotive-Charcoal/Charcoal/commit/9313ec9e39ac775be81d410421dc7c7163e8e39f))
* **typo:** add missing path step backs ([0e68084](https://github.com/Locomotive-Charcoal/Charcoal/commit/0e6808490f143b696fa6d658661cd2518b096f49))
* **user-create:** fix the User/CreateScript to allow User table creation in the process ([d1453f5](https://github.com/Locomotive-Charcoal/Charcoal/commit/d1453f5f32ece575f033e433dad29432bc03596c))
* **user-interface:** remove the 'required' flag on 'display_name' ([27b12b5](https://github.com/Locomotive-Charcoal/Charcoal/commit/27b12b5345dcb43836a486b427e7e37bcf8373be))


### Features

* **cache-key-callback:** add a callback function to process the cache key in the cache middleware ([42e6a7f](https://github.com/Locomotive-Charcoal/Charcoal/commit/42e6a7f2a57303b5c2346e7966c35f8412df3964))
* **cache-middleware:** rework the implementation to allow container extension instead and add a callback setter methode in CacheMiddleware.php ([107bcbf](https://github.com/Locomotive-Charcoal/Charcoal/commit/107bcbf849957f8f8daa4541193f81f03781d888))
* Container attachments are now able to delete their children ([76eeb18](https://github.com/Locomotive-Charcoal/Charcoal/commit/76eeb18d9293e868618701b53c3a5894a86e0e73))
* Container attachments are now able to delete their children ([6317430](https://github.com/Locomotive-Charcoal/Charcoal/commit/6317430b31567c0247674db863e4351dd5f150cb))
* **elfinder:** fetch allowed mimetypes from property config in connectorAction ([155e132](https://github.com/Locomotive-Charcoal/Charcoal/commit/155e1321dcfef23788dd0ae46ea581806053c11a))
* **email-queue-item:** add newly required property `status` ([f3d7ba0](https://github.com/Locomotive-Charcoal/Charcoal/commit/f3d7ba04c8d7cbba557e0df309d3718ba24b715c))
* **email:** improve email by making it extends Abstract entity to add ArrayAccess capabilties ([0a60566](https://github.com/Locomotive-Charcoal/Charcoal/commit/0a60566df66b5dc60127dfadf63e140b8b782fb1))
* **exception:** add exception interface ([8e381f3](https://github.com/Locomotive-Charcoal/Charcoal/commit/8e381f3d921d944094fdd26db51804d9dec353c2))
* **module:** add a new default asset collection for js module to support native module through script type=module ([b7fa496](https://github.com/Locomotive-Charcoal/Charcoal/commit/b7fa496d570f033e550f0112aa451c94cb617c8c))
* **monorepo:** init monorepo-builder ([bf3f78f](https://github.com/Locomotive-Charcoal/Charcoal/commit/bf3f78fdc4fab65c7631bcc6f8f28c469114da04))
* **monorepo:** merge packages composer dependencies in root composer with monorepo-builder merge ([1a34e96](https://github.com/Locomotive-Charcoal/Charcoal/commit/1a34e96ec239d1725d8e6ca67dac17f638405e69))
* **monorepo:** update all packages composer.json to eliminate interdependency conflicts ([e3cec63](https://github.com/Locomotive-Charcoal/Charcoal/commit/e3cec63f79705b941a50b0b236341037ae0384f9))
* **queue-item:** add queue item expiry generation and props ([b1e0641](https://github.com/Locomotive-Charcoal/Charcoal/commit/b1e0641da00246297f049aca24a0e894a12558f6))
* **queue-item:** improve QueueItemTrait and QueueItemInterface with a status property, some status constants and a logging method ([c04a63d](https://github.com/Locomotive-Charcoal/Charcoal/commit/c04a63d62213d58c0147aa4d5b524ee6a33f167f))
* **queue-manager:** add a filter to exclude expired queue items ([f18145a](https://github.com/Locomotive-Charcoal/Charcoal/commit/f18145ac9c478645610bbda76e5dd25c765cc5d7))
* **queue-manager:** transfer responsibility for queueItem's "processed" flag validation and update to AbstractQueueManager ([0655836](https://github.com/Locomotive-Charcoal/Charcoal/commit/0655836a6e0fc83c8885785a8768373c908e6b53))
* **queue:** add queue expiry property ([4c7f614](https://github.com/Locomotive-Charcoal/Charcoal/commit/4c7f614b9adb1bfc776ab73a8484b68c64e5af23))
* **selectize:** add ability to pass form_data to selectize quick_form ([c41e943](https://github.com/Locomotive-Charcoal/Charcoal/commit/c41e9432f0cfb02674b86e066d86135042b72987))
* **selectize:** add recursive translation to parseSelectizeOptions ([f229945](https://github.com/Locomotive-Charcoal/Charcoal/commit/f2299455716195844d28f801377e5e018c28e971))
* **selectize:** Add support for optgroups generation based on object property of selectable propperty. ([945226c](https://github.com/Locomotive-Charcoal/Charcoal/commit/945226cdb3c40c0a604d25a21782ae5910430c86))


### Reverts

* Revert "Insert HTML line breaks in TextDisplay" ([41da106](https://github.com/Locomotive-Charcoal/Charcoal/commit/41da106e1e42cb05d877c5b2c82f875ec783aafd))
* Revert "Bump branch-alias to 0.8" ([882c6ca](https://github.com/Locomotive-Charcoal/Charcoal/commit/882c6ca2cc505ce5b754b1477c2d831c0ea7ad68))
* Revert "Fix admin module exclusion from route to allow URLs such as /administration" ([b204c20](https://github.com/Locomotive-Charcoal/Charcoal/commit/b204c20b6366a948dd430bdecebcbd67988bae86))
* Revert "Set translator from dependencies, not constructor" ([8983ff2](https://github.com/Locomotive-Charcoal/Charcoal/commit/8983ff298bf1f32c421c0dbc703216043862f7f2))
* Revert "Revert "Fix factories usage."" ([b15e648](https://github.com/Locomotive-Charcoal/Charcoal/commit/b15e6483eca02d17f552c579723e5a19b5a4ba83))
* Revert "Fixed use case for RouteManager" ([5ff9da9](https://github.com/Locomotive-Charcoal/Charcoal/commit/5ff9da98afa730ac5246de4e1f197d131e6aeb8f))
* Revert "Fixed exceptions thrown from checking existance" ([0c08fc4](https://github.com/Locomotive-Charcoal/Charcoal/commit/0c08fc476a2c5fc5fb12119fc5f4d55d060307f7))

# [1.0.0-beta.2](https://github.com/Locomotive-Charcoal/Charcoal/compare/v1.0.0-beta.1...v1.0.0-beta.2) (2022-05-27)


### Bug Fixes

* **composer:** fix duplicate entry ([11db546](https://github.com/Locomotive-Charcoal/Charcoal/commit/11db546434290cb92b9d5c3ebefa7ae2e561e0ef))

<?php

namespace Charcoal\Admin\ServiceProvider;

// From Pimple
use Charcoal\Admin\AssetsConfig;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Assetic\Asset\AssetReference;
use Charcoal\Attachment\Object\File;
use Charcoal\Factory\GenericResolver;
// from 'kriswallsmith/assetic'
use Assetic\AssetManager;
// From PSR-7
use Psr\Http\Message\UriInterface;
// From Slim
use Slim\Csrf\Guard;
use Slim\Http\Uri;
// From 'charcoal-app'
use Charcoal\App\Middleware\CsrfMiddleware;
// From Mustache
use Mustache\LambdaHelper;
// From 'charcoal-config'
use Charcoal\Config\ConfigInterface;
use Charcoal\Config\GenericConfig as Config;
// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;
// From 'charcoal-core'
use Charcoal\Model\Service\MetadataConfig;
// From 'charcoal-ui'
use Charcoal\Ui\ServiceProvider\UiServiceProvider;
// From 'charcoal-email'
use Charcoal\Email\ServiceProvider\EmailServiceProvider;
// From 'charcoal-factory'
use Charcoal\Factory\GenericFactory as Factory;
// From 'charcoal-user'
use Charcoal\User\Authenticator;
use Charcoal\User\Authorizer;
// From 'charcoal-view'
use Charcoal\View\EngineInterface;
use Charcoal\View\GenericView;
use Charcoal\View\ViewConfig;
use Charcoal\View\ViewInterface;
// From 'charcoal-admin'
use Charcoal\Admin\Config as AdminConfig;
use Charcoal\Admin\Property\PropertyInputInterface;
use Charcoal\Admin\Property\PropertyDisplayInterface;
use Charcoal\Admin\Service\SelectizeRenderer;
use Charcoal\Admin\Ui\SecondaryMenu\GenericSecondaryMenuGroup;
use Charcoal\Admin\Ui\SecondaryMenu\SecondaryMenuGroupInterface;
use Charcoal\Admin\User;
use Charcoal\Admin\User\AuthToken;

/**
 * Charcoal Administration Service Provider
 *
 * ## Services
 *
 * - Module
 * - Config
 * - Widget Factory
 */
class AdminServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param  Container $container The Pimple DI container.
     * @return void
     */
    public function register(Container $container)
    {
        // Ensure dependencies are set
        $container->register(new EmailServiceProvider());
        $container->register(new UiServiceProvider());
        $container->register(new AssetsManagerServiceProvider());

        $this->registerAdminServices($container);
        $this->registerFactoryServices($container);
        $this->registerElfinderServices($container);
        $this->registerSelectizeServices($container);
        $this->registerMetadataExtensions($container);
        $this->registerAuthExtensions($container);
        $this->registerMiddlewareServices($container);
        $this->registerViewExtensions($container);
        $this->registerAssetsManager($container);

        // Register Access-Control-List (acl)
        $container->register(new AclServiceProvider());
    }

    /**
     * Registers admin services.
     *
     * @param  Container $container The Pimple DI container.
     * @return void
     */
    protected function registerAdminServices(Container $container)
    {
        /**
         * The admin configset.
         *
         * @param  Container $container The Pimple DI Container.
         * @return AdminConfig
         */
        $container['admin/config'] = function (Container $container) {
            $appConfig = $container['config'];

            $extraConfigs = [];

            if (isset($container['module/classes'])) {
                $modules = $container['module/classes'];
                foreach ($modules as $module) {
                    if (defined(sprintf('%s::ADMIN_CONFIG', $module))) {
                        $moduleAdminConfigs = (array)$module::ADMIN_CONFIG;
                        array_push($extraConfigs, ...$moduleAdminConfigs);
                    };
                }
            }

            // The `admin.json` file is not part of regular config
            if (!empty($appConfig['admin_config'])) {
                $appAdminConfigs = (array)$appConfig['admin_config'];
                $appAdminConfigs = $container['config']->resolveValues($appAdminConfigs);
                array_push($extraConfigs, ...$appAdminConfigs);
            }

            if (!empty($extraConfigs)) {
                foreach ($extraConfigs as $path) {
                    $configPath =  $appConfig['base_path'] . DIRECTORY_SEPARATOR . ltrim($path, '/');

                    $appConfig->addFile($configPath);
                }
            }

            $adminConfig = $appConfig['admin'];
            if (!($adminConfig instanceof AdminConfig)) {
                $adminConfig = new AdminConfig($appConfig['admin']);
            }

            return $adminConfig;
        };

        if (!isset($container['admin/base-url'])) {
            /**
             * Base Admin URL as a PSR-7 UriInterface object for the current request
             * or the Charcoal application.
             *
             * @param  Container $container The Pimple DI Container.
             * @return \Psr\Http\Message\UriInterface
             */
            $container['admin/base-url'] = function (Container $container) {
                $adminConfig = $container['admin/config'];

                if (isset($adminConfig['base_url'])) {
                    $adminUrl = $adminConfig['base_url'];
                } else {
                    $adminUrl = clone $container['base-url'];
                    if ($adminConfig['base_path']) {
                        $basePath  = rtrim($adminUrl->getBasePath(), '/');
                        $adminPath = ltrim($adminConfig['base_path'], '/');
                        $adminUrl  = $adminUrl->withBasePath($basePath . '/' . $adminPath);
                    }
                }

                $adminUrl = Uri::createFromString($adminUrl)->withUserInfo('');

                /** Fix the base path */
                $path = $adminUrl->getPath();
                if ($path) {
                    $adminUrl = $adminUrl->withBasePath($path)->withPath('');
                }

                return $adminUrl;
            };
        }

        /**
         * Overwrite view instance.
         *
         * @param GenericView $view The view instance.
         * @param Container $container A container instance.
         * @return ViewInterface
         */
        $container->extend('view', function (GenericView $view, Container $container): ViewInterface {
            return new GenericView([
                'engine' => $container['view/engine/mustache']
            ]);
        });

        /**
         * Extend view/config.
         *
         * @param ConfigInterface $viewConfig The view config instance.
         * @param Container $container A container instance.
         * @return ViewInterface
         */
        $container->extend('view/config', function (ViewConfig $viewConfig, Container $container): ViewConfig {
            $adminConfig = $container['admin/config'];
            if (isset($adminConfig['view']['paths'])) {
                $viewConfig->addPaths($adminConfig['view']['paths']);
            }
            return $viewConfig;
        });
    }

    /**
     * Registers metadata extensions.
     *
     * @see    \Charcoal\Model\ServiceProvider\ModelServiceProvider
     * @param  Container $container The Pimple DI container.
     * @return void
     */
    protected function registerMetadataExtensions(Container $container)
    {
        if (!isset($container['metadata/config'])) {
            /**
             * @return MetadataConfig
             */
            $container['metadata/config'] = function (Container $container) {
                $settings   = $container['admin/config']['metadata'];
                $metaConfig = new MetadataConfig($settings);

                return $metaConfig;
            };
        } else {
            /**
             * Alters the application's metadata configset.
             *
             * This extension will merge any Admin-only metadata settings.
             *
             * @param  MetadataConfig $metaConfig The metadata configset.
             * @param  Container      $container  The Pimple DI container.
             * @return MetadataConfig
             */
            $container->extend('metadata/config', function (MetadataConfig $metaConfig, Container $container) {
                $settings = $container['admin/config']['metadata'];
                if (is_array($settings) && !empty($settings)) {
                    $metaConfig->merge($settings);
                }

                return $metaConfig;
            });
        }

        /**
         * Alters the application's metadata configset.
         *
         * This extension will duplicate each previously defined
         * metadata include path to introduce an "admin" subdirectory
         * which adds support for Admin-only metadata settings.
         *
         * For example, if a developer defines the following paths:
         *
         * ```json
         * "paths": [
         *     "my-app/metadata/",
         *     "vendor/charcoal/cms/metadata/"
         * ]
         * ```
         *
         * The Admin's service provider will duplicate like so:
         *
         * ```json
         * "paths": [
         *     "my-app/metadata/admin/",
         *     "my-app/metadata/",
         *     "vendor/charcoal/cms/metadata/admin/"
         *     "vendor/charcoal/cms/metadata/"
         * ]
         * ```
         *
         * Any data included from the "admin" subdirectory will override
         * any "base" data that's been imported.
         *
         * @param  MetadataConfig $metaConfig The metadata configset.
         * @param  Container      $container  The Pimple DI container.
         * @return MetadataConfig
         */
        $container->extend('metadata/config', function (MetadataConfig $metaConfig, Container $container) {
            $adminConfig = $container['admin/config'];
            $adminDir    = '/' . trim($adminConfig['base_path'], '/');

            $metaPaths   = $metaConfig->paths();
            $parsedPaths = [];
            foreach ($metaPaths as $basePath) {
                $adminPath = rtrim($basePath, '/') . $adminDir;

                array_push($parsedPaths, $adminPath, $basePath);
            }

            $metaConfig->setPaths($parsedPaths);

            return $metaConfig;
        });
    }

    /**
     * Registers user-authentication extensions.
     *
     * @param  Container $container The Pimple DI container.
     * @return void
     */
    protected function registerAuthExtensions(Container $container)
    {
        /**
         * @param  Container $container The Pimple DI Container.
         * @return Authenticator
         */
        $container['admin/authenticator'] = function (Container $container) {
            return new Authenticator([
                'logger'        => $container['logger'],
                'user_type'     => User::class,
                'user_factory'  => $container['model/factory'],
                'token_type'    => AuthToken::class,
                'token_factory' => $container['model/factory']
            ]);
        };

        /**
         * Replace default Authenticator ('charcoal-ui') with the Admin Authenticator.
         *
         * @todo   Do this right!
         * @param  Container $container The Pimple DI Container.
         * @return Authenticator
         */
        $container['authenticator'] = function (Container $container) {
            return $container['admin/authenticator'];
        };

        /**
         * @param  Container $container The Pimple DI container.
         * @return Authorizer
         */
        $container['admin/authorizer'] = function (Container $container) {
            return new Authorizer([
                'logger'   => $container['logger'],
                'acl'      => $container['admin/acl'],
                'resource' => 'admin'
            ]);
        };

        /**
         * Replace default Authorizer ('charcoal-ui') with the Admin Authorizer.
         *
         * @todo   Do this right!
         * @param  Container $container The Pimple DI Container.
         * @return Authorizer
         */
        $container['authorizer'] = function (Container $container) {
            return $container['admin/authorizer'];
        };
    }

    /**
     * Registers admin-scoped middlewares.
     *
     * @param  Container $container The Pimple DI container.
     * @return void
     */
    public function registerMiddlewareServices(Container $container)
    {
        // Ensure a default configset exists (active, covering the auth
        // templates) so every app gets this protection without having to
        // configure it — while still fully overridable, since an app's own
        // `charcoal/admin/middleware/csrf` config entry, if present, is left
        // untouched.
        $middlewares = ($container['config']['middlewares'] ?: []);
        if (!isset($middlewares['charcoal/admin/middleware/csrf'])) {
            $middlewares['charcoal/admin/middleware/csrf'] = $this->defaultCsrfMiddlewareConfig();
            $container['config']['middlewares'] = $middlewares;
        }

        /**
         * Slim-CSRF guard for the admin area, independent of `charcoal/app`'s
         * own `csrf/guard` (used for the public-facing CSRF middleware, if
         * any) — `Charcoal\App\Middleware\CsrfMiddleware` mutates its guard's
         * failure callable on construction, so two differently-configured
         * middleware instances must not share one guard.
         *
         * @param  Container $container The Pimple DI Container.
         * @return Guard
         */
        $container['admin/csrf/guard'] = function (Container $container) {
            return new Guard();
        };

        /**
         * @param  Container $container The Pimple DI Container.
         * @return CsrfMiddleware
         */
        $container['middlewares/charcoal/admin/middleware/csrf'] = function (Container $container) {
            $wareConfig = $container['config']['middlewares']['charcoal/admin/middleware/csrf'];
            $wareConfig['guard'] = $container['admin/csrf/guard'];
            return new CsrfMiddleware($wareConfig);
        };
    }

    /**
     * The default configset for `charcoal/admin/middleware/csrf`, used
     * whenever a consuming app hasn't defined its own — covers the admin
     * area's plain-form auth pages (login, lost-password, reset-password),
     * responding with the same `{success, next_url, feedbacks}` shape as any
     * other admin action, since that's what the bundled admin JS expects.
     *
     * Assumes the default `admin` base path (`admin.config.default.json`'s
     * `base_path`). This method is called unconditionally, for every
     * request, before `admin/config` is necessarily registered (it's only
     * registered for requests under the admin path — see
     * {@see \Charcoal\Admin\AdminModule::setUp()}), so it can't reliably
     * read a customized base path here. An app that renames its admin path
     * should define this configset itself, same as it already must adjust
     * other admin-path-dependent integrations.
     *
     * @return array
     */
    private function defaultCsrfMiddlewareConfig(): array
    {
        return [
            'active'         => true,
            'included_path'  => [
                '^/admin/login$',
                '^/admin/account/lost-password$',
                '^/admin/account/reset-password(/.*)?$',
            ],
            'failure_message' => 'Your session has expired. Please try logging in again.',
            'failure_body'    => [
                'success'   => false,
                'next_url'  => null,
                'feedbacks' => [
                    [ 'level' => 'error', 'message' => '{{message}}' ],
                ],
            ],
        ];
    }

    /**
     * Registers view extensions.
     *
     * @param  Container $container The Pimple DI container.
     * @return void
     */
    protected function registerViewExtensions(Container $container)
    {
        if (!isset($container['view/mustache/helpers'])) {
            $container['view/mustache/helpers'] = function () {
                return [];
            };
        }

        /**
         * Extend helpers for the Mustache Engine
         *
         * @return array
         */
        $container->extend('view/mustache/helpers', function (array $helpers, Container $container) {
            $adminUrl = $container['admin/base-url'];

            $urls = [
                /**
                 * Alias of "siteUrl"
                 *
                 * @return UriInterface|null
                 */
                'adminUrl'     => $adminUrl,
                /**
                 * Prepend the administration-area URI to the given path.
                 *
                 * Mustache 3.2 treats a non-string lambda return as section context,
                 * so this helper must return a string.
                 *
                 * @see    \Charcoal\App\ServiceProvider\AppServiceProvider::registerViewServices()
                 * @param  string $uri A URI path to wrap.
                 * @return string
                 */
                'withAdminUrl' => function ($uri, LambdaHelper $helper = null) use ($adminUrl) {
                    if ($helper) {
                        $uri = $helper->render($uri);
                    }

                    $uri = strval($uri);
                    if ($uri === '') {
                        return (string)$adminUrl->withPath('');
                    }

                    $parts = parse_url($uri);
                    if (!isset($parts['scheme'])) {
                        if (!in_array($uri[0], ['/', '#', '?'])) {
                            $path  = isset($parts['path']) ? ltrim($parts['path'], '/') : '';
                            $query = isset($parts['query']) ? $parts['query'] : '';
                            $hash  = isset($parts['fragment']) ? $parts['fragment'] : '';

                            return (string)$adminUrl->withPath($path)
                                            ->withQuery($query)
                                            ->withFragment($hash);
                        }
                    }

                    return $uri;
                }
            ];

            return array_merge($helpers, $urls);
        });
    }

    /**
     * Registers services for {@link https://studio-42.github.io/elFinder/ elFinder}.
     *
     * @param  Container $container The Pimple DI Container.
     * @return void
     */
    protected function registerElfinderServices(Container $container)
    {
        /**
         * Configure the "config.admin.elfinder" dataset.
         *
         * @param  AdminConfig $adminConfig The admin configset.
         * @return AdminConfig
         */
        $container->extend('admin/config', function (AdminConfig $adminConfig) {
            $adminConfig['elfinder'] = new Config($adminConfig['elfinder']);

            return $adminConfig;
        });

        /**
         * The elFinder configset.
         *
         * @param  Container $container The Pimple DI Container.
         * @return ConfigInterface
         */
        $container['elfinder/config'] = function (Container $container) {
            return $container['admin/config']['elfinder'];
        };
    }

    /**
     * Registers services for {@link https://selectize.github.io/selectize.js/ Selectize}.
     *
     * @param  Container $container The Pimple DI Container.
     * @return void
     */
    protected function registerSelectizeServices(Container $container)
    {
        /**
         * The Selectize Renderer.
         *
         * @param  Container $container The Pimple DI container.
         * @return SelectizeRenderer
         */
        $container['selectize/renderer'] = function (Container $container) {
            return new SelectizeRenderer([
                'logger'           => $container['logger'],
                'translator'       => $container['translator'],
                'template_factory' => $container['template/factory'],
                'view'             => $container['view']
            ]);
        };
    }

    /**
     * @param Container $container Pimple DI container.
     * @return void
     */
    protected function registerAssetsManager(Container $container)
    {
        $container['assets/config'] = function (Container $container) {
            $config = $container['admin/config']->get('assets');

            return new AssetsConfig($config);
        };
    }

    /**
     * Registers the admin factories.
     *
     * @param  Container $container The Pimple DI container.
     * @return void
     */
    protected function registerFactoryServices(Container $container)
    {
        /**
         * @param  Container $container The Pimple DI container.
         * @return FactoryInterface
         */
        $container['property/input/factory'] = function (Container $container) {
            return new Factory([
                'base_class'       => PropertyInputInterface::class,
                'arguments'        => [[
                    'container' => $container,
                    'logger'    => $container['logger']
                ]],
                'resolver_options' => [
                    'suffix' => 'Input'
                ]
            ]);
        };

        /**
         * @param  Container $container The Pimple DI container.
         * @return FactoryInterface
         */
        $container['property/display/factory'] = function (Container $container) {
            return new Factory([
                'base_class'       => PropertyDisplayInterface::class,
                'arguments'        => [[
                    'container' => $container,
                    'logger'    => $container['logger']
                ]],
                'resolver_options' => [
                    'suffix' => 'Display'
                ]
            ]);
        };

        /**
         * @param  Container $container A Pimple DI container.
         * @return FactoryInterface
         */
        $container['secondary-menu/group/factory'] = function (Container $container) {
            return new Factory([
                'base_class'       => SecondaryMenuGroupInterface::class,
                'default_class'    => GenericSecondaryMenuGroup::class,
                'arguments'        => [[
                    'container'      => $container,
                    'logger'         => $container['logger'],
                    'view'           => $container['view'],
                    'layout_builder' => $container['layout/builder']
                ]],
                'resolver_options' => [
                    'suffix' => 'SecondaryMenuGroup'
                ]
            ]);
        };
    }
}

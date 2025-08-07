<?php

namespace Charcoal\Admin\ServiceProvider;

use Charcoal\Admin\AssetsConfig;
use DI\Container;
// From Slim
use Nyholm\Psr7\Uri;
// From Mustache
use Mustache_LambdaHelper as LambdaHelper;
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
use Charcoal\View\GenericView;
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
use Psr\Container\ContainerInterface;

/**
 * Charcoal Administration Service Provider
 *
 * ## Services
 *
 * - Module
 * - Config
 * - Widget Factory
 */
class AdminServiceProvider
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $container The DI container.
     * @return void
     */
    public function register(ContainerInterface $container)
    {
        // Ensure dependencies are set
        (new EmailServiceProvider())->register($container);
        (new UiServiceProvider())->register($container);
        (new AssetsManagerServiceProvider())->register($container);

        $this->registerAdminServices($container);
        $this->registerFactoryServices($container);
        $this->registerElfinderServices($container);
        $this->registerSelectizeServices($container);
        $this->registerMetadataExtensions($container);
        $this->registerAuthExtensions($container);
        $this->registerViewExtensions($container);
        $this->registerAssetsManager($container);

        // Register Access-Control-List (acl)
        (new AclServiceProvider())->register($container);
    }

    /**
     * Registers admin services.
     *
     * @param  Container $container The DI Container.
     * @return void
     */
    protected function registerAdminServices(ContainerInterface $container)
    {
        /**
         * The admin configset.
         *
         * @param  Container $container The DI Container.
         * @return AdminConfig
         */
        $container->set('admin/config', function (Container $container) {
            $appConfig = $container->get('config');

            $extraConfigs = [];

            if (($container->has('module/classes'))) {
                $modules = $container->get('module/classes');
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
                $appAdminConfigs = $container->get('config')->resolveValues($appAdminConfigs);
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
        });

        if (!($container->has('admin/base-url'))) {
            /**
             * Base Admin URL as a PSR-7 UriInterface object for the current request
             * or the Charcoal application.
             *
             * @param  Container $container The DI Container.
             * @return \Psr\Http\Message\UriInterface
             */
            $container->set('admin/base-url', function (Container $container) {
                $adminConfig = $container->get('admin/config');

                if (isset($adminConfig['base_url'])) {
                    $adminUrl = $adminConfig['base_url'];
                } else {
                    /** @var Uri $adminUrl */
                    $adminUrl = clone $container->get('base-url');
                    if ($adminConfig['base_path']) {
                        $basePath  = rtrim($adminUrl->getPath(), '/');
                        $adminPath = ltrim($adminConfig['base_path'], '/');
                        $adminUrl  = $adminUrl->withPath($basePath . '/' . $adminPath);
                    }
                }

                $adminUrl = (new Uri($adminUrl))->withUserInfo('');

                /** Fix the base path */
                $path = $adminUrl->getPath();
                if ($path) {
                    $adminUrl = $adminUrl->withPath($path . '/');
                }

                return $adminUrl;
            });
        }

        /**
         * Overwrite view instance.
         *
         * @param GenericView $view The view instance.
         * @param Container $container A container instance.
         * @return ViewInterface
         */
        $container->set('view', function (Container $container): ViewInterface {
            return new GenericView([
                'engine' => $container->get('view/engine/mustache')
            ]);
        });

        /**
         * Extend view/config.
         *
         * @param ConfigInterface $viewConfig The view config instance.
         * @param Container $container A container instance.
         * @return ViewInterface
         */

        // Get the previous definition (if it exists)
        $previousViewConfig = $container->has('view/config') ? $container->get('view/config') : null;

        // Redefine the service, wrapping the previous
        $container->set('view/config', function (Container $container) use ($previousViewConfig) {
            /** @var \Charcoal\Admin\Config $adminConfig */
            $adminConfig = $container->get('admin/config');
            if ($previousViewConfig && isset($adminConfig['view']['paths'])) {
                $previousViewConfig->addPaths($adminConfig['view']['paths']);
            }
            return $previousViewConfig;
        });
    }

    /**
     * Registers metadata extensions.
     *
     * @see    \Charcoal\Model\ServiceProvider\ModelServiceProvider
     * @param  Container $container The DI Container.
     * @return void
     */
    protected function registerMetadataExtensions(ContainerInterface $container)
    {
        if (!($container->has('metadata/config'))) {
            /**
             * @return MetadataConfig
             */
            $container->set('metadata/config', function (Container $container) {
                $settings   = $container->get('admin/config')['metadata'];
                $metaConfig = new MetadataConfig($settings);

                return $metaConfig;
            });
        } else {
            /**
             * Alters the application's metadata configset.
             * This extension will merge any Admin-only metadata settings.
             */
            if ($container->has('metadata/config')) {
                $settings = $container->get('admin/config')['metadata'];
                $metaConfig = $container->get('metadata/config');
                if (is_array($settings) && !empty($settings)) {
                    $metaConfig->merge($settings);
                }
            }
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
         */
        if ($container->has('metadata/config')) {
            $metaConfig  = $container->get('metadata/config');
            $adminConfig = $container->get('admin/config');
            $adminDir    = '/' . trim($adminConfig['base_path'], '/');

            $metaPaths   = $metaConfig->paths();
            $parsedPaths = [];
            foreach ($metaPaths as $basePath) {
                $adminPath = rtrim($basePath, '/') . $adminDir;

                array_push($parsedPaths, $adminPath, $basePath);
            }

            $metaConfig->setPaths($parsedPaths);
        }
    }

    /**
     * Registers user-authentication extensions.
     *
     * @param  Container $container The DI Container.
     * @return void
     */
    protected function registerAuthExtensions(ContainerInterface $container)
    {
        /**
         * @param  Container $container The DI Container.
         * @return Authenticator
         */
        $container->set('admin/authenticator', function (Container $container) {
            return new Authenticator([
                'logger'        => $container->get('logger'),
                'user_type'     => User::class,
                'user_factory'  => $container->get('model/factory'),
                'token_type'    => AuthToken::class,
                'token_factory' => $container->get('model/factory')
            ]);
        });

        /**
         * Replace default Authenticator ('charcoal-ui') with the Admin Authenticator.
         *
         * @todo   Do this right!
         * @param  Container $container The DI Container.
         * @return Authenticator
         */
        $container->set('authenticator', function (Container $container) {
            return $container->get('admin/authenticator');
        });

        /**
         * @param  Container $container The DI Container.
         * @return Authorizer
         */
        $container->set('admin/authorizer', function (Container $container) {
            return new Authorizer([
                'logger'   => $container->get('logger'),
                'acl'      => $container->get('admin/acl'),
                'resource' => 'admin'
            ]);
        });

        /**
         * Replace default Authorizer ('charcoal-ui') with the Admin Authorizer.
         *
         * @todo   Do this right!
         * @param  Container $container The DI Container.
         * @return Authorizer
         */
        $container->set('authorizer', function (Container $container) {
            return $container->get('admin/authorizer');
        });
    }

    /**
     * Registers view extensions.
     *
     * @param  Container $container The DI Container.
     * @return void
     */
    protected function registerViewExtensions(ContainerInterface $container)
    {
        /**
         * Extend helpers for the Mustache Engine
         *
         * @return array
         */
        $helpers = $container->has('view/mustache/helpers') ? $container->get('view/mustache/helpers') : [];
        $container->set('view/mustache/helpers', function (Container $container) use ($helpers): array {
            $adminUrl = $container->get('admin/base-url');

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
                 * @see    \Charcoal\App\ServiceProvider\AppServiceProvider::registerViewServices()
                 * @param  string $uri A URI path to wrap.
                 * @return UriInterface|null
                 */
                'withAdminUrl' => function ($uri, ?LambdaHelper $helper = null) use ($adminUrl) {
                    if ($helper) {
                        $uri = $helper->render($uri);
                    }

                    $uri = strval($uri);
                    if ($uri === '') {
                        $uri = $adminUrl->withPath('');
                    } else {
                        $parts = parse_url($uri);
                        if (!isset($parts['scheme'])) {
                            if (!in_array($uri[0], ['/', '#', '?'])) {
                                $path  = isset($parts['path']) ? ltrim($parts['path'], '/') : '';
                                $query = isset($parts['query']) ? $parts['query'] : '';
                                $hash  = isset($parts['fragment']) ? $parts['fragment'] : '';

                                return $adminUrl->withPath($path)
                                                ->withQuery($query)
                                                ->withFragment($hash);
                            }
                        }
                    }

                    return $uri;
                }
            ];

            /*if ($container->has('view/mustache/helpers')) {
                $helpers = $container->get('view/mustache/helpers');
            }*/

            return array_merge($helpers, $urls);
        });
    }

    /**
     * Registers services for {@link https://studio-42.github.io/elFinder/ elFinder}.
     *
     * @param  Container $container The DI Container.
     * @return void
     */
    protected function registerElfinderServices(ContainerInterface $container)
    {
        /**
         * Configure the "config.admin.elfinder" dataset.
         *
         * @param  AdminConfig $adminConfig The admin configset.
         * @return AdminConfig
         */
        $elfinderConfig = new Config($container->get('admin/config')['elfinder']);
        $container->get('admin/config')['elfinder'] = new Config($elfinderConfig);

        /**
         * The elFinder configset.
         *
         * @param  Container $container The DI Container.
         * @return ConfigInterface
         */
        $container->set('elfinder/config', function (Container $container) {
            return $container->get('admin/config')['elfinder'];
        });
    }

    /**
     * Registers services for {@link https://selectize.github.io/selectize.js/ Selectize}.
     *
     * @param  Container $container The DI Container.
     * @return void
     */
    protected function registerSelectizeServices(ContainerInterface $container)
    {
        /**
         * The Selectize Renderer.
         *
         * @param  Container $container The DI Container.
         * @return SelectizeRenderer
         */
        $container->set('selectize/renderer', function (Container $container) {
            return new SelectizeRenderer([
                'logger'           => $container->get('logger'),
                'translator'       => $container->get('translator'),
                'template_factory' => $container->get('template/factory'),
                'view'             => $container->get('view')
            ]);
        });
    }

    /**
     * @param Container $container DI Container.
     * @return void
     */
    protected function registerAssetsManager(ContainerInterface $container)
    {
        $container->set('assets/config', function (Container $container) {
            $config = $container->get('admin/config')->get('assets');

            return new AssetsConfig($config);
        });
    }

    /**
     * Registers the admin factories.
     *
     * @param  Container $container The DI Container.
     * @return void
     */
    protected function registerFactoryServices(ContainerInterface $container)
    {
        /**
         * @param  Container $container The DI Container.
         * @return FactoryInterface
         */
        $container->set('property/input/factory', function (Container $container) {
            return new Factory([
                'base_class'       => PropertyInputInterface::class,
                'arguments'        => [[
                    'container' => $container,
                    'logger'    => $container->get('logger')
                ]],
                'resolver_options' => [
                    'suffix' => 'Input'
                ]
            ]);
        });

        /**
         * @param  Container $container The DI Container.
         * @return FactoryInterface
         */
        $container->set('property/display/factory', function (Container $container) {
            return new Factory([
                'base_class'       => PropertyDisplayInterface::class,
                'arguments'        => [[
                    'container' => $container,
                    'logger'    => $container->get('logger')
                ]],
                'resolver_options' => [
                    'suffix' => 'Display'
                ]
            ]);
        });

        /**
         * @param  Container $container A DI Container.
         * @return FactoryInterface
         */
        $container->set('secondary-menu/group/factory', function (Container $container) {
            return new Factory([
                'base_class'       => SecondaryMenuGroupInterface::class,
                'default_class'    => GenericSecondaryMenuGroup::class,
                'arguments'        => [[
                    'container'      => $container,
                    'logger'         => $container->get('logger'),
                    'view'           => $container->get('view'),
                    'layout_builder' => $container->get('layout/builder')
                ]],
                'resolver_options' => [
                    'suffix' => 'SecondaryMenuGroup'
                ]
            ]);
        });
    }
}

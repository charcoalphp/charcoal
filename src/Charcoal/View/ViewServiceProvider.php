<?php

namespace Charcoal\View;

// From Pimple
use Pimple\ServiceProviderInterface;
use Pimple\Container;

// From 'erusev/parsedown'
use Parsedown;

// From 'charcoal-view'
use Charcoal\View\Mustache\MustacheEngine;
use Charcoal\View\Mustache\MustacheLoader;
use Charcoal\View\Mustache\AssetsHelpers;
use Charcoal\View\Mustache\MarkdownHelpers;
use Charcoal\View\Mustache\TranslatorHelpers;
use Charcoal\View\Php\PhpEngine;
use Charcoal\View\Php\PhpLoader;
use Charcoal\View\Twig\TwigEngine;
use Charcoal\View\Twig\TwigLoader;

/**
 * View Service Provider
 *
 * ## Requirements / Dependencies
 *
 * - `config`
 *   - The global / base app config (`ConfigInterface`).
 *
 * ## Services
 *
 * - `view/config`
 *   - The global view config (`ViewConfig`).
 * - `view`
 *   - The default `ViewInterface` object, determined by `view/config`.
 * - `view/renderer`
 *   - A PSR-7 renderer using the default `view` object.
 *
 * ## Helpers
 *
 * - `view/engine`
 *   - The default `EngineInterface` object, determined by `view/config`.
 * - `view/loader`
 *   - The defailt `LoaderInterface` object, determined by `view/config`
 *
 */
class ViewServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $container A container instance.
     * @return void
     */
    public function register(Container $container)
    {
        $this->registerViewConfig($container);
        $this->registerLoaderServices($container);
        $this->registerEngineServices($container);
        $this->registerMustacheTemplatingServices($container);
        $this->registerTwigTemplatingServices($container);
        $this->registerViewServices($container);
    }

    /**
     * @param Container $container The DI container.
     * @return void
     */
    protected function registerViewConfig(Container $container)
    {
        /**
         * @param  Container $container A container instance.
         * @return ViewConfig
         */
        $container['view/config'] = function (Container $container) {
            $appConfig  = isset($container['config']) ? $container['config'] : [];
            $viewConfig = isset($appConfig['view']) ? $appConfig['view'] : null;
            $viewConfig = new ViewConfig($viewConfig);

            if (isset($container['module/classes'])) {
                $extraPaths = [];
                $basePath   = rtrim($appConfig['base_path'], '/');
                $modules    = $container['module/classes'];
                foreach ($modules as $module) {
                    if (defined(sprintf('%s::APP_CONFIG', $module))) {
                        $configPath = ltrim($module::APP_CONFIG, '/');
                        $configPath = $basePath.'/'.$configPath;

                        $configData = $viewConfig->loadFile($configPath);
                        $extraPaths = array_merge(
                            $extraPaths,
                            $configData['view']['paths']
                        );
                    };
                }

                if (!empty($extraPaths)) {
                    $viewConfig->addPaths($extraPaths);
                }
            }

            return $viewConfig;
        };
    }

    /**
     * @param Container $container The DI container.
     * @return void
     */
    protected function registerLoaderServices(Container $container)
    {
        /**
         * @param Container $container A container instance.
         * @return array The view loader dependencies array.
         */
        $container['view/loader/dependencies'] = function (Container $container) {
            return [
                'base_path' => $container['config']['base_path'],
                'paths'     => $container['view/config']['paths']
            ];
        };

        /**
         * @param Container $container A container instance.
         * @return MustacheLoader
         */
        $container['view/loader/mustache'] = function (Container $container) {
            return new MustacheLoader($container['view/loader/dependencies']);
        };

        /**
         * @param Container $container A container instance.
         * @return PhpLoader
         */
        $container['view/loader/php'] = function (Container $container) {
            return new PhpLoader($container['view/loader/dependencies']);
        };

        /**
         * @param Container $container A container instance.
         * @return TwigLoader
         */
        $container['view/loader/twig'] = function (Container $container) {
            return new TwigLoader($container['view/loader/dependencies']);
        };
    }

    /**
     * @param Container $container The DI container.
     * @return void
     */
    protected function registerEngineServices(Container $container)
    {
        /**
         * @param Container $container A container instance.
         * @return MustacheEngine
         */
        $container['view/engine/mustache'] = function (Container $container) {
            return new MustacheEngine([
                'loader'    => $container['view/loader/mustache'],
                'helpers'   => $container['view/mustache/helpers'],
                'cache'     => $container['view/mustache/cache']
            ]);
        };

        /**
         * @param Container $container A container instance.
         * @return PhpEngine
         */
        $container['view/engine/php'] = function (Container $container) {
            return new PhpEngine([
                'loader'    => $container['view/loader/php']
            ]);
        };

        /**
         * @param Container $container A container instance.
         * @return TwigEngine
         */
        $container['view/engine/twig'] = function (Container $container) {
            return new TwigEngine([
                'loader'    => $container['view/loader/twig'],
                'cache'     => $container['view/twig/cache']
            ]);
        };

        /**
         * The default view engine.
         *
         * @param Container $container A container instance.
         * @return \Charcoal\View\EngineInterface
         */
        $container['view/engine'] = function (Container $container) {
            $viewConfig = $container['view/config'];
            $type = $viewConfig['default_engine'];
            return $container['view/engine/'.$type];
        };
    }

    /**
     * @param Container $container The DI container.
     * @return void
     */
    protected function registerMustacheTemplatingServices(Container $container)
    {
        $this->registerMustacheHelpersServices($container);

        /**
         * @param Container $container A container instance.
         * @return string|null
         */
        $container['view/mustache/cache'] = function (Container $container) {
            $viewConfig = $container['view/config'];
            return $viewConfig['engines.mustache.cache'];
        };
    }

    /**
     * @param Container $container The DI container.
     * @return void
     */
    protected function registerMustacheHelpersServices(Container $container)
    {
        if (!isset($container['view/mustache/helpers'])) {
            $container['view/mustache/helpers'] = function () {
                return [];
            };
        }

        /**
         * Asset helpers for Mustache.
         *
         * @return AssetsHelpers
         */
        $container['view/mustache/helpers/assets'] = function () {
            return new AssetsHelpers();
        };

        /**
         * Translation helpers for Mustache.
         *
         * @return TranslatorHelpers|null
         */
        $container['view/mustache/helpers/translator'] = function (Container $container) {
            return new TranslatorHelpers([
                'translator' => (isset($container['translater']) ? $container['translator'] : null)
            ]);
        };

        /**
         * Markdown helpers for Mustache.
         *
         * @return MarkdownHelpers
         */
        $container['view/mustache/helpers/markdown'] = function (Container $container) {
            return new MarkdownHelpers([
                'parsedown' => $container['view/parsedown']
            ]);
        };

        /**
         * Extend global helpers for the Mustache Engine.
         *
         * @param  array     $helpers   The Mustache helper collection.
         * @param  Container $container A container instance.
         * @return array
         */
        $container->extend('view/mustache/helpers', function (array $helpers, Container $container) {
            return array_merge(
                $helpers,
                $container['view/mustache/helpers/assets']->toArray(),
                $container['view/mustache/helpers/translator']->toArray(),
                $container['view/mustache/helpers/markdown']->toArray()
            );
        });
    }

    /**
     * @param Container $container The DI container.
     * @return void
     */
    protected function registerTwigTemplatingServices(Container $container)
    {
        /**
         * @param  Container $container A container instance.
         * @return string|null
         */
        $container['view/twig/cache'] = function (Container $container) {
            $viewConfig = $container['view/config'];
            return $viewConfig['engines.twig.cache'];
        };
    }

    /**
     * @param Container $container The DI container.
     * @return void
     */
    protected function registerViewServices(Container $container)
    {
        /**
         * The default view instance.
         *
         * @param Container $container A container instance.
         * @return ViewInterface
         */
        $container['view'] = function (Container $container) {
            return new GenericView([
                'engine' => $container['view/engine']
            ]);
        };

        /**
         * A PSR-7 renderer, using the default view instance.
         *
         * @param Container $container A container instance.
         * @return Renderer
         */
        $container['view/renderer'] = function (Container $container) {
            return new Renderer([
                'view' => $container['view']
            ]);
        };

        /**
         * A Markdown parser.
         *
         * @return Parsedown
         */
        $container['view/parsedown'] = function () {
            $parsedown = new Parsedown();
            $parsedown->setSafeMode(true);
            return $parsedown;
        };
    }
}

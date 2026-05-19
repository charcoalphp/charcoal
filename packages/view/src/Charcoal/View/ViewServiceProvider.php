<?php

declare(strict_types=1);

namespace Charcoal\View;

use Parsedown;
use Pimple\ServiceProviderInterface;
use Pimple\Container;
use Charcoal\View\EngineInterface;
use Charcoal\View\LoaderInterface;
use Charcoal\View\Mustache\AssetsHelpers as MustacheAssetsHelpers;
use Charcoal\View\Mustache\HelpersInterface as MustacheHelpersInterface;
use Charcoal\View\Mustache\MarkdownHelpers as MustacheMarkdownHelpers;
use Charcoal\View\Mustache\MustacheEngine;
use Charcoal\View\Mustache\MustacheLoader;
use Charcoal\View\Mustache\TranslatorHelpers as MustacheTranslatorHelpers;
use Charcoal\View\Php\PhpEngine;
use Charcoal\View\Php\PhpLoader;
use Charcoal\View\Renderer;
use Charcoal\View\Twig\HelpersInterface as TwigHelpersInterface;
use Charcoal\View\Twig\TranslatorHelpers as TwigTranslatorHelpers;
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
     */
    public function register(Container $container): void
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
     */
    protected function registerViewConfig(Container $container): void
    {
        /**
         * @param  Container $container A container instance.
         * @return ViewConfig
         */
        $container['view/config'] = function (Container $container): ViewConfig {
            $appConfig  = $container['config'] ?? [];
            $viewConfig = $appConfig['view'] ?? null;
            $viewConfig = new ViewConfig($viewConfig);

            if (isset($container['module/classes'])) {
                $extraPaths = [];
                $basePath   = $appConfig['base_path'];
                $modules    = $container['module/classes'];
                foreach ($modules as $module) {
                    if (defined(sprintf('%s::APP_CONFIG', $module))) {
                        $configPath = ltrim((string) $module::APP_CONFIG, '/');
                        $configPath = $basePath . DIRECTORY_SEPARATOR . $configPath;

                        $configData = $viewConfig->loadFile($configPath);
                        if (isset($configData['view']['paths'])) {
                            $extraPaths = array_merge(
                                $extraPaths,
                                $configData['view']['paths']
                            );
                        }
                    };
                }

                if ($extraPaths !== []) {
                    $viewConfig->addPaths($extraPaths);
                }
            }

            return $viewConfig;
        };
    }

    /**
     * @param Container $container The DI container.
     */
    protected function registerLoaderServices(Container $container): void
    {
        /**
         * @param Container $container A container instance.
         * @return array The view loader dependencies array.
         */
        $container['view/loader/dependencies'] = (fn(Container $container): array => [
            'base_path' => $container['config']['base_path'],
            'paths'     => $container['config']->resolveValues($container['view/config']['paths'])
        ]);

        /**
         * @param Container $container A container instance.
         * @return MustacheLoader
         */
        $container['view/loader/mustache'] = (fn(Container $container): LoaderInterface => new MustacheLoader($container['view/loader/dependencies']));

        /**
         * @param Container $container A container instance.
         * @return PhpLoader
         */
        $container['view/loader/php'] = (fn(Container $container): LoaderInterface => new PhpLoader($container['view/loader/dependencies']));

        /**
         * @param Container $container A container instance.
         * @return TwigLoader
         */
        $container['view/loader/twig'] = (fn(Container $container): LoaderInterface => new TwigLoader($container['view/loader/dependencies']));
    }

    /**
     * @param Container $container The DI container.
     */
    protected function registerEngineServices(Container $container): void
    {
        /**
         * @param Container $container A container instance.
         * @return MustacheEngine
         */
        $container['view/engine/mustache'] = (fn(Container $container): EngineInterface => new MustacheEngine([
            'loader'    => $container['view/loader/mustache'],
            'helpers'   => $container['view/mustache/helpers'],
            'cache'     => $container['view/mustache/cache']
        ]));

        /**
         * @param Container $container A container instance.
         * @return PhpEngine
         */
        $container['view/engine/php'] = (fn(Container $container): EngineInterface => new PhpEngine([
            'loader'    => $container['view/loader/php']
        ]));

        /**
         * @param Container $container A container instance.
         * @return TwigEngine
         */
        $container['view/engine/twig'] = (fn(Container $container): EngineInterface => new TwigEngine([
            'config'    => $container['view/config'],
            'loader'    => $container['view/loader/twig'],
            'helpers'   => $container['view/twig/helpers'],
            'cache'     => $container['view/twig/cache'],
            'debug'     => $container['debug'],
        ]));

        /**
         * The default view engine.
         *
         * @param Container $container A container instance.
         * @return EngineInterface
         */
        $container['view/engine'] = function (Container $container): EngineInterface {
            $viewConfig = $container['view/config'];
            $type = $viewConfig['default_engine'];
            return $container['view/engine/' . $type];
        };
    }

    /**
     * @param Container $container The DI container.
     */
    protected function registerMustacheTemplatingServices(Container $container): void
    {
        $this->registerMustacheHelpersServices($container);

        /**
         * @param Container $container A container instance.
         * @return string|null
         */
        $container['view/mustache/cache'] = function (Container $container): ?string {
            $viewConfig = $container['view/config'];
            return $viewConfig['engines.mustache.cache'];
        };
    }

    /**
     * @param Container $container The DI container.
     */
    protected function registerMustacheHelpersServices(Container $container): void
    {
        if (!isset($container['view/mustache/helpers'])) {
            $container['view/mustache/helpers'] = (fn(): array => []);
        }

        /**
         * Asset helpers for Mustache.
         *
         * @return MustacheAssetsHelpers
         */
        $container['view/mustache/helpers/assets'] = (fn(): MustacheHelpersInterface => new MustacheAssetsHelpers());

        /**
         * Translation helpers for Mustache.
         *
         * @return TranslatorHelpers
         */
        $container['view/mustache/helpers/translator'] = (fn(Container $container): MustacheHelpersInterface => new MustacheTranslatorHelpers([
            'translator' => ($container['translator'] ?? null)
        ]));

        /**
         * Markdown helpers for Mustache.
         *
         * @return MarkdownHelpers
         */
        $container['view/mustache/helpers/markdown'] = (fn(Container $container): MustacheHelpersInterface => new MustacheMarkdownHelpers([
            'parsedown' => $container['view/parsedown']
        ]));

        /**
         * Extend global helpers for the Mustache Engine.
         *
         * @param  array     $helpers   The Mustache helper collection.
         * @param  Container $container A container instance.
         * @return array
         */
        $container->extend('view/mustache/helpers', fn(array $helpers, Container $container): array => array_merge(
            $helpers,
            $container['view/mustache/helpers/assets']->toArray(),
            $container['view/mustache/helpers/translator']->toArray(),
            $container['view/mustache/helpers/markdown']->toArray()
        ));
    }

    /**
     * @param Container $container The DI container.
     * @return void
     */
    protected function registerTwigTemplatingServices(Container $container)
    {
        $this->registerTwigHelpersServices($container);

        /**
         * @param  Container $container A container instance.
         * @return string|null
         */
        $container['view/twig/cache'] = function (Container $container): ?string {
            $viewConfig = $container['view/config'];
            return $viewConfig['engines.twig.cache'];
        };
    }

    /**
     * @param Container $container The DI container.
     * @return void
     */
    protected function registerTwigHelpersServices(Container $container)
    {
        if (!isset($container['view/twig/helpers'])) {
            $container['view/twig/helpers'] = (fn(): array => []);
        }

        /**
         * Translation helpers for Twig.
         *
         * @return TranslatorHelpers
         */
        $container['view/twig/helpers/translator'] = (fn(Container $container): TwigHelpersInterface => new TwigTranslatorHelpers([
            'translator' => $container['translator'],
        ]));

        /**
         * Extend global helpers for the Twig Engine.
         *
         * @param  array     $helpers   The Mustache helper collection.
         * @param  Container $container A container instance.
         * @return array
         */
        $container->extend('view/twig/helpers', fn(array $helpers, Container $container): array => array_merge(
            $helpers,
            $container['view/twig/helpers/translator']->toArray(),
        ));
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
        $container['view'] = (fn(Container $container): ViewInterface => new GenericView([
            'engine' => $container['view/engine']
        ]));

        /**
         * A PSR-7 renderer, using the default view instance.
         *
         * @param Container $container A container instance.
         * @return Renderer
         */
        $container['view/renderer'] = (fn(Container $container): Renderer => new Renderer([
            'view' => $container['view']
        ]));

        /**
         * A Markdown parser.
         *
         * @return Parsedown
         */
        $container['view/parsedown'] = function (): Parsedown {
            $parsedown = new Parsedown();
            $parsedown->setSafeMode(true);
            return $parsedown;
        };
    }
}

<?php

declare(strict_types=1);

namespace Charcoal\View;

use Parsedown;
use DI\Container;
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
use Charcoal\View\Twig\DebugHelpers as TwigDebugHelpers;
use Charcoal\View\Twig\UrlHelpers as TwigUrlHelpers;
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
class ViewServiceProvider
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
     * @return void
     */
    protected function registerViewConfig(Container $container): void
    {
        /**
         * @param  Container $container A container instance.
         * @return ViewConfig
         */
        $container->set('view/config', function (Container $container): ViewConfig {
            $appConfig  = $container->has('config') ? $container->get('config') : [];
            $viewConfig = isset($appConfig['view']) ? $appConfig['view'] : null;
            $viewConfig = new ViewConfig($viewConfig);

            if ($container->has('module/classes')) {
                $extraPaths = [];
                $basePath   = $appConfig['base_path'];
                $modules    = $container->get('module/classes');
                foreach ($modules as $module) {
                    if (defined(sprintf('%s::APP_CONFIG', $module))) {
                        $configPath = ltrim($module::APP_CONFIG, '/');
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

                if (!empty($extraPaths)) {
                    $viewConfig->addPaths($extraPaths);
                }
            }

            return $viewConfig;
        });
    }

    /**
     * @param Container $container The DI container.
     * @return void
     */
    protected function registerLoaderServices(Container $container): void
    {
        /**
         * @param Container $container A container instance.
         * @return array The view loader dependencies array.
         */
        $container->set('view/loader/dependencies', function (Container $container): array {
            return [
                'base_path' => $container->get('config')['base_path'],
                'paths'     => $container->get('config')->resolveValues($container->get('view/config')['paths'])
            ];
        });

        /**
         * @param Container $container A container instance.
         * @return MustacheLoader
         */
        $container->set('view/loader/mustache', function (Container $container): LoaderInterface {
            /*echo '<pre>';
            print_r($container->get('view/loader/dependencies'));
            echo '</pre>';

            foreach ($container->get('view/loader/dependencies')['paths'] as $path) {
                $fullPath = $container->get('view/loader/dependencies')['base_path'] . DIRECTORY_SEPARATOR . $path . 'login.mustache';
                echo $fullPath . '<br>';
                if (file_exists($fullPath)) {
                    exit('Found template at: ' . $fullPath);
                }
            }
            exit;*/
            return new MustacheLoader($container->get('view/loader/dependencies'));
        });

        /**
         * @param Container $container A container instance.
         * @return PhpLoader
         */
        $container->set('view/loader/php', function (Container $container): LoaderInterface {
            return new PhpLoader($container->get('view/loader/dependencies'));
        });

        /**
         * @param Container $container A container instance.
         * @return TwigLoader
         */
        $container->set('view/loader/twig', function (Container $container): LoaderInterface {
            return new TwigLoader($container->get('view/loader/dependencies'));
        });
    }

    /**
     * @param Container $container The DI container.
     * @return void
     */
    protected function registerEngineServices(Container $container): void
    {
        /**
         * @param Container $container A container instance.
         * @return MustacheEngine
         */
        $container->set('view/engine/mustache', function (Container $container): EngineInterface {
            return new MustacheEngine([
                'config'    => $container->get('view/config'),
                'loader'    => $container->get('view/loader/mustache'),
                'helpers'   => $container->get('view/mustache/helpers'),
                'cache'     => $container->get('view/mustache/cache'),
                'debug'     => $container->get('debug'),
            ]);
        });

        /**
         * @param Container $container A container instance.
         * @return PhpEngine
         */
        $container->set('view/engine/php', function (Container $container): EngineInterface {
            return new PhpEngine([
                'loader'    => $container->get('view/loader/php')
            ]);
        });

        /**
         * @param Container $container A container instance.
         * @return TwigEngine
         */
        //$twigHelpers = $container->get('view/twig/helpers');
        $container->set('view/engine/twig', function (Container $container): EngineInterface {
            return new TwigEngine([
                'config'    => $container->get('view/config'),
                'loader'    => $container->get('view/loader/twig'),
                'helpers'   => $container->get('view/twig/helpers'),
                'cache'     => $container->get('view/twig/cache'),
                'debug'     => $container->get('debug'),
            ]);
        });

        /**
         * The default view engine.
         *
         * @param Container $container A container instance.
         * @return EngineInterface
         */
        $container->set('view/engine', function (Container $container): EngineInterface {
            $viewConfig = $container->get('view/config');
            $type = $viewConfig['default_engine'];
            return $container->get('view/engine/' . $type);
        });
    }

    /**
     * @param Container $container The DI container.
     * @return void
     */
    protected function registerMustacheTemplatingServices(Container $container): void
    {
        $this->registerMustacheHelpersServices($container);

        /**
         * @param Container $container A container instance.
         * @return string|null
         */
        $container->set('view/mustache/cache', function (Container $container): ?string {
            $viewConfig = $container->get('view/config');
            return $viewConfig['engines.mustache.cache'];
        });
    }

    /**
     * @param Container $container The DI container.
     * @return void
     */
    protected function registerMustacheHelpersServices(Container $container): void
    {
        /**
         * Asset helpers for Mustache.
         *
         * @return MustacheAssetsHelpers
         */
        $container->set('view/mustache/helpers/assets', function (): MustacheHelpersInterface {
            return new MustacheAssetsHelpers();
        });

        /**
         * Translation helpers for Mustache.
         *
         * @return TranslatorHelpers
         */
        $container->set('view/mustache/helpers/translator', function (Container $container): MustacheHelpersInterface {
            return new MustacheTranslatorHelpers([
                'translator' => ($container->has('translator') ? $container->get('translator') : null)
            ]);
        });

        /**
         * A Markdown parser.
         *
         * @return Parsedown
         */
        $container->set('view/parsedown', function (): Parsedown {
            $parsedown = new Parsedown();
            $parsedown->setSafeMode(true);
            return $parsedown;
        });

        /**
         * Markdown helpers for Mustache.
         *
         * @return MarkdownHelpers
         */
        $container->set('view/mustache/helpers/markdown', function (Container $container): MustacheHelpersInterface {
            return new MustacheMarkdownHelpers([
                'parsedown' => $container->get('view/parsedown')
            ]);
        });

        /**
         * Extend global helpers for the Mustache Engine.
         *
         * @param  array     $helpers   The Mustache helper collection.
         * @param  Container $container A container instance.
         * @return array
         */
        $helpers = $container->has('view/mustache/helpers') ? $container->get('view/mustache/helpers') : [];
        $container->set('view/mustache/helpers', function (Container $container) use ($helpers) {
            return array_merge(
                $helpers,
                $container->get('view/mustache/helpers/assets')->toArray(),
                $container->get('view/mustache/helpers/translator')->toArray(),
                $container->get('view/mustache/helpers/markdown')->toArray()
            );
        });
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
        $container->set('view/twig/cache', function (Container $container): ?string {
            $viewConfig = $container->get('view/config');
            return $viewConfig['engines.twig.cache'];
        });
    }

    /**
     * @param Container $container The DI container.
     * @return void
     */
    protected function registerTwigHelpersServices(Container $container)
    {
        //if (!$container->has('view/twig/helpers')) {
        //    $container->set('view/twig/helpers', []);
        //}

        /**
         * Translation helpers for Twig.
         *
         * @return TranslatorHelpers
         */
        $container->set('view/twig/helpers/translator', function (Container $container): TwigHelpersInterface {
            return new TwigTranslatorHelpers([
                'translator' => $container->get('translator'),
            ]);
        });

        $container->set('view/twig/helpers/url', function (Container $container): TwigHelpersInterface {
            return new TwigUrlHelpers([
                'baseUrl' => $container->get('base-url'),
            ]);
        });

        $container->set('view/twig/helpers/debug', function (Container $container): TwigHelpersInterface {
            return new TwigDebugHelpers([
                'debug'  => $container->get('debug'),
            ]);
        });

        $helpers = $container->has('view/twig/helpers') ? $container->get('view/twig/helpers') : [];
        $container->set('view/twig/helpers', array_merge(
            $helpers,
            $container->get('view/twig/helpers/url')->toArray(),
            $container->get('view/twig/helpers/translator')->toArray(),
            $container->get('view/twig/helpers/debug')->toArray(),
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
        $container->set('view', function (Container $container): ViewInterface {
            return new GenericView([
                'engine' => $container->get('view/engine')
            ]);
        });

        /**
         * A PSR-7 renderer, using the default view instance.
         *
         * @param Container $container A container instance.
         * @return Renderer
         */
        $container->set('view/renderer', function (Container $container): Renderer {
            return new Renderer([
                'view' => $container->get('view')
            ]);
        });
    }
}

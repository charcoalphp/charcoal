<?php

namespace Charcoal\Admin\ServiceProvider;

use Charcoal\Admin\AssetsConfig;
use Charcoal\Admin\Mustache\AssetsHelpers;
use Charcoal\Admin\Service\AssetsBuilder;
use DI\Container;
use Assetic\AssetManager;
use Psr\Container\ContainerInterface;

/**
 * Class AssetsManagerServiceProvider
 */
class AssetsManagerServiceProvider
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
    public function register(ContainerInterface $container)
    {
        $this->registerAssetsManager($container);
        $this->registerMustacheHelpersServices($container);
    }

    /**
     * @param Container $container The DI container.
     * @return void
     */
    protected function registerMustacheHelpersServices(ContainerInterface $container)
    {
        if (!($container->has('view/mustache/helpers'))) {
            $container->set('view/mustache/helpers', function () {
                return [];
            });
        }

        /**
         * Translation helpers for Mustache.
         *
         * @param Container $container DI Container.
         * @return AssetsHelpers
         */
        $container->set('view/mustache/helpers/assets-manager', function (Container $container) {
            return new AssetsHelpers([
                'assets' => $container->get('assets')
            ]);
        });

        /**
         * Extend global helpers for the Mustache Engine.
         *
         * @param  array     $helpers   The Mustache helper collection.
         * @param  Container $container A container instance.
         * @return array
         */
        $container->set('view/mustache/helpers', function (Container $container): array {
            $helpers = [];

            if ($container->has('view/mustache/helpers')) {
                $helpers = $container->get('view/mustache/helpers');
            }

            return array_merge(
                $helpers,
                $container->get('view/mustache/helpers/assets-manager')->toArray()
            );
        });
    }

    /**
     * Registers services for {@link https://selectize.github.io/selectize.js/ Selectize}.
     *
     * @param  Container $container The DI Container.
     * @return void
     */
    protected function registerAssetsManager(ContainerInterface $container)
    {
        $container->set('assets/config', function (Container $container) {
            $config = $container->get('view/config')->get('assets');

            return new AssetsConfig($config);
        });

        $container->set('assets/builder', function (Container $container) {
            $appConfig = $container->get('config');

            return new AssetsBuilder($appConfig['base_path']);
        });

        /**
         * @param Container $container DI Container.
         * @return AssetManager
         */
        $container->set('assets', function (Container $container) {
            $assetsBuilder = $container->get('assets/builder');
            $assetsConfig = $container->get('assets/config');

            return $assetsBuilder($assetsConfig);
        });
    }
}

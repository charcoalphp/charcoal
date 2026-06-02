<?php

namespace Charcoal\Admin\ServiceProvider;

use Charcoal\Admin\AssetsConfig;
use Charcoal\Admin\Mustache\AssetsHelpers;
use Charcoal\Admin\Service\AssetsBuilder;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Assetic\AssetManager;

/**
 * Class AssetsManagerServiceProvider
 */
class AssetsManagerServiceProvider implements ServiceProviderInterface
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
        $this->registerAssetsManager($container);
        $this->registerMustacheHelpersServices($container);
    }

    /**
     * @param Container $container The DI container.
     * @return void
     */
    protected function registerMustacheHelpersServices(Container $container)
    {
        if (!isset($container['view/mustache/helpers'])) {
            $container['view/mustache/helpers'] = (fn(): array => []);
        }

        /**
         * Translation helpers for Mustache.
         *
         * @param Container $container Pimple DI container.
         * @return AssetsHelpers
         */
        $container['view/mustache/helpers/assets-manager'] = (fn(Container $container): \Charcoal\Admin\Mustache\AssetsHelpers => new AssetsHelpers([
            'assets' => $container['assets']
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
            $container['view/mustache/helpers/assets-manager']->toArray()
        ));
    }

    /**
     * Registers services for {@link https://selectize.github.io/selectize.js/ Selectize}.
     *
     * @param  Container $container The Pimple DI Container.
     * @return void
     */
    protected function registerAssetsManager(Container $container)
    {
        $container['assets/config'] = function (Container $container): \Charcoal\Admin\AssetsConfig {
            $config = $container['view/config']->get('assets');

            return new AssetsConfig($config);
        };

        $container['assets/builder'] = function (Container $container): \Charcoal\Admin\Service\AssetsBuilder {
            $appConfig = $container['config'];

            return new AssetsBuilder($appConfig['base_path']);
        };

        /**
         * @param Container $container Pimple DI container.
         * @return AssetManager
         */
        $container['assets'] = function (Container $container) {
            $assetsBuilder = $container['assets/builder'];
            $assetsConfig = $container['assets/config'];

            return $assetsBuilder($assetsConfig);
        };
    }
}

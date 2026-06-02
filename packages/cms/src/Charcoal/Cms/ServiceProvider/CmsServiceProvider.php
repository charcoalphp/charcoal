<?php

namespace Charcoal\Cms\ServiceProvider;

// From Pimple
use Pimple\Container;
use Pimple\ServiceProviderInterface;
// From 'charcoal-core'
use Charcoal\Model\AbstractModel;
// From 'charcoal-factory'
use Charcoal\Factory\GenericFactory as Factory;
// From 'charcoal-cms'
use Charcoal\Cms\SectionInterface;
use Charcoal\Cms\Config;
use Charcoal\Cms\Config\CmsConfig;
use Charcoal\Cms\Service\Loader\EventLoader;
use Charcoal\Cms\Service\Loader\NewsLoader;
use Charcoal\Cms\Service\Loader\SectionLoader;
use Charcoal\Cms\Service\Manager\EventManager;
use Charcoal\Cms\Service\Manager\NewsManager;
use Charcoal\Cms\Support\Helpers\DateHelper;

/**
 * Cms Service Provider
 *
 * Provide the following service to container:
 *
 * - `cms/section/factory`
 */
class CmsServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param \Pimple\Container $container Pimple DI Container.
     */
    public function register(Container $container): void
    {
        $this->registerConfig($container);
        $this->reggisterDateHelper($container);
        $this->registerSectionServices($container);
        $this->registerNewsServices($container);
        $this->registerEventServices($container);
    }

    /**
     * @param Container $container Pimple DI Container.
     */
    private function registerConfig(Container $container): void
    {
        /**
         * @param Container $container Pimple DI Container.
         * @return CmsConfig Website configurations (from cms.json).
         */
        $container['cms/config'] = function (Container $container): \Charcoal\Cms\Config\CmsConfig {
            $appConfig = $container['config'];
            $cms = $appConfig->get('cms');

            $cmsConfig = new CmsConfig();
            $cmsConfig->addFile(__DIR__ . '/../../../../config/cms.json');
            $cmsConfig->setData($cms);

            $configType = $cmsConfig->get('config_obj');

            if ($configType) {
                $configId = $cmsConfig->get('config_obj_id') ?: 1;

                $model = $container['model/factory']->create($configType);
                $model->load($configId);

                if ((bool)$model->id()) {
                    $cmsConfig->addModel($model);
                }
            }

            return $cmsConfig;
        };
    }

    /**
     * @param Container $container Pimple DI Container.
     */
    private function reggisterDateHelper(Container $container): void
    {
        /**
         * @param Container $container Pimple DI Container.
         * @return DateHelper
         */
        $container['cms/date/helper'] = (fn(Container $container): \Charcoal\Cms\Support\Helpers\DateHelper => new DateHelper([
            'date_formats' => $container['cms/config']->get('date_formats'),
            'time_formats' => $container['cms/config']->get('time_formats'),
            'translator'   => $container['translator']
        ]));

        /**
         * @param Container $container Pimple DI Container.
         * @return DateHelper
         */
        $container['date/helper'] = function (Container $container) {
            trigger_error(sprintf(
                '%s is deprecated, use %s instead',
                '$container[\'date/helper\']',
                '$container[\'cms/date/helper\']'
            ));

            return $container['cms/date/helper'];
        };
    }

    /**
     * @param Container $container Pimple DI Container.
     */
    private function registerSectionServices(Container $container): void
    {
        /**
         * @param Container $container Pimple DI Container.
         * @return Factory
         */
        $container['cms/section/factory'] = (fn(Container $container): \Charcoal\Factory\GenericFactory => new Factory([
            'base_class'       => SectionInterface::class,
            'arguments'        => $container['model/factory']->arguments(),
            'resolver_options' => [
                'suffix' => 'Section'
            ]
        ]));

        /**
         * @param Container $container Pimple DI Container.
         * @return SectionLoader
         */
        $container['cms/section/loader'] = function (Container $container): \Charcoal\Cms\Service\Loader\SectionLoader {
            $sectionLoader = new SectionLoader([
                'loader'     => $container['model/collection/loader'],
                'factory'    => $container['model/factory'],
                'cache'      => $container['cache'],
                'translator' => $container['translator']
            ]);

            // Cms.json
            $sectionConfig = $container['cms/config']->sectionConfig();

            $sectionLoader->setObjType($sectionConfig->get('objType'));
            $sectionLoader->setBaseSection($sectionConfig->get('baseSection'));
            $sectionLoader->setSectionTypes($sectionConfig->get('sectionTypes'));

            return $sectionLoader;
        };
    }

    /**
     * @param Container $container Pimple DI Container.
     */
    private function registerNewsServices(Container $container): void
    {
        /**
         * @param Container $container Pimple DI Container.
         * @return NewsLoader
         */
        $container['cms/news/loader'] = function (Container $container): \Charcoal\Cms\Service\Loader\NewsLoader {
            $newsLoader = new NewsLoader([
                'loader'     => $container['model/collection/loader'],
                'factory'    => $container['model/factory'],
                'cache'      => $container['cache'],
                'translator' => $container['translator']
            ]);

            $newsConfig = $container['cms/config']->newsConfig();

            // Cms.json
            $objType = $newsConfig->get('obj_type');
            $newsLoader->setObjType($objType);

            return $newsLoader;
        };

        /**
         * @param Container $container
         * @return NewsManager
         */
        $container['cms/news/manager'] = (fn(Container $container): \Charcoal\Cms\Service\Manager\NewsManager => new NewsManager([
            'loader'      => $container['model/collection/loader'],
            'factory'     => $container['model/factory'],
            'news/loader' => $container['cms/news/loader'],
            'cache'       => $container['cache'],
            'cms/config'  => $container['cms/config'],
            'translator'  => $container['translator']
        ]));
    }

    /**
     * @param Container $container Pimple DI Container.
     */
    private function registerEventServices(Container $container): void
    {
        /**
         * @param Container $container Pimple DI Container.
         * @return EventLoader
         */
        $container['cms/event/loader'] = function (Container $container): \Charcoal\Cms\Service\Loader\EventLoader {
            $eventLoader = new EventLoader([
                'loader'     => $container['model/collection/loader'],
                'factory'    => $container['model/factory'],
                'cache'      => $container['cache'],
                'translator' => $container['translator']
            ]);

            $eventConfig = $container['cms/config']->eventConfig();

            // Cms.json
            $objType = $eventConfig->get('obj_type');
            $eventLoader->setObjType($objType);

            $lifespan = $eventConfig->get('lifespan');
            $eventLoader->setLifespan($lifespan);

            return $eventLoader;
        };

        /**
         * @param Container $container
         * @return EventManager
         */
        $container['cms/event/manager'] = (fn(Container $container): \Charcoal\Cms\Service\Manager\EventManager => new EventManager([
            'loader'       => $container['model/collection/loader'],
            'factory'      => $container['model/factory'],
            'event/loader' => $container['cms/event/loader'],
            'cache'        => $container['cache'],
            'cms/config'   => $container['cms/config'],
            'translator'   => $container['translator']
        ]));
    }
}

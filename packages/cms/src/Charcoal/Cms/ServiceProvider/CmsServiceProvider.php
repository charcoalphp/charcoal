<?php

namespace Charcoal\Cms\ServiceProvider;

use DI\Container;
// From 'charcoal-factory'
use Charcoal\Factory\GenericFactory as Factory;
// From 'charcoal-cms'
use Charcoal\Cms\SectionInterface;
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
class CmsServiceProvider
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $container DI Container.
     * @return void
     */
    public function register(Container $container)
    {
        $this->registerConfig($container);
        $this->reggisterDateHelper($container);
        $this->registerSectionServices($container);
        $this->registerNewsServices($container);
        $this->registerEventServices($container);
    }

    /**
     * @param Container $container DI Container.
     * @return void
     */
    private function registerConfig(Container $container)
    {
        /**
         * @param Container $container DI Container.
         * @return CmsConfig Website configurations (from cms.json).
         */
        $container->set('cms/config', function (Container $container) {
            $appConfig = $container->get('config');
            $cms = $appConfig->get('cms');

            $cmsConfig = new CmsConfig();
            $cmsConfig->addFile(__DIR__ . '/../../../../config/cms.json');
            $cmsConfig->setData($cms);

            $configType = $cmsConfig->get('config_obj');

            if ($configType) {
                $configId = $cmsConfig->get('config_obj_id') ?: 1;

                $model = $container->get('model/factory')->create($configType);
                $model->load($configId);

                if (!!$model->id()) {
                    $cmsConfig->addModel($model);
                }
            }

            return $cmsConfig;
        });
    }

    /**
     * @param Container $container DI Container.
     * @return void
     */
    private function reggisterDateHelper(Container $container)
    {
        /**
         * @param Container $container DI Container.
         * @return DateHelper
         */
        $container->set('cms/date/helper', function (Container $container) {
            return new DateHelper([
                'date_formats' => $container->get('cms/config')->get('date_formats'),
                'time_formats' => $container->get('cms/config')->get('time_formats'),
                'translator'   => $container->get('translator')
            ]);
        });

        /**
         * @param Container $container DI Container.
         * @return DateHelper
         */
        $container->set('date/helper', function (Container $container) {
            trigger_error(sprintf(
                '%s is deprecated, use %s instead',
                '$container[\'date/helper\']',
                '$container[\'cms/date/helper\']'
            ));

            return $container->get('cms/date/helper');
        });
    }

    /**
     * @param Container $container DI Container.
     * @return void
     */
    private function registerSectionServices(Container $container)
    {
        /**
         * @param Container $container DI Container.
         * @return Factory
         */
        $container->set('cms/section/factory', function (Container $container) {
            return new Factory([
                'base_class'       => SectionInterface::class,
                'arguments'        => $container->get('model/factory')->arguments(),
                'resolver_options' => [
                    'suffix' => 'Section'
                ]
            ]);
        });

        /**
         * @param Container $container DI Container.
         * @return SectionLoader
         */
        $container->set('cms/section/loader', function (Container $container) {
            $sectionLoader = new SectionLoader([
                'loader'     => $container->get('model/collection/loader'),
                'factory'    => $container->get('model/factory'),
                'cache'      => $container->get('cache'),
                'translator' => $container->get('translator')
            ]);

            // Cms.json
            $sectionConfig = $container->get('cms/config')->sectionConfig();

            $sectionLoader->setObjType($sectionConfig->get('objType'));
            $sectionLoader->setBaseSection($sectionConfig->get('baseSection'));
            $sectionLoader->setSectionTypes($sectionConfig->get('sectionTypes'));

            return $sectionLoader;
        });
    }

    /**
     * @param Container $container DI Container.
     * @return void
     */
    private function registerNewsServices(Container $container)
    {
        /**
         * @param Container $container DI Container.
         * @return NewsLoader
         */
        $container->set('cms/news/loader', function (Container $container) {
            $newsLoader = new NewsLoader([
                'loader'     => $container->get('model/collection/loader'),
                'factory'    => $container->get('model/factory'),
                'cache'      => $container->get('cache'),
                'translator' => $container->get('translator')
            ]);

            $newsConfig = $container->get('cms/config')->newsConfig();

            // Cms.json
            $objType = $newsConfig->get('obj_type');
            $newsLoader->setObjType($objType);

            return $newsLoader;
        });

        /**
         * @param Container $container
         * @return NewsManager
         */
        $container->set('cms/news/manager', function (Container $container) {

            $newsManager = new NewsManager([
                'loader'      => $container->get('model/collection/loader'),
                'factory'     => $container->get('model/factory'),
                'news/loader' => $container->get('cms/news/loader'),
                'cache'       => $container->get('cache'),
                'cms/config'  => $container->get('cms/config'),
                'translator'  => $container->get('translator')
            ]);

            return $newsManager;
        });
    }

    /**
     * @param Container $container DI Container.
     * @return void
     */
    private function registerEventServices(Container $container)
    {
        /**
         * @param Container $container DI Container.
         * @return EventLoader
         */
        $container->set('cms/event/loader', function (Container $container) {
            $eventLoader = new EventLoader([
                'loader'     => $container->get('model/collection/loader'),
                'factory'    => $container->get('model/factory'),
                'cache'      => $container->get('cache'),
                'translator' => $container->get('translator')
            ]);

            $eventConfig = $container->get('cms/config')->eventConfig();

            // Cms.json
            $objType = $eventConfig->get('obj_type');
            $eventLoader->setObjType($objType);

            $lifespan = $eventConfig->get('lifespan');
            $eventLoader->setLifespan($lifespan);

            return $eventLoader;
        });

        /**
         * @param Container $container
         * @return EventManager
         */
        $container->set('cms/event/manager', function (Container $container) {

            $eventManager = new EventManager([
                'loader'       => $container->get('model/collection/loader'),
                'factory'      => $container->get('model/factory'),
                'event/loader' => $container->get('cms/event/loader'),
                'cache'        => $container->get('cache'),
                'cms/config'   => $container->get('cms/config'),
                'translator'   => $container->get('translator')
            ]);

            return $eventManager;
        });
    }
}

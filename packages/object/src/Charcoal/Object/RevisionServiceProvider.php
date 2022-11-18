<?php

namespace Charcoal\Object;

use Pimple\Container;
use Pimple\Psr11\ServiceLocator;
use Pimple\ServiceProviderInterface;

/**
 * Revision Service Provider
 */
class RevisionServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['revisions/config'] = function (Container $container): RevisionsConfig {
            $configData = $container['config']->get('revisions');

            // If the config data is a boolean, it means we only want to affect the enabled state.
            if (is_bool($configData)) {
                $configData = [
                    'enabled' => $configData,
                ];
            }

            return new RevisionsConfig($configData);
        };

        $container['revisions/manager'] = function (Container $container): RevisionsManager {
            $services = new ServiceLocator($container, [
                'revisions/config',
                'model/factory',
                'logger'
            ]);

            return new RevisionsManager($services);
        };
    }
}

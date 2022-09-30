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
    public function register(Container $pimple)
    {
        $pimple['revisions/config'] = function (Container $pimple): RevisionsConfig {
            $configData = $pimple['config']->get('revisions');

            // If the config data is a boolean, it means we only want to affect the enabled state.
            if (is_bool($configData)) {
                $configData = [
                    'enabled' => $configData,
                ];
            }

            return new RevisionsConfig($configData);
        };

        $pimple['revisions/manager'] = function (Container $pimple): RevisionsManager {
            $services = new ServiceLocator($pimple, [
                'revisions/config',
                'model/factory',
                'logger'
            ]);

            return new RevisionsManager($services);
        };
    }
}

<?php

namespace Charcoal\Admin\Widget\Cms;

use DI\Container;
// From 'charcoal-admin'
use Charcoal\Admin\Widget\TableWidget;
use Psr\Container\ContainerInterface;

/**
 * The hierarchical table widget displays a collection in a tabular (table) format.
 */
class SectionTableWidget extends TableWidget
{
    use SectionTableTrait;

    /**
     * Inject dependencies from a DI Container.
     *
     * @param  Container $container A dependencies container instance.
     * @return void
     */
    protected function setDependencies(ContainerInterface $container)
    {
        parent::setDependencies($container);

        $this->setSectionFactory($container->get('cms/section/factory'));
    }
}

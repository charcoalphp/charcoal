<?php

namespace Charcoal\Admin\Template\System;

use DI\Container;
use Charcoal\Admin\AdminTemplate;
use Charcoal\Admin\Ui\CollectionContainerInterface;
use Charcoal\Admin\Ui\CollectionContainerTrait;
use Charcoal\Admin\Ui\DashboardContainerInterface;
use Charcoal\Admin\Ui\DashboardContainerTrait;
use Psr\Container\ContainerInterface;

/**
 *
 */
class UserRolesTemplate extends AdminTemplate implements
    CollectionContainerInterface,
    DashboardContainerInterface
{
    use CollectionContainerTrait;
    use DashboardContainerTrait;

    /**
     * Retrieve the list of parameters to extract from the HTTP request.
     *
     * @return string[]
     */
    protected function validDataFromRequest()
    {
        return array_merge([
            'obj_type'
        ], parent::validDataFromRequest());
    }

    /**
     * @return \Charcoal\Translator\Translation
     */
    public function title()
    {
        return $this->translator()->translation('Administrator Roles');
    }

    /**
     * @return mixed
     */
    public function createDashboardConfig()
    {
        return [
            'layout' => [
                'structure' => [
                    [ 'columns' => [ 0 ] ]
                ]
            ],
            'widgets' => [
                'list' => [
                    'type'     => 'charcoal/admin/widget/table',
                    'obj_type' => 'charcoal/admin/user/acl-role'
                ]
            ]
        ];
    }

    /**
     * @param Container $container DI Container.
     * @return void
     */
    protected function setDependencies(ContainerInterface $container)
    {
        parent::setDependencies($container);

        // Required collection dependencies
        $this->setModelFactory($container->get('model/factory'));
        $this->setCollectionLoader($container->get('model/collection/loader'));

        // Required dashboard dependencies.
        $this->setDashboardBuilder($container->get('dashboard/builder'));
    }
}

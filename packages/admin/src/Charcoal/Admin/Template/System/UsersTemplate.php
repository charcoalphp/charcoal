<?php

namespace Charcoal\Admin\Template\System;

// From Pimple
use Pimple\Container;
// From 'charcoal-admin'
use Charcoal\Admin\AdminTemplate;
use Charcoal\Admin\Ui\CollectionContainerInterface;
use Charcoal\Admin\Ui\CollectionContainerTrait;
use Charcoal\Admin\Ui\DashboardContainerInterface;
use Charcoal\Admin\Ui\DashboardContainerTrait;
use Charcoal\Admin\User;

/**
 * List Admin Users
 */
class UsersTemplate extends AdminTemplate implements
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
    #[\Override]
    protected function validDataFromRequest(): array
    {
        return array_merge([
            'obj_type'
        ], parent::validDataFromRequest());
    }

    /**
     * Retrieve the title of the page.
     *
     * @return \Charcoal\Translator\Translation|string|null
     */
    #[\Override]
    public function title(): ?\Charcoal\Translator\Translation
    {
        return $this->translator()->translation('Administrators');
    }

    public function createDashboardConfig(): array
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
                    'obj_type' => 'charcoal/admin/user'
                ]
            ]
        ];
    }

    /**
     * @param Container $container Pimple DI Container.
     * @return void
     */
    #[\Override]
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        // Required collection dependencies
        $this->setCollectionLoader($container['model/collection/loader']);

        // Required dashboard dependencies.
        $this->setDashboardBuilder($container['dashboard/builder']);
    }
}

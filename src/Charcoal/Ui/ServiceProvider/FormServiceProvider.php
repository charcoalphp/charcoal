<?php

namespace Charcoal\Ui\ServiceProvider;

use \Pimple\Container;
use \Pimple\ServiceProviderInterface;

use \Charcoal\Ui\Form\FormBuilder;
use \Charcoal\Ui\Form\FormFactory;
use \Charcoal\Ui\FormGroup\FormGroupBuilder;
use \Charcoal\Ui\FormGroup\FormGroupFactory;
use \Charcoal\Ui\FormInput\FormInputBuilder;
use \Charcoal\Ui\FormInput\FormInputFactory;

/**
 *
 */
class FormServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container A Pimple DI container.
     * @return void
     */
    public function register(Container $container)
    {
        $this->registerFormServices($container);
        $this->registerFormGroupServices($container);
        $this->registerFormInputServices($container);
    }

    /**
     * @param Container $container A Pimple DI container.
     * @return void
     */
    public function registerFormServices(Container $container)
    {
        /**
         * @param Container $container A Pimple DI container.
         * @return FormFactory
         */
        $container['form/factory'] = function(Container $container) {
            $formFactory = new FormFactory();
            $formFactory->setArguments([
                'logger'             => $container['logger'],
                'view'               => $container['view'],
                'layout_builder'     => $container['layout/builder'],
                'form_group_builder' => $container['form/group/builder']

            ]);
            return $formFactory;
        };

        /**
         * @param Container $container A Pimple DI container.
         * @return FormBuilder
         */
        $container['form/builder'] = function(Container $container) {
            $formFactory = $container['form/factory'];
            $formBuilder = new FormBuilder($formFactory);
            return $formBuilder;
        };
    }

    /**
     * @param Container $container A Pimple DI container.
     * @return void
     */
    public function registerFormGroupServices(Container $container)
    {
        /**
         * @param Container $container A Pimple DI container.
         * @return FormGroupFactory
         */
        $container['form/group/factory'] = function(Container $container) {
            $formGroupFactory = new FormGroupFactory();
            $formGroupFactory->setArguments([
                'logger'             => $container['logger'],
                'view'               => $container['view'],
                'layout_builder'     => $container['layout/builder'],
                'form_input_builder' => $container['form/input/builder']
            ]);
            return $formGroupFactory;
        };

        /**
         * @param Container $container A Pimple DI container.
         * @return FormGroupBuilder
         */
        $container['form/group/builder'] = function(Container $container) {
            $formGroupFactory = $container['form/group/factory'];
            $formGroupBuilder = new FormGroupBuilder($formGroupFactory, $container);
            return $formGroupBuilder;
        };
    }

    /**
     * @param Container $container A Pimple DI container.
     * @return void
     */
    public function registerFormInputServices(Container $container)
    {
        /**
         * @param Container $container A Pimple DI container.
         * @return FormInputFactory
         */
        $container['form/input/factory'] = function(Container $container) {
            $formFactory = new FormInputFactory();
            return $formFactory;
        };

        /**
         * @param Container $container A Pimple DI container.
         * @return FormInputBuilder
         */
        $container['form/input/builder'] = function(Container $container) {
            $formInputFactory = $container['form/input/factory'];
            $formInputBuilder = new FormInputBuilder($formInputFactory, $container);
            return $formInputBuilder;
        };
    }
}

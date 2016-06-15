<?php

namespace Charcoal\Ui\ServiceProvider;

use \Pimple\Container;
use \Pimple\ServiceProviderInterface;

use \Charcoal\Factory\GenericFactory as Factory;

use \Charcoal\Ui\Form\FormBuilder;
use \Charcoal\Ui\Form\FormInterface;
use \Charcoal\Ui\Form\GenericForm;
use \Charcoal\Ui\FormGroup\FormGroupBuilder;
use \Charcoal\Ui\FormGroup\FormGroupInterface;
use \Charcoal\Ui\FormGroup\GenericFormGroup;
use \Charcoal\Ui\FormInput\FormInputBuilder;
use \Charcoal\Ui\FormInput\FormInputInterface;
use \Charcoal\Ui\FormInput\GenericFormInput;

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
            return new Factory([
                'base_class' => FormInterface::class,
                'default_class' => GenericForm::class,
                'arguments' => [[
                    'logger'             => $container['logger'],
                    'view'               => $container['view'],
                    'layout_builder'     => $container['layout/builder'],
                    'form_group_builder' => $container['form/group/builder']

                ]]
            ]);
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
            return new Factory([
                'base_class' => FormGroupInterface::class,
                'default_class' => GenericFormGroup::class,
                'arguments' => [[
                    'logger'             => $container['logger'],
                    'view'               => $container['view'],
                    'layout_builder'     => $container['layout/builder'],
                    'form_input_builder' => $container['form/input/builder']
                ]]
            ]);
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
            return new Factory([
                'base_class' => FormInputInterface::class,
                'default_class' => GenericFormInput::class,
                'resolver_options' => [
                    'suffix' => 'FormInput'
                ]
            ]);
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

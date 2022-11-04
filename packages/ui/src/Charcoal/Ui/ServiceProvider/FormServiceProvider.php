<?php

namespace Charcoal\Ui\ServiceProvider;

// From Pimple
use Pimple\Container;
use Pimple\ServiceProviderInterface;
// From 'charcoal-factory'
use Charcoal\Factory\GenericFactory as Factory;
// From 'charcoal-ui'
use Charcoal\Ui\Form\FormBuilder;
use Charcoal\Ui\Form\FormInterface;
use Charcoal\Ui\Form\GenericForm;
use Charcoal\Ui\FormGroup\FormGroupBuilder;
use Charcoal\Ui\FormGroup\FormGroupInterface;
use Charcoal\Ui\FormGroup\GenericFormGroup;
use Charcoal\Ui\FormInput\FormInputBuilder;
use Charcoal\Ui\FormInput\FormInputInterface;
use Charcoal\Ui\FormInput\GenericFormInput;

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
         * @return \Charcoal\Factory\FactoryInterface
         */
        $container['form/factory'] = function (Container $container) {
            return new Factory([
                'base_class'    => FormInterface::class,
                'default_class' => GenericForm::class,
                'arguments'     => [
                    [
                        'container'          => $container,
                        'logger'             => $container['logger'],
                        'view'               => $container['view'],
                        'layout_builder'     => $container['layout/builder'],
                        'form_group_factory' => $container['form/group/factory'],
                    ],
                ],
            ]);
        };

        /**
         * @param Container $container A Pimple DI container.
         * @return FormBuilder
         */
        $container['form/builder'] = function (Container $container) {
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
         * @return \Charcoal\Factory\FactoryInterface
         */
        $container['form/group/factory'] = function (Container $container) {
            return new Factory([
                'base_class'    => FormGroupInterface::class,
                'default_class' => GenericFormGroup::class,
                'arguments'     => [
                    [
                        'container'          => $container,
                        'logger'             => $container['logger'],
                        'view'               => $container['view'],
                        'layout_builder'     => $container['layout/builder'],
                        'form_input_builder' => $container['form/input/builder'],
                    ],
                ],
                'resolver_options' => [
                    'suffix' => 'FormGroup',
                ],
            ]);
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
         * @return \Charcoal\Factory\FactoryInterface
         */
        $container['form/input/factory'] = function () {
            return new Factory([
                'base_class'       => FormInputInterface::class,
                'default_class'    => GenericFormInput::class,
                'resolver_options' => [
                    'suffix' => 'FormInput',
                ],
            ]);
        };

        /**
         * @param Container $container A Pimple DI container.
         * @return FormInputBuilder
         */
        $container['form/input/builder'] = function (Container $container) {
            $formInputFactory = $container['form/input/factory'];
            $formInputBuilder = new FormInputBuilder($formInputFactory, $container);
            return $formInputBuilder;
        };
    }
}

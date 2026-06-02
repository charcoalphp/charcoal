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
     */
    public function register(Container $container): void
    {
        $this->registerFormServices($container);
        $this->registerFormGroupServices($container);
        $this->registerFormInputServices($container);
    }

    /**
     * @param Container $container A Pimple DI container.
     */
    public function registerFormServices(Container $container): void
    {
        /**
         * @param Container $container A Pimple DI container.
         * @return \Charcoal\Factory\FactoryInterface
         */
        $container['form/factory'] = (fn(Container $container): \Charcoal\Factory\GenericFactory => new Factory([
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
        ]));

        /**
         * @param Container $container A Pimple DI container.
         * @return FormBuilder
         */
        $container['form/builder'] = function (Container $container): \Charcoal\Ui\Form\FormBuilder {
            $formFactory = $container['form/factory'];
            return new FormBuilder($formFactory);
        };
    }

    /**
     * @param Container $container A Pimple DI container.
     */
    public function registerFormGroupServices(Container $container): void
    {
        /**
         * @param Container $container A Pimple DI container.
         * @return \Charcoal\Factory\FactoryInterface
         */
        $container['form/group/factory'] = (fn(Container $container): \Charcoal\Factory\GenericFactory => new Factory([
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
        ]));
    }

    /**
     * @param Container $container A Pimple DI container.
     */
    public function registerFormInputServices(Container $container): void
    {
        /**
         * @param Container $container A Pimple DI container.
         * @return \Charcoal\Factory\FactoryInterface
         */
        $container['form/input/factory'] = (fn(): \Charcoal\Factory\GenericFactory => new Factory([
            'base_class'       => FormInputInterface::class,
            'default_class'    => GenericFormInput::class,
            'resolver_options' => [
                'suffix' => 'FormInput',
            ],
        ]));

        /**
         * @param Container $container A Pimple DI container.
         * @return FormInputBuilder
         */
        $container['form/input/builder'] = function (Container $container): \Charcoal\Ui\FormInput\FormInputBuilder {
            $formInputFactory = $container['form/input/factory'];
            return new FormInputBuilder($formInputFactory, $container);
        };
    }
}

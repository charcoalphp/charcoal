<?php

namespace Charcoal\Ui\ServiceProvider;

use DI\Container;
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
use Psr\Container\ContainerInterface;

/**
 *
 */
class FormServiceProvider
{
    /**
     * @param Container $container A DI Container.
     * @return void
     */
    public function register(ContainerInterface $container)
    {
        $this->registerFormServices($container);
        $this->registerFormGroupServices($container);
        $this->registerFormInputServices($container);
    }

    /**
     * @param Container $container A DI Container.
     * @return void
     */
    public function registerFormServices(ContainerInterface $container)
    {
        /**
         * @param Container $container A DI Container.
         * @return \Charcoal\Factory\FactoryInterface
         */
        $container->set('form/factory', function (Container $container) {
            return new Factory([
                'base_class'    => FormInterface::class,
                'default_class' => GenericForm::class,
                'arguments'     => [
                    [
                        'container'          => $container,
                        'logger'             => $container->get('logger'),
                        'view'               => $container->get('view'),
                        'layout_builder'     => $container->get('layout/builder'),
                        'form_group_factory' => $container->get('form/group/factory'),
                    ],
                ],
            ]);
        });

        /**
         * @param Container $container A DI Container.
         * @return FormBuilder
         */
        $container->set('form/builder', function (Container $container) {
            $formFactory = $container->get('form/factory');
            $formBuilder = new FormBuilder($formFactory);
            return $formBuilder;
        });
    }

    /**
     * @param Container $container A DI Container.
     * @return void
     */
    public function registerFormGroupServices(ContainerInterface $container)
    {
        /**
         * @param Container $container A DI Container.
         * @return \Charcoal\Factory\FactoryInterface
         */
        $container->set('form/group/factory', function (Container $container) {
            return new Factory([
                'base_class'    => FormGroupInterface::class,
                'default_class' => GenericFormGroup::class,
                'arguments'     => [
                    [
                        'container'          => $container,
                        'logger'             => $container->get('logger'),
                        'view'               => $container->get('view'),
                        'layout_builder'     => $container->get('layout/builder'),
                        'form_input_builder' => $container->get('form/input/builder'),
                    ],
                ],
                'resolver_options' => [
                    'suffix' => 'FormGroup',
                ],
            ]);
        });
    }

    /**
     * @param Container $container A DI Container.
     * @return void
     */
    public function registerFormInputServices(ContainerInterface $container)
    {
        /**
         * @param Container $container A DI Container.
         * @return \Charcoal\Factory\FactoryInterface
         */
        $container->set('form/input/factory', function () {
            return new Factory([
                'base_class'       => FormInputInterface::class,
                'default_class'    => GenericFormInput::class,
                'resolver_options' => [
                    'suffix' => 'FormInput',
                ],
            ]);
        });

        /**
         * @param Container $container A DI Container.
         * @return FormInputBuilder
         */
        $container->set('form/input/builder', function (Container $container) {
            $formInputFactory = $container->get('form/input/factory');
            $formInputBuilder = new FormInputBuilder($formInputFactory, $container);
            return $formInputBuilder;
        });
    }
}

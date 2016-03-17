<?php

namespace Charcoal\Ui\FormGroup;

use \Pimple\Container;

use \Charcoal\Factory\FactoryInterface;

/**
 *
 */
class FormGroupBuilder
{
    const DEFAULT_TYPE = 'charcoal/ui/form-group/generic';

    /**
     * @var FactoryInterface $factory
     */
    protected $factory;

    /**
     * A Pimple dependency-injection container
     * @var Container $container
     */
    protected $container;

    /**
     * @param FactoryInterface $factory   An object factory.
     * @param Container        $container The DI container.
     */
    public function __construct(FactoryInterface $factory, Container $container)
    {
        $this->factory = $factory;
        $this->container = $container;
    }

    /**
     * @param array|\ArrayAccess $options The form group build options / config.
     * @return FormGroupInterface
     */
    public function build($options)
    {
        $container = $this->container;
        $objType = isset($options['type']) ? $options['type'] : self::DEFAULT_TYPE;
        $obj = $this->factory->create($objType, [
            'form'      => $options['form'],
            'logger'    => $container['logger'],
            'view'      => $container['view'],
            'layout_builder'     => $container['layout/builder'],
            'form_input_builder' => $container['form/input/builder']
        ]);
        $obj->setDependencies($container);
        $obj->setData($options);
        return $obj;
    }
}

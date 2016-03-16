<?php

namespace Charcoal\Ui\FormInput;

use \Pimple\Container;

use \Charcoal\Factory\FactoryInterface;

/**
 *
 */
class FormInputBuilder
{
    const DEFAULT_TYPE = 'charcoal/ui/form-input/generic';

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
     * @return FormInputInterface
     */
    public function build($options)
    {
        $container = $this->container;
        $objType = isset($options['type']) ? $options['type'] : self::DEFAULT_TYPE;

        $obj = $this->factory->create($objType, [
            'logger'    =>  $container['logger'],
            'view'      =>  $container['view']
        ]);
        $obj->setData($options);
        return $obj;
    }
}

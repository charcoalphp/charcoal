<?php

namespace Charcoal\Ui\Form;

use \Pimple\Container;

use \Charcoal\Factory\FactoryInterface;

/**
 *
 */
class FormBuilder
{
    const DEFAULT_TYPE = 'charcoal/ui/form/generic';

    /**
     * @var FactoryInterface $factory
     */
    protected $factory;

    /**
     * A Pimple dependency-injection container to fulfill the required services.
     * @var Container $container
     */
    protected $container;

    /**
     * @param FactoryInterface $factory An object factory.
     */
    public function __construct(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @param array|\ArrayAccess $options The form group build options / config.
     * @return FormGroupInterface
     */
    public function build($options)
    {
        $objType = isset($options['type']) ? $options['type'] : self::DEFAULT_TYPE;

        $obj = $this->factory->create($objType);
        $obj->setData($options);
        return $obj;
    }
}

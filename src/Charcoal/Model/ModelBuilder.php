<?php

namespace Charcoal\Model;

// Dependencies from `Pimple`
use \Pimple\Container;

// Module
use \Charcoal\Factory\FactoryInterface;

/**
 * Model Builder.
 */
class ModelBuilder
{
    const DEFAULT_TYPE = 'charcoal/model/model';

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
     * @return ModelGroupInterface
     */
    public function build($options)
    {
        $objType = isset($options['type']) ? $options['type'] : self::DEFAULT_TYPE;

        $obj = $this->factory->create($objType);
        $obj->setData($options);
        return $obj;
    }
}

<?php

namespace Charcoal\Model;

use RuntimeException;
// from `charcoal-factory`
use Charcoal\Factory\FactoryInterface;

/**
 * Provides model loader builder features.
 *
 * Trait ModelLoaderAwareTrait
 * @package Charcoal\Model
 */
trait ModelFactoryTrait
{
    /**
     * Store the factory instance.
     *
     * @var FactoryInterface
     */
    protected $modelFactory;

    /**
     * Set an model factory.
     *
     * @param  FactoryInterface $factory The factory to create models.
     * @return void
     */
    protected function setModelFactory(FactoryInterface $factory)
    {
        $this->modelFactory = $factory;
    }

    /**
     * Retrieve the model factory.
     *
     * @throws RuntimeException If the model factory is missing.
     * @return FactoryInterface
     */
    public function modelFactory()
    {
        if (!isset($this->modelFactory)) {
            throw new RuntimeException(sprintf(
                'Model Factory is not defined for [%s]',
                get_class($this)
            ));
        }

        return $this->modelFactory;
    }
}

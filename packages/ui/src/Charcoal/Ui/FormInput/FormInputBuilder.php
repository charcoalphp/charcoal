<?php

namespace Charcoal\Ui\FormInput;

use Pimple\Container;
// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

/**
 * Form Input Builder
 */
class FormInputBuilder
{
    /**
     * The default, concrete, form input model.
     *
     * @const string
     */
    public const DEFAULT_TYPE = 'charcoal/ui/form-input/generic';

    /**
     * Return a new form input builder.
     *
     * @param FactoryInterface $factory   A dashboard factory.
     * @param Container        $container The DI container.
     */
    public function __construct(protected \Charcoal\Factory\FactoryInterface $factory, protected \Pimple\Container $container)
    {
    }

    /**
     * Build and return a new form input.
     *
     * @param  array|\ArrayAccess $options The form input build options.
     * @return FormInputInterface
     */
    public function build($options)
    {
        $container = $this->container;
        $objType = ($options['type'] ?? self::DEFAULT_TYPE);

        $obj = $this->factory->create($objType, [
            'logger' => $container['logger'],
            'view'   => $container['view']
        ]);
        $obj->setData($options);

        return $obj;
    }
}

<?php

namespace Charcoal\Ui\Form;

// From Pimple
use Pimple\Container;
// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

/**
 * Form Builder
 */
class FormBuilder
{
    /**
     * The default, concrete, form model.
     *
     * @const string
     */
    public const DEFAULT_TYPE = 'charcoal/ui/form/generic';

    /**
     * Store the form factory instance.
     *
     * @var FactoryInterface
     */
    protected $factory;

    /**
     * Store the dependency-injection container to fulfill the required services.
     *
     * @var Container $container
     */
    protected $container;

    /**
     * Return a new form builder.
     *
     * @param FactoryInterface $factory An form factory.
     */
    public function __construct(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Build and return a new form.
     *
     * @param  array|\ArrayAccess $options The form build options.
     * @return DashboardInterface
     */
    public function build($options)
    {
        $objType = isset($options['type']) ? $options['type'] : self::DEFAULT_TYPE;

        $obj = $this->factory->create($objType);
        $obj->setData($options);

        return $obj;
    }
}

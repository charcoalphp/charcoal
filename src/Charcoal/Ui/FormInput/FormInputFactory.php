<?php

namespace Charcoal\Ui\FormInput;

// Module `charcoal-factory` dependencies
use \Charcoal\Factory\ResolverFactory;

/**
 * Form Input Factory, to create Form Input objects.
 */
class FormInputFactory extends ResolverFactory
{
    /**
     * @return string
     */
    public function baseClass()
    {
        return '\Charcoal\Ui\FormInput\FormInputInterface';
    }

    /**
     * @return string
     */
    public function defaultClass()
    {
        return '\Charcoal\Ui\FormInput\GenericFormInput';
    }

    /**
     * @return string
     */
    public function resolverSuffix()
    {
        return 'FormInput';
    }
}

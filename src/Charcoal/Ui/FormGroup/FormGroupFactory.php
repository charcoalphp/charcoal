<?php

namespace Charcoal\Ui\FormGroup;

// Module `charcoal-factory` dependencies
use \Charcoal\Factory\ResolverFactory;

/**
 * Form Group Factory, to create Form Group objects.
 */
class FormGroupFactory extends ResolverFactory
{
    /**
     * @return string
     */
    public function baseClass()
    {
        return '\Charcoal\Ui\FormGroup\FormGroupInterface';
    }

    /**
     * @return string
     */
    public function defaultClass()
    {
        return '\Charcoal\Ui\FormGroup\GenericFormGroup';
    }

    /**
     * @return string
     */
    public function resolverSuffix()
    {
        return '';
    }
}

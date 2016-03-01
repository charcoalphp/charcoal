<?php

namespace Charcoal\Ui\Form;

use \Charcoal\Factory\ResolverFactory;

/**
 * Form Factory.
 */
class FormFactory extends ResolverFactory
{
    /**
     * @return string
     */
    public function baseClass()
    {
        return '\Charcoal\Ui\Form\FormInterface';
    }

    /**
     * @return string
     */
    public function defaultClass()
    {
        return '\Charcoal\Ui\Form\GenericForm';
    }

    /**
     * @return string
     */
    public function resolverSuffix()
    {
        return 'Form';
    }
}

<?php

namespace Charcoal\Admin\Action\Selectize;

use DI\Container;
// From 'charcoal-admin'
use Charcoal\Admin\Action\Object\SaveAction as BaseSaveAction;
use Psr\Container\ContainerInterface;

/**
 * Selectize Save Action
 */
class SaveAction extends BaseSaveAction
{
    use SelectizeRendererAwareTrait;

    /**
     * Retrieve the list of parameters to extract from the HTTP request.
     *
     * @return string[]
     */
    protected function validDataFromRequest()
    {
        return array_merge([
            'selectize_obj_type', 'selectize_prop_ident', 'selectize_property'
        ], parent::validDataFromRequest());
    }

    /**
     * @return array
     */
    public function results()
    {
        $results = parent::results();

        if ($this->success() === true) {
            $results['selectize'] = $this->selectizeVal($this->obj()->id());
        }

        return $results;
    }

    /**
     * Dependencies
     * @param Container $container DI Container.
     * @return void
     */
    protected function setDependencies(ContainerInterface $container)
    {
        parent::setDependencies($container);

        $this->setSelectizeRenderer($container->get('selectize/renderer'));
        $this->setPropertyInputFactory($container->get('property/input/factory'));
        $this->setPropertyFactory($container->get('property/factory'));
    }
}

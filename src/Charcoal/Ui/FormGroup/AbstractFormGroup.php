<?php

namespace Charcoal\Ui\FormGroup;

// Intra-module (`charcoal-ui`) dependencies
use \Charcoal\Ui\AbstractUiItem;
use \Charcoal\Ui\FormGroup\FormGroupInterface;
use \Charcoal\Ui\FormGroup\FormGroupTrait;
use \Charcoal\Ui\Layout\LayoutAwareInterface;
use \Charcoal\Ui\Layout\LayoutAwareTrait;

/**
 * Default implementation of the FormGroupInterface, as an abstract class.
 */
abstract class AbstractFormGroup extends AbstractUiItem implements
    FormGroupInterface,
    LayoutAwareInterface
{
    use LayoutAwareTrait;
    use FormGroupTrait;

    /**
     * @param array|ArrayAccess $data The class depdendencies.
     */
    public function __construct($data)
    {
        if (isset($data['form'])) {
            $this->setForm($data['form']);
        }

        $this->setFormInputBuilder($data['form_input_builder']);

        // Set up layout builder (to fulfill LayoutAware Interface)
        $this->setLayoutBuilder($data['layout_builder']);
    }
}

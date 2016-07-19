<?php

namespace Charcoal\Ui\Form;

// Intra-module (`charcoal-ui`) dependencies
use \Charcoal\Ui\AbstractUiItem;
use \Charcoal\Ui\Form\FormInterface;
use \Charcoal\Ui\Layout\LayoutAwareInterface;
use \Charcoal\Ui\Layout\LayoutAwareTrait;
use \Charcoal\Ui\UiItemInterface;

/**
 *
 */
abstract class AbstractForm extends AbstractUiItem implements
    FormInterface,
    LayoutAwareInterface,
    UiItemInterface
{
    use LayoutAwareTrait;
    use FormTrait;

    /**
     * @param array|ArrayAccess $data The class dependencies.
     */
    public function __construct($data = null)
    {
        // Set up form group factory (to fulfill FormInterface)
        $this->setFormGroupFactory($data['form_group_factory']);

        // Set up layout builder (to fulfill LayoutAwareInterface)
        $this->setLayoutBuilder($data['layout_builder']);
    }
}

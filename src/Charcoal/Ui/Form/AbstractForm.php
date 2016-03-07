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
        $this->setFormGroupBuilder($data['form_group_builder']);

        // Set up layout builder (to fulfill LayoutAware Interface)
        $this->setLayoutBuilder($data['layout_builder']);
    }
}

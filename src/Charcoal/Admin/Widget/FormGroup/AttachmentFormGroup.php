<?php

namespace Charcoal\Admin\Widget\FormGroup;

// From 'charcoal-ui'
use \Charcoal\Ui\FormGroup\FormGroupInterface;
use \Charcoal\Ui\FormGroup\FormGroupTrait;
use \Charcoal\Ui\Layout\LayoutAwareInterface;
use \Charcoal\Ui\Layout\LayoutAwareTrait;
use \Charcoal\Ui\UiItemInterface;
use \Charcoal\Ui\UiItemTrait;

use \Charcoal\Admin\Widget\AttachmentWidget;

/**
 * Attachment widget, as form group.
 */
class AttachmentFormGroup extends AttachmentWidget implements
    FormGroupInterface,
    UiItemInterface
{
    use FormGroupTrait;
    use UiItemTrait;
}

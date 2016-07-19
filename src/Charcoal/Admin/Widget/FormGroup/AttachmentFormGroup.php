<?php

namespace Charcoal\Admin\Widget\FormGroup;

use \Charcoal\Ui\FormGroup\FormGroupInterface;
use \Charcoal\Ui\FormGroup\FormGroupTrait;

use \Charcoal\Admin\Widget\AttachmentWidget;

/**
 * Attachment widget, as form group.
 */
class AttachmentFormGroup extends AttachmentWidget implements FormGroupInterface
{
    use FormGroupTrait;
}

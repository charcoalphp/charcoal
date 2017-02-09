<?php

namespace Charcoal\Attachment\Object;

// From 'beneroch/charcoal-attachments'
use Charcoal\Attachment\Traits\AttachmentAwareTrait;
use Charcoal\Attachment\Interfaces\AttachmentAwareInterface;

use Charcoal\Attachment\Traits\AttachmentContainerTrait;
use Charcoal\Attachment\Interfaces\AttachmentContainerInterface;

/**
 * Gallery Attachment Type
 *
 * This type allows for nesting of additional attachment types.
 */
class Container extends Attachment implements
    AttachmentAwareInterface,
    AttachmentContainerInterface
{
    use AttachmentAwareTrait;
    use AttachmentContainerTrait;
}

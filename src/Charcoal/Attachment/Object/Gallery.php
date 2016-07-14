<?php

namespace Charcoal\Attachment\Object;

use \Charcoal\Attachment\Traits\AttachmentAwareTrait;
use \Charcoal\Attachment\Interfaces\AttachmentAwareInterface;

/**
 * Gallery Attachment Type
 *
 * This type allows for nesting of additional attachment types.
 */
class Gallery extends Attachment implements
    AttachmentAwareInterface
{
    use AttachmentAwareTrait;
}

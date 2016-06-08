<?php

namespace Charcoal\Attachment\Interfaces;

/**
 * Object can have attachment.
 */
interface AttachmentAwareInterface
{
    /**
     * Obj type and objId.
     * @return string   Current objType.
     * @return mixed    Current ID.
     */
    public function objType();
    public function id();

}

<?php

namespace Charcoal\Attachment\Interfaces;

/**
 * Defines a object that can have attachments.
 */
interface AttachmentAwareInterface
{
    /**
     * Retrieve the object's type identifier.
     *
     * @return string
     */
    public function objType();

    /**
     * Retrieve the object's unique ID.
     *
     * @return mixed
     */
    public function id();
}

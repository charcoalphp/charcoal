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
    public static function objType();

    /**
     * Retrieve the object's unique ID.
     *
     * @return mixed
     */
    public function id();

    /**
     * Attach a node to the current object.
     *
     * @param  AttachableInterface|ModelInterface $attachment An attachment or object.
     * @param  string                             $group      Attachment group, defaults to contents.
     * @return boolean|self
     */
    public function addAttachment($attachment, $group = 'contents');

    /**
     * Retrieve the object's available attachment object types.
     *
     * @return array
     */
    public function attachmentObjTypes();
}

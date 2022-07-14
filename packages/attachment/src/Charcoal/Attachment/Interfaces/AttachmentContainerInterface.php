<?php

namespace Charcoal\Attachment\Interfaces;

/**
 * Defines a object that can have attachments.
 */
interface AttachmentContainerInterface
{
    /**
     * The default grouping for contained attachments.
     *
     * @var string
     */
    public const DEFAULT_GROUPING = 'generic';

    /**
     * Retrieve the attachments configuration from this object's metadata.
     *
     * @return array
     */
    public function attachmentsMetadata();

    /**
     * Returns attachable objects
     *
     * @return array Attachable Objects
     */
    public function attachableObjects();

    /**
     * Determine if this attachment is a container.
     *
     * @return boolean
     */
    public function isAttachmentContainer();
}

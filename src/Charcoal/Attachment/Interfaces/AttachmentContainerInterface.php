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
    const DEFAULT_GROUPING = 'generic';

    /**
     * Gets the attachments config from
     * the object metadata.
     *
     * @return [type] [description]
     */
    public function attachmentConfig();

    /**
     * Returns attachable objects
     *
     * @return array Attachable Objects
     */
    public function attachableObjects();

    /**
     * Returns true
     * @return boolean True.
     */
    public function isAttachmentContainer();

}

<?php

namespace Charcoal\Attachment\Interfaces;

// From 'charcoal-attachment'
use Charcoal\Attachment\Object\Join;

/**
 * Defines an attachment aware of their relationship.
 */
interface AttachmentRelationInterface
{
    /**
     * Retrieve the attachment/object relationship.
     *
     * @return Join|null
     */
    public function pivot();

    /**
     * Set the attachment/object relationship.
     *
     * @param  mixed $pivot The relationship object ID or instance.
     * @return AttachmentRelationInterface
     */
    public function setPivot($pivot);
}

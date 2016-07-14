<?php

namespace Charcoal\Attachment\Object;

/**
 * Image Attachment Type
 *
 * Uses a file input, title, and description.
 */
class Image extends File
{
    /**
     * Generate a thumbnail from the uploaded image.
     *
     * @todo    Generate thumbnail from the main image (or not.).
     * @used-by StorableTrait::preSave() For the "create" Event.
     * @used-by StorableTrait::preUpdate() For the "update" Event.
     * @return  boolean
     */
    public function generateThumbnail()
    {
        return true;
    }
}

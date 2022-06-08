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
     * Alias of {@see Attachment::thumbnail()} and {@see Attachment::file()}.
     *
     * @return string|null
     */
    public function src()
    {
        $src = $this->thumbnail();

        if (!$src) {
            $src = $this->file();
        }

        return $this->createAbsoluteUrl($src);
    }

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

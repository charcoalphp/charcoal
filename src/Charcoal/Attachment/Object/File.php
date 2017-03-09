<?php

namespace Charcoal\Attachment\Object;

/**
 * File Attachment Type
 *
 * For example: PDF, DOC, DOCX, etc. The file node is displayed differently
 * than the Image node, but is still basicly the same.
 */
class File extends Attachment
{
    /**
     * Alias of {@see Attachment::file()}.
     *
     * @return string|null
     */
    public function src()
    {
        return $this->createAbsoluteUrl($this->file());
    }

    /**
     * Generate a thumbnail from the MIME type of the uploaded file.
     *
     * @todo    Generate thumbnail from config or whatever. File nodes should have a placeholder defined.
     * @used-by StorableTrait::preSave() For the "create" Event.
     * @used-by StorableTrait::preUpdate() For the "update" Event.
     * @return  boolean
     */
    public function generateThumbnail()
    {
        return true;
    }



// Events
// =============================================================================

    /**
     * Event called before _creating_ the object.
     *
     * @see    Charcoal\Source\StorableTrait::preSave() For the "create" Event.
     * @return boolean
     */
    public function preSave()
    {
        $this->generateThumbnail();

        return parent::preSave();
    }

    /**
     * Event called before _updating_ the object.
     *
     * @see    StorableTrait::preUpdate() For the "update" Event.
     * @param  array $properties Optional. The list of properties to update.
     * @return boolean
     */
    public function preUpdate(array $properties = null)
    {
        $this->generateThumbnail();

        return parent::preUpdate($properties);
    }
}

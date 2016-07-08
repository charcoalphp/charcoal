<?php
namespace Charcoal\Attachment\Object;

// From Charcoal\Attachment
use \Charcoal\Attachment\Object\Attachment;

/**
 * Text attachment
 * Uses the title, subtitle and description content
 * of the extended Attachment object.
 * Its all about the metadata.
 *
 */
class Text extends Attachment
{
    /**
     * From bootstrap. Glyphicon used to identify the attachment type.
     * @return string Glypicon.
     */
    public function glyphicon()
    {
        return 'glyphicon-font';
    }
}

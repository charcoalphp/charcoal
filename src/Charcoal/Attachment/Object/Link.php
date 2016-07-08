<?php
namespace Charcoal\Attachment\Object;

// From Charcoal\Attachment
use \Charcoal\Attachment\Object\Attachment;

/**
 * Link attachment
 * Uses the title and link
 * This is mainly to add with "ressources" table.
 */
class Link extends Attachment
{
    /**
     * From bootstrap. Glyphicon used to identify the attachment type.
     * @return string Glypicon.
     */
    public function glyphicon()
    {
        return 'glyphicon-file';
    }
}

<?php
namespace Charcoal\Attachment\Object;

// From Charcoal\Attachment
use \Charcoal\Attachment\Object\Attachment;

use \Charcoal\Attachment\Traits\AttachmentAwareTrait;
use \Charcoal\Attachment\Interfaces\AttachmentAwareInterface;

/**
 * Video Attachment
 * A video attachment is basicly just either an
 * URL or an embed as provided by the provider (obviously)
 * such as youtube, vimeo, etc.
 * It's all about the metadata.
 */
class Gallery extends Attachment implements
    AttachmentAwareInterface
{
    use AttachmentAwareTrait;

    /**
     * From bootstrap. Glyphicon used to identify the attachment type.
     * @return string Glypicon.
     */
    public function glyphicon()
    {
        return 'glyphicon-duplicate';
    }
}

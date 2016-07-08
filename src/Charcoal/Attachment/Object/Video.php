<?php
namespace Charcoal\Attachment\Object;

// From Charcoal\Attachment
use \Charcoal\Attachment\Object\Attachment;

/**
 * Video Attachment
 * A video attachment is basicly just either an
 * URL or an embed as provided by the provider (obviously)
 * such as youtube, vimeo, etc.
 * It's all about the metadata.
 */
class Video extends Attachment
{
    /**
     * From bootstrap. Glyphicon used to identify the attachment type.
     * @return string Glypicon.
     */
    public function glyphicon()
    {
        return 'glyphicon-facetime-video';
    }
}

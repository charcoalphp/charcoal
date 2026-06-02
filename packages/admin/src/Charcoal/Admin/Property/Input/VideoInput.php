<?php

namespace Charcoal\Admin\Property\Input;

// From 'charcoal-admin'
use Charcoal\Admin\Property\Input\FileInput;

/**
 * Video Property Input
 */
class VideoInput extends FileInput
{
    /**
     * Retrieve list of default file type specifiers.
     */
    #[\Override]
    public function getDefaultAccept(): string
    {
        return 'video/*';
    }

    #[\Override]
    public function filePreview(): string
    {
        $value = $this->inputVal();
        if ($value) {
            return $this->view()->render('charcoal/admin/property/input/video/preview', $this);
        }

        return '';
    }
}

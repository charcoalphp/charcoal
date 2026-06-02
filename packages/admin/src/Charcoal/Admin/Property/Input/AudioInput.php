<?php

namespace Charcoal\Admin\Property\Input;

// From 'charcoal-admin'
use Charcoal\Admin\Property\Input\FileInput;

/**
 * Audio Property Input
 */
class AudioInput extends FileInput
{
    /**
     * Retrieve list of default file type specifiers.
     */
    #[\Override]
    public function getDefaultAccept(): string
    {
        return 'audio/*';
    }

    #[\Override]
    public function filePreview(): string
    {
        $value = $this->inputVal();
        if ($value) {
            return $this->view()->render('charcoal/admin/property/input/audio/preview', $this);
        }

        return '';
    }
}

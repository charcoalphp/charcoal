<?php

namespace Charcoal\App\Event;

/**
 * Event: File Was Uploaded
 */
class FileWasUploaded extends Event
{
    private string $file;

    /**
     * @param string $file THe file path.
     */
    public function __construct(string $file)
    {
        $this->file = $file;
    }

    /**
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }
}

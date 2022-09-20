<?php

namespace Charcoal\Event\Events;

use Charcoal\Event\Event;

/**
 * Event: File Was Uploaded
 */
class FileWasUploaded extends Event
{
    private string $file;

    /**
     * @param string $file The file path.
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

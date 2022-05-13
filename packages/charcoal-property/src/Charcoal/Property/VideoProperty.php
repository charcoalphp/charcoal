<?php

namespace Charcoal\Property;

// From 'charcoal-property'
use Charcoal\Property\FileProperty;

/**
 * Video Property.
 *
 * The video property is a specialized file property that handles video file.
 */
class VideoProperty extends FileProperty
{
    /**
     * @return string
     */
    public function type()
    {
        return 'video';
    }

    /**
     * Retrieves the default list of acceptable MIME types for uploaded files.
     *
     * This method should be overriden.
     *
     * @return string[]
     */
    public function getDefaultAcceptedMimetypes()
    {
        return [
            'video/mp4',
            'video/webm',
            'video/ogg',
            'video/ogv',
            'video/x-matroska',
        ];
    }

    /**
     * Resolve the file extension from the given MIME type.
     *
     * @param  string $type The MIME type to resolve.
     * @return string|null The extension based on the MIME type.
     */
    protected function resolveExtensionFromMimeType($type)
    {
        switch ($type) {
            case 'video/mp4':
                return 'mp4';

            case 'video/webm':
                return 'webm';

            case 'video/ogg':
            case 'video/ogv':
                return 'ogv';

            case 'video/x-matroska':
                return 'mkv';
        }

        return null;
    }
}

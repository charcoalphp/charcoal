<?php

declare(strict_types=1);

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
    #[\Override]
    public function type(): string
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
    #[\Override]
    public function getDefaultAcceptedMimetypes(): array
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
    #[\Override]
    protected function resolveExtensionFromMimeType($type): ?string
    {
        return match ($type) {
            'video/mp4' => 'mp4',
            'video/webm' => 'webm',
            'video/ogg', 'video/ogv' => 'ogv',
            'video/x-matroska' => 'mkv',
            default => null,
        };
    }
}

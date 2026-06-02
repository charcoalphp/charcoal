<?php

declare(strict_types=1);

namespace Charcoal\Property;

use InvalidArgumentException;
// From 'charcoal-property'
use Charcoal\Property\FileProperty;

/**
 * Audio Property.
 *
 * The audio property is a specialized file property that handles audio file.
 */
class AudioProperty extends FileProperty
{
    /**
     * Minimum audio length, in seconds.
     */
    private int $minLength = 0;

    /**
     * Maximum audio length, in seconds.
     */
    private int $maxLength = 0;

    #[\Override]
    public function type(): string
    {
        return 'audio';
    }

    /**
     * @param integer $minLength The minimum length allowed, in seconds.
     * @throws InvalidArgumentException If the length is not an integer.
     * @return AudioProperty Chainable
     */
    public function setMinLength($minLength): static
    {
        if (!is_int($minLength)) {
            throw new InvalidArgumentException(
                'Min length must be an integer (in seconds)'
            );
        }
        $this->minLength = $minLength;
        return $this;
    }

    public function getMinLength(): int
    {
        return $this->minLength;
    }

    /**
     * @param integer $maxLength The maximum length allowed, in seconds.
     * @throws InvalidArgumentException If the length is not an integer.
     * @return AudioProperty Chainable
     */
    public function setMaxLength($maxLength): static
    {
        if (!is_int($maxLength)) {
            throw new InvalidArgumentException(
                'Max length must be an integer (in seconds)'
            );
        }
        $this->maxLength = $maxLength;
        return $this;
    }

    public function getMaxLength(): int
    {
        return $this->maxLength;
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
            'audio/mp3',
            'audio/mpeg',
            'audio/ogg',
            'audio/webm',
            'audio/wav',
            'audio/wave',
            'audio/x-wav',
            'audio/x-pn-wav',
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
            'audio/mp3', 'audio/mpeg' => 'mp3',
            'audio/ogg' => 'ogg',
            'audio/webm' => 'webm',
            'audio/wav', 'audio/wave', 'audio/x-wav', 'audio/x-pn-wav' => 'wav',
            default => null,
        };
    }
}

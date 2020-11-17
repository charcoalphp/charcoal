<?php

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
     *
     * @var integer
     */
    private $minLength = 0;

    /**
     * Maximum audio length, in seconds.
     *
     * @var integer
     */
    private $maxLength = 0;

    /**
     * @return string
     */
    public function type()
    {
        return 'audio';
    }

    /**
     * @param integer $minLength The minimum length allowed, in seconds.
     * @throws InvalidArgumentException If the length is not an integer.
     * @return AudioProperty Chainable
     */
    public function setMinLength($minLength)
    {
        if (!is_int($minLength)) {
            throw new InvalidArgumentException(
                'Min length must be an integer (in seconds)'
            );
        }
        $this->minLength = $minLength;
        return $this;
    }

    /**
     * @return integer
     */
    public function getMinLength()
    {
        return $this->minLength;
    }

    /**
     * @param integer $maxLength The maximum length allowed, in seconds.
     * @throws InvalidArgumentException If the length is not an integer.
     * @return AudioProperty Chainable
     */
    public function setMaxLength($maxLength)
    {
        if (!is_int($maxLength)) {
            throw new InvalidArgumentException(
                'Max length must be an integer (in seconds)'
            );
        }
        $this->maxLength = $maxLength;
        return $this;
    }

    /**
     * @return integer
     */
    public function getMaxLength()
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
    public function getDefaultAcceptedMimetypes()
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
    protected function resolveExtensionFromMimeType($type)
    {
        switch ($type) {
            case 'audio/mp3':
            case 'audio/mpeg':
                return 'mp3';

            case 'audio/ogg':
                return 'ogg';

            case 'audio/webm':
                return 'webm';

            case 'audio/wav':
            case 'audio/wave':
            case 'audio/x-wav':
            case 'audio/x-pn-wav':
                return 'wav';
        }

        return null;
    }
}

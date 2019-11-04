<?php

namespace Charcoal\Property;

use InvalidArgumentException;

// From 'charcoal-property'
use Charcoal\Property\FileProperty;

/**
 * Audio Property.
 *
 * The audio property is a specialized file property.
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
     * @return string[]
     */
    public function getAcceptedMimetypes()
    {
        return [
            'audio/mp3',
            'audio/mpeg',
            'audio/ogg',
            'audio/wav',
            'audio/x-wav',
        ];
    }

    /**
     * Generate the file extension from the property's value.
     *
     * @param  string $file The file to parse.
     * @return string The extension based on the MIME type.
     */
    public function generateExtension($file = null)
    {
        if (is_string($file)) {
            if (in_array($file, $this->getAcceptedMimetypes())) {
                $mime = $file;
            } else {
                $mime = $this->getMimetypeFor($file);
            }
        } else {
            $mime = $this->getMimetype();
        }

        switch ($mime) {
            case 'audio/mp3':
            case 'audio/mpeg':
                return 'mp3';

            case 'audio/ogg':
                return 'ogg';

            case 'audio/wav':
            case 'audio/x-wav':
                return 'wav';

            default:
                return '';
        }
    }
}

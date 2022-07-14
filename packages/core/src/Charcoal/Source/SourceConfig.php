<?php

namespace Charcoal\Source;

use InvalidArgumentException;
// From 'charcoal-config'
use Charcoal\Config\AbstractConfig;

/**
 * Source Config
 */
class SourceConfig extends AbstractConfig
{
    /**
     * @var string $type
     */
    private $type;

    /**
     * @return array
     */
    public function defaults()
    {
        return [
            'type' => ''
        ];
    }

    /**
     * @param string $type The type of source.
     * @throws InvalidArgumentException If parameter is not a string.
     * @return self
     */
    public function setType($type)
    {
        if (!is_string($type)) {
            throw new InvalidArgumentException(
                'Source type must be a string.'
            );
        }
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function type()
    {
        return $this->type;
    }
}

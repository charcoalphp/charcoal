<?php

declare(strict_types=1);

namespace Charcoal\Source;

use InvalidArgumentException;
// From 'charcoal-config'
use Charcoal\Config\AbstractConfig;

/**
 * Source Config
 */
class SourceConfig extends AbstractConfig
{
    private ?string $type = null;

    #[\Override]
    public function defaults(): array
    {
        return [
            'type' => ''
        ];
    }

    /**
     * @param string $type The type of source.
     * @throws InvalidArgumentException If parameter is not a string.
     */
    public function setType($type): static
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
    public function type(): ?string
    {
        return $this->type;
    }
}

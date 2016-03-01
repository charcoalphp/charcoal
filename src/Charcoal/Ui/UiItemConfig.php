<?php

namespace Charcoal\Ui\Dashboard;

use \InvalidArgumentException;

// Module `charcoa-config` dependencies
use \Charcoal\Config\AbstractConfig;

/**
 * Base UI Item config class
 */
class UiItemConfig extends AbstractConfig
{
    /**
     * @var string $type
     */
    private $type = '';

    /**
     * @param string $type The type of UI item / dashboard/ form / form group / menu / menu item / layout.
     * @throws InvalidArgumentException If the type is not a string.
     * @return UiItemConfig Chainable
     */
    public function setType($type)
    {
        if (!is_string($type)) {
            throw new InvalidArgumentException(
                'Can not set UI item config type: Type must be a string'
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

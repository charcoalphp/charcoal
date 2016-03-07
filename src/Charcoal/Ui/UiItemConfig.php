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
     * @var string $template
     */
    private $template;

    /**
     * @var string $controller
     */
    private $controller;

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

    /**
     * @param string $template The UI item template identifier.
     * @throws InvalidArgumentException If the template is not a string.
     * @return UiItemConfig Chainable
     */
    public function setTemplate($template)
    {
        if (!is_string($template)) {
            throw new InvalidArgumentException(
                'Can not set UI item config template: Template must be a string'
            );
        }
        $this->template = $template;
        return $this;
    }

    /**
     * Get the template identifier.
     * If it was not set, use `type()` as the template default value.
     *
     * @return string
     */
    public function template()
    {
        if ($this->template === null) {
            return $this->type();
        }
        return $this->template;
    }

    /**
     * @param string $controller The UI item controller "type", or ident.
     * @throws InvalidArgumentException If the controller is not a string.
     * @return UiItemConfig Chainable
     */
    public function setController($controller)
    {
        if (!is_string($controller)) {
            throw new InvalidArgumentException(
                'Can not set UI item config controller: Controller ust be a string'
            );
        }
        $this->controller = $controller;
        return $this;
    }

    /**
     * Get the controller.
     * If it was not set, use `type()` as the default controller value.
     *
     * @return string
     */
    public function controller()
    {
        if ($this->controller === null) {
            return $this->type();
        }
        return $this->controller;
    }
}

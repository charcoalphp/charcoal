<?php

namespace Charcoal\Ui;

use InvalidArgumentException;
// From 'charcoa-config'
use Charcoal\Config\AbstractConfig;

/**
 * A UI Item configuration set.
 */
class UiItemConfig extends AbstractConfig
{
    /**
     * The UI item type.
     *
     * @var string|null
     */
    private $type;

    /**
     * The UI item's template.
     *
     * @var string|null
     */
    private $template;

    /**
     * The FQN of a view controller.
     *
     * @var string|null
     */
    private $controller;

    /**
     * Set the UI item type.
     *
     * The type of UI item (e.g., dashboard, layout, form, form group,
     * field set, field, menu, menu item).
     *
     * @param string|null $type The UI item type.
     * @throws InvalidArgumentException If the type is not a string.
     * @return UiItemConfig Chainable
     */
    public function setType($type)
    {
        if (is_string($type) || $type === null) {
            $this->type = $type;
        } else {
            throw new InvalidArgumentException(
                'Can not set UI item config type: Type must be a string or NULL'
            );
        }

        return $this;
    }

    /**
     * Retrieve the UI item type.
     *
     * @return string
     */
    public function type()
    {
        return $this->type;
    }

    /**
     * Set the UI item's template.
     *
     * Usually, a path to a file containing the template to be rendered.
     *
     * @param string $template A template (identifier).
     * @throws InvalidArgumentException If the template is not a string.
     * @return UiItemInterface Chainable
     */
    public function setTemplate($template)
    {
        if (!is_string($template)) {
            throw new InvalidArgumentException(
                'The UI Item Config can not set the template, must be a string'
            );
        }

        $this->template = $template;

        return $this;
    }

    /**
     * Retrieve the UI item's template.
     *
     * @return string If unset, returns the UI item type.
     */
    public function template()
    {
        if ($this->template === null) {
            return $this->type();
        }

        return $this->template;
    }

    /**
     * Set the UI item's view controller "type" (identifier).
     *
     * @param string $controller The FQN of a view controller.
     * @throws InvalidArgumentException If the controller is not a string.
     * @return UiItemInterface Chainable
     */
    public function setController($controller)
    {
        if (!is_string($controller)) {
            throw new InvalidArgumentException(
                'The UI Item Config can not set the view controller, must be a string'
            );
        }

        $this->controller = $controller;

        return $this;
    }

    /**
     * Retrieve the UI item's view controller "type" (identifier).
     *
     * @return string If unset, returns the UI item type.
     */
    public function controller()
    {
        if ($this->controller === null) {
            return $this->type();
        }

        return $this->controller;
    }
}

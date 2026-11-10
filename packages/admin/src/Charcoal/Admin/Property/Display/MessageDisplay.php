<?php

namespace Charcoal\Admin\Property\Display;

// From Pimple
use Pimple\Container;
// From 'charcoal-view'
use Charcoal\View\ViewableInterface;
use Charcoal\View\ViewableTrait;
// From 'charcoal-translator'
use Charcoal\Translator\Translation;
// From 'charcoal-admin'
use Charcoal\Admin\Property\AbstractPropertyDisplay;

/**
 * Message Display Property
 *
 * Example:
 *
 * ```json
 * {
 *     "heading": {
 *         "type": "string",
 *         "storable": false,
 *         "output_type": "display",
 *         "display_type": "charcoal/admin/property/display/message",
 *         "message": {
 *             "en": "<h3 class=\"pb-2 mb-n2 border-bottom\">My Heading</h3>"
 *         },
 *         "show_label": false
 *     }
 * }
 * ```
 */
class MessageDisplay extends AbstractPropertyDisplay implements
    ViewableInterface
{
    use ViewableTrait;

    /**
     * @var Translation|null
     */
    protected $message;

    /**
     * @param  mixed $message The message to display.
     * @return self
     */
    public function setMessage($message)
    {
        $this->message = $this->translator()->translation($message);
        if ($this->message instanceof Translation) {
            $this->message->isRendered = false;
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function hasMessage()
    {
        return !!$this->message;
    }

    /**
     * @return Translation|string|null
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return string|null
     */
    public function displayMessage()
    {
        $message = $this->message;

        if ($message instanceof Translation) {
            if (isset($message->isRendered) && $message->isRendered === false) {
                $message = $this->renderTranslatableTemplate($message);
            }

            if ($this->lang() && isset($message[$this->lang()])) {
                $message = $message[$this->lang()];
            } else {
                $message = (string)$message;
            }
        }

        if (is_string($message)) {
            return $this->escapeVal($message);
        }

        return $message;
    }
}

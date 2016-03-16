<?php

namespace Charcoal\Ui;

use \InvalidArgumentException;

// Module `charcoal-config` dependencies
use \Charcoal\Config\AbstractEntity;

// Module `charcoal-core` dependencies
use \Charcoal\Translation\TranslationString;

// Module `charcoal-view` dependencies
use \Charcoal\View\ViewableInterface;
use \Charcoal\View\ViewableTrait;

// Intra-module (`charcoal-ui`) dependencies
use \Charcoal\Ui\UiItemInterface;

/**
 *
 */
abstract class AbstractUiItem extends AbstractEntity implements UiItemInterface
{
    use ViewableTrait;

    /**
     * @var string $template
     */
    private $template;

    /**
     * @var TranslationString $label
     */
    private $label = '';

    /**
     * @var TranslationString $title
     */
    private $title = '';

    /**
     * @var TranslationString $subtitle
     */
    private $subtitle = '';

    /**
     * @var TranslationString $description
     */
    private $description = '';

    /**
     * @var TranslationString $notes
     */
    private $notes = '';

    abstract public function type();

    /**
     * @param string $template
     * @throws InvalidArgumentException If the template identifier is not a string.
     * @return UiItemInterface Chainable
     */
    public function setTemplate($template)
    {
        if (!is_string($template)) {
            throw new InvalidArgumentException(
                'Can not set UI Item\'s template: template identifier must be a string'
            );
        }
        $this->template = $template;
        return $this;
    }

    /**
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
     * @param mixed $title The group title.
     * @return UiItemInterface Chainable
     */
    public function setTitle($title)
    {
        $this->title = new TranslationString($title);
        return $this;
    }

    /**
     * Get the title. If unset, returns the label.
     *
     * @return TranslationString
     */
    public function title()
    {
        return $this->title;
    }

    /**
     * @param mixed $subtitle The group subtitle.
     * @return UiItemInterface Chainable
     */
    public function setSubtitle($subtitle)
    {
        $this->subtitle = new TranslationString($subtitle);
        return $this;
    }

    /**
     * @return TranslationString
     */
    public function subtitle()
    {
        return $this->subtitle;
    }

    /**
     * @param mixed $description The group description.
     * @return UiItemInterface Chainable
     */
    public function setDescription($description)
    {
        $this->description = new TranslationString($description);
        return $this;
    }

    /**
     * @return TranslationString
     */
    public function description()
    {
        return $this->description;
    }

    /**
     * @param mixed $notes The group notes.
     * @return UiItemInterface Chainable
     */
    public function setNotes($notes)
    {
        $this->notes = new TranslationString($notes);
        return $this;
    }

    /**
     * @return TranslationString
     */
    public function notes()
    {
        return $this->notes;
    }
}

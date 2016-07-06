<?php

namespace Charcoal\Ui;

use \InvalidArgumentException;

// Module `charcoal-config` dependencies
use \Charcoal\Config\AbstractEntity;

// Module `charcoal-core` dependencies
use \Charcoal\Translation\TranslationString;

// Intra-module (`charcoal-ui`) dependencies
use \Charcoal\Ui\UiItemInterface;
use \Charcoal\Ui\UiItemTrait;

/**
 * Ui Item Trait
 */
trait UiItemtrait
{
    /**
     * @param boolean $active
     */
    private $active = true;

    /**
     * @var string $type
     */
    private $type;

    /**
     * @var string $template
     */
    private $template;

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

    /**
     * If it is set to false, will disable display of title.
     * @var boolean $showTitle
     */
    private $showTitle = true;

    /**
     * If it is set to false, will disable display of title.
     * @var boolean $showSubtitle
     */
    private $showSubtitle = true;

    /**
     * If it is set to false, will disable display of description
     * @var boolean $showDescription
     */
    private $showDescription = true;

    /**
     * If it is set to false, will disable display of the notes (footer).
     * @var boolean $showNotes
     */
    private $showNotes = true;

    /**
     * @var boolean $showHeader
     */
    private $showHeader = true;

    /**
     * @var boolean $showFooter
     */
    private $showFooter = true;

    /**
     * @param string $active The item active flag.
     * @return UiItemInterface Chainable
     */
    public function setActive($active)
    {
        $this->active = !!$active;
        return $this;
    }

    /**
     * @return string
     */
    public function active()
    {
        return $this->active;
    }

    /**
     * @param string $type The UI item type.
     * @return UiItemInterface Chainable
     */
    public function setType($type)
    {
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
     * @param string $template The UI item's template (identifier).
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
     * Get the title.
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

        /**
         * @param boolean $show The show title flag.
         * @return FormGroup Chainable
         */
    public function setShowTitle($show)
    {
        $this->showTitle = !!$show;
        return $this;
    }

    /**
     * @return boolean
     */
    public function showTitle()
    {
        if ($this->showTitle === false) {
            return false;
        } else {
            return !!$this->title();
        }
    }

    /**
     * @param boolean $show The show subtitle flag.
     * @return FormGroup Chainable
     */
    public function setShowSubtitle($show)
    {
        $this->showSubtitle = !!$show;
        return $this;
    }

    /**
     * @return boolean
     */
    public function showSubtitle()
    {
        if ($this->showSubtitle === false) {
            return false;
        } else {
            return !!$this->subtitle();
        }
    }

    /**
     * @param boolean $show The show description flag.
     * @return FormGroup Chainable
     */
    public function setShowDescription($show)
    {
        $this->showDescription = !!$show;
        return $this;
    }

    /**
     * @return boolean
     */
    public function showDescription()
    {
        if ($this->showDescription === false) {
            return false;
        } else {
            return !!$this->description();
        }
    }

    /**
     * @param boolean $show The show notes flag.
     * @return FormGroup Chainable
     */
    public function setShowNotes($show)
    {
        $this->showNotes = !!$show;
        return $this;
    }

    /**
     * Ensure there are are notes to show, if notes are to be shown.
     * @return boolean
     */
    public function showNotes()
    {
        if ($this->showNotes === false) {
            return false;
        } else {
            $notes = $this->notes();
            return !!$notes;
        }
    }

    /**
     * @param boolean $show The show header flag.
     * @return FormGroup Chainable
     */
    public function setShowHeader($show)
    {
        $this->showHeader = !!$show;
        return $this;
    }

    /**
     * @return boolean
     */
    public function showHeader()
    {
        if ($this->showHeader === false) {
            return false;
        } else {
            return $this->showTitle();
        }
    }

    /**
     * @param boolean $show The show footer flag.
     * @return FormGroup Chainable
     */
    public function setShowFooter($show)
    {
        $this->showFooger = !!$show;
        return $this;
    }

    /**
     * @return boolean
     */
    public function showFooter()
    {
        if ($this->showFooter === false) {
            return false;
        } else {
            return $this->showNotes();
        }
    }
}

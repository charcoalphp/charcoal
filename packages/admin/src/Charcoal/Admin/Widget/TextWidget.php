<?php

declare(strict_types=1);

namespace Charcoal\Admin\Widget;

// From 'charcoal-admin'
use Charcoal\Admin\AdminWidget;

/**
 *
 */
class TextWidget extends AdminWidget
{
    private bool $showTitle = true;

    private bool $showSubtitle = true;

    private bool $showDescription = true;

    private bool $showNotes = true;

    /**
     * @var \Charcoal\Translator\Translation|string|null
     */
    private $title;

    /**
     * @var \Charcoal\Translator\Translation|string|null
     */
    private $subtitle;

    /**
     * @var \Charcoal\Translator\Translation|string|null
     */
    private $description;

    /**
     * @var \Charcoal\Translator\Translation|string|null
     */
    private $notes;

    /**
     * @param boolean $show The show title flag.
     */
    public function setShowTitle($show): static
    {
        $this->showTitle = (bool) $show;
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
            return (bool) $this->title();
        }
    }

    /**
     * @param boolean $show The show subtitle flag.
     */
    public function setShowSubtitle($show): static
    {
        $this->showSubtitle = (bool) $show;
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
            return (bool) $this->subtitle();
        }
    }

    /**
     * @param boolean $show The show description flag.
     */
    public function setShowDescription($show): static
    {
        $this->showDescription = (bool) $show;
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
            return (bool) $this->description();
        }
    }

    /**
     * @param boolean $show The "show notes" flag.
     */
    public function setShowNotes($show): static
    {
        $this->showNotes = (bool) $show;
        return $this;
    }

    /**
     * @return boolean
     */
    public function showNotes()
    {
        if ($this->showNotes === false) {
            return false;
        } else {
            return (bool) $this->notes();
        }
    }

    /**
     * @param mixed $title The text widget title.
     */
    public function setTitle($title): static
    {
        $this->title = $this->translator()->translation($title);

        return $this;
    }

    /**
     * @return \Charcoal\Translator\Translation|string|null
     */
    public function title()
    {
        return $this->title;
    }

    /**
     * @param mixed $subtitle The text widget subtitle.
     */
    public function setSubtitle($subtitle): static
    {
        $this->subtitle = $this->translator()->translation($subtitle);

        return $this;
    }

    /**
     * @return \Charcoal\Translator\Translation|string|null
     */
    public function subtitle()
    {
        return $this->subtitle;
    }

    /**
     * @param mixed $description The text widget description (main content).
     */
    public function setDescription($description): static
    {
        $this->description = $this->translator()->translation($description);

        return $this;
    }

    /**
     * @return \Charcoal\Translator\Translation|string|null
     */
    public function description()
    {
        return $this->description;
    }

    /**
     * @param mixed $notes The text widget notes.
     */
    public function setNotes($notes): static
    {
        $this->notes = $this->translator()->translation($notes);

        return $this;
    }

    /**
     * @return \Charcoal\Translator\Translation|string|null
     */
    public function notes()
    {
        return $this->notes;
    }
}

<?php

namespace Charcoal\Ui;

use InvalidArgumentException;

use Charcoal\Ui\UiItemInterface;

/**
 * Provides an implementation of {@see \Charcoal\Ui\UiItemInterface}.
 */
trait UiItemTrait
{

    /**
     * @var boolean
     */
    private $active = true;

    /**
     * The UI item's sorting index.
     *
     * @var integer
     */
    private $priority = 0;

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
     * The UI item's icon.
     *
     * Note: Only icons from the {@link http://fontawesome.io/ Font Awesome}
     * library are supported.
     *
     * @var string|null
     */
    private $icon;

    /**
     * The UI item's title.
     *
     * @var \Charcoal\Translator\Translation
     */
    private $title = '';

    /**
     * The UI item's sub-title.
     *
     * @var \Charcoal\Translator\Translation
     */
    private $subtitle = '';

    /**
     * The UI item's description.
     *
     * @var \Charcoal\Translator\Translation
     */
    private $description = '';

    /**
     * The UI item's notes.
     *
     * @var \Charcoal\Translator\Translation
     */
    private $notes = '';

    /**
     * The title is displayed by default.
     *
     * @var boolean
     */
    private $showTitle = true;

    /**
     * The sub-title is displayed by default.
     *
     * @var boolean
     */
    private $showSubtitle = true;

    /**
     * The description is displayed by default.
     *
     * @var boolean
     */
    private $showDescription = true;

    /**
     * The notes are displayed by default.
     *
     * @var boolean
     */
    private $showNotes = true;

    /**
     * The icon is displayed by default.
     *
     * @var boolean
     */
    private $showIcon = true;

    /**
     * The header is displayed by default.
     *
     * @var boolean
     */
    private $showHeader = true;

    /**
     * The footer is displayed by default.
     *
     * @var boolean
     */
    private $showFooter = true;

    /**
     * Activates/deactivates the UI item.
     *
     * @param boolean $active Activate (TRUE) or deactivate (FALSE) the UI item.
     * @return AbstractUiItem Chainable
     */
    public function setActive($active)
    {
        $this->active = !!$active;

        return $this;
    }

    /**
     * Determine if the UI item is active.
     *
     * @return boolean
     */
    public function active()
    {
        return $this->active;
    }

    /**
     * Set the UI item type.
     *
     * @param string|null $type The UI item type.
     * @throws InvalidArgumentException If the type is not a string (or null).
     * @return UiItemInterface Chainable
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
     * If it is not explicitely set (or null), then return the object's FQN.
     *
     * @return string
     */
    public function type()
    {
        if ($this->type === null) {
            return static::class;
        }
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
                'The UI Item can not set the template, must be a string'
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
     * Set the group's priority or sorting index.
     *
     * @param  integer|mixed $priority An index, for sorting.
     * @throws InvalidArgumentException If the priority is not an integer / numeric.
     * @return self
     */
    public function setPriority($priority)
    {
        if (!is_numeric($priority)) {
            throw new InvalidArgumentException(
                'Priority must be an integer'
            );
        }

        $this->priority = intval($priority);

        return $this;
    }

    /**
     * Retrieve the group's priority or sorting index.
     *
     * @return integer
     */
    public function priority()
    {
        return $this->priority;
    }


    /**
     * Set the UI item's title.
     *
     * @param mixed $title A title.
     * @return UiItemInterface Chainable
     */
    public function setTitle($title)
    {
        $this->title = $this->translator()->translation($title);
        return $this;
    }

    /**
     * Retrieve the title.
     *
     * @return \Charcoal\Translator\Translation|null
     */
    public function title()
    {
        return $this->title;
    }

    /**
     * Set the UI item's sub-title.
     *
     * @param mixed $subtitle A sub-title.
     * @return UiItemInterface Chainable
     */
    public function setSubtitle($subtitle)
    {
        $this->subtitle = $this->translator()->translation($subtitle);
        return $this;
    }

    /**
     * Retrieve the sub-title.
     *
     * @return \Charcoal\Translator\Translation|null
     */
    public function subtitle()
    {
        return $this->subtitle;
    }

    /**
     * Set the UI item's description.
     *
     * @param mixed $description A description.
     * @return UiItemInterface Chainable
     */
    public function setDescription($description)
    {
        $this->description = $this->translator()->translation($description);
        return $this;
    }

    /**
     * Retrieve the description.
     *
     * @return \Charcoal\Translator\Translation|null
     */
    public function description()
    {
        return $this->description;
    }

    /**
     * Set notes about the UI item.
     *
     * @param mixed $notes Notes.
     * @return UiItemInterface Chainable
     */
    public function setNotes($notes)
    {
        $this->notes = $this->translator()->translation($notes);
        return $this;
    }

    /**
     * Retrieve the notes.
     *
     * @return \Charcoal\Translator\Translation|null
     */
    public function notes()
    {
        return $this->notes;
    }

    /**
     * Retrieve the path to the item's icon.
     *
     * @todo  [mcaskill 2016-09-16] Move this to a tab interface in charcoal-admin
     *     so as to focus the icon getter/setter on being a Glyphicon.
     * @return string
     */
    public function icon()
    {
        return $this->icon;
    }

    /**
     * Set the path to the item's icon associated with the object.
     *
     * @param  string $icon A path to an image.
     * @return UiItemInterface Chainable
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Show/hide the UI item's title.
     *
     * @param boolean $show Show (TRUE) or hide (FALSE) the title.
     * @return UiItemInterface Chainable
     */
    public function setShowTitle($show)
    {
        $this->showTitle = !!$show;

        return $this;
    }

    /**
     * Determine if the title is to be displayed.
     *
     * @return boolean If TRUE or unset, check if there is a title.
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
     * Show/hide the UI item's sub-title.
     *
     * @param boolean $show Show (TRUE) or hide (FALSE) the sub-title.
     * @return UiItemInterface Chainable
     */
    public function setShowSubtitle($show)
    {
        $this->showSubtitle = !!$show;

        return $this;
    }

    /**
     * Determine if the sub-title is to be displayed.
     *
     * @return boolean If TRUE or unset, check if there is a sub-title.
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
     * Show/hide the UI item's description.
     *
     * @param boolean $show Show (TRUE) or hide (FALSE) the description.
     * @return UiItemInterface Chainable
     */
    public function setShowDescription($show)
    {
        $this->showDescription = !!$show;

        return $this;
    }

    /**
     * Determine if the description is to be displayed.
     *
     * @return boolean If TRUE or unset, check if there is a description.
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
     * Show/hide the UI item's notes.
     *
     * @param boolean $show Show (TRUE) or hide (FALSE) the notes.
     * @return UiItemInterface Chainable
     */
    public function setShowNotes($show)
    {
        $this->showNotes = !!$show;

        return $this;
    }

    /**
     * Determine if the notes is to be displayed.
     *
     * @return boolean If TRUE or unset, check if there are notes.
     */
    public function showNotes()
    {
        if ($this->showNotes === false) {
            return false;
        } else {
            return !!$this->notes();
        }
    }

    /**
     * Show/hide the UI item's icon.
     *
     * @param boolean $show Show (TRUE) or hide (FALSE) the icon.
     * @return UiItemInterface Chainable
     */
    public function setShowIcon($show)
    {
        $this->showIcon = !!$show;

        return $this;
    }

    /**
     * Determine if the icon is to be displayed.
     *
     * @return boolean If TRUE or unset, check if there is an icon.
     */
    public function showIcon()
    {
        if ($this->showIcon === false) {
            return false;
        } else {
            return !!$this->icon();
        }
    }

    /**
     * Show/hide the UI item's header.
     *
     * @param boolean $show Show (TRUE) or hide (FALSE) the header.
     * @return UiItemInterface Chainable
     */
    public function setShowHeader($show)
    {
        $this->showHeader = !!$show;

        return $this;
    }

    /**
     * Determine if the header is to be displayed.
     *
     * @return boolean If TRUE or unset, check if there is a title.
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
     * Show/hide the UI item's footer.
     *
     * @param boolean $show Show (TRUE) or hide (FALSE) the footer.
     * @return UiItemInterface Chainable
     */
    public function setShowFooter($show)
    {
        $this->showFooter = !!$show;

        return $this;
    }

    /**
     * Determine if the footer is to be displayed.
     *
     * @return boolean If TRUE or unset, check if there are notes.
     */
    public function showFooter()
    {
        if ($this->showFooter === false) {
            return false;
        } else {
            return $this->showNotes();
        }
    }

    /**
     * Static comparison function used by {@see uasort()}.
     *
     * @param  UiItemInterface $a Widget A.
     * @param  UiItemInterface $b Widget B.
     * @return integer Sorting value: -1 or 1
     */
    protected static function sortItemsByPriority(UiItemInterface $a, UiItemInterface $b)
    {
        $priorityA = $a->priority();
        $priorityB = $b->priority();

        if ($priorityA === $priorityB) {
            return 0;
        }
        return ($priorityA < $priorityB) ? (-1) : 1;
    }

    /**
     * All UI objects are translatable, therefore are `translator`-aware.
     *
     * @return \Charcoal\Translator\Translator
     */
    abstract protected function translator();
}

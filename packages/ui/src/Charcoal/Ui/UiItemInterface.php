<?php

namespace Charcoal\Ui;

// From 'charcoal-config'
use Charcoal\Config\EntityInterface;
// From 'charcoal-view'
use Charcoal\View\ViewableInterface;
// From 'charcoal-ui'
use Charcoal\Ui\PrioritizableInterface;
use Charcoal\Ui\ConditionalizableInterface;

/**
 * Defines a UI Item.
 *
 * - Also implements the Entity Interface.
 *   - for `ArrayAccess`, `Serializable` and `JsonSerializable`.
 *   - also add `keys()`, `data()`, `keys`, `delegates` and `separator`) methods.
 * - Also implements the Viewable Interface.
 *   - add a required `view` dependency. Typically provided from DI container `['view']`.
 *   - also add `template_ident` property.
 */
interface UiItemInterface extends
    EntityInterface,
    PrioritizableInterface,
    ConditionalizableInterface,
    ViewableInterface
{
    /**
     * Activates/deactivates the UI item.
     *
     * @param  boolean $active Activate (TRUE) or deactivate (FALSE) the UI item.
     * @return UiItemInterface Returns the current item.
     */
    public function setActive($active);

    /**
     * Determine if the UI item is active.
     *
     * @return boolean
     */
    public function active();

    /**
     * Set the UI item type.
     *
     * @param  string|null $type The UI item type.
     * @return UiItemInterface Returns the current item.
     */
    public function setType($type);

    /**
     * Retrieve the UI item type.
     *
     * @return string
     */
    public function type();

    /**
     * Set the UI item's template.
     *
     * Usually, a path to a file containing the template to be rendered.
     *
     * @param  string $template A template (identifier).
     * @return UiItemInterface Returns the current item.
     */
    public function setTemplate($template);

    /**
     * Retrieve the UI item's template.
     *
     * @return string
     */
    public function template();

    /**
     * Set the UI item's title.
     *
     * @param  mixed $title A title.
     * @return UiItemInterface Returns the current item.
     */
    public function setTitle($title);

    /**
     * Retrieve the title.
     *
     * @return \Charcoal\Translator\Translation|null
     */
    public function title();

    /**
     * Set the UI item's sub-title.
     *
     * @param  mixed $subtitle A sub-title.
     * @return UiItemInterface Returns the current item.
     */
    public function setSubtitle($subtitle);

    /**
     * Retrieve the sub-title.
     *
     * @return \Charcoal\Translator\Translation|null
     */
    public function subtitle();

    /**
     * Set the UI item's description.
     *
     * @param  mixed $description A description.
     * @return UiItemInterface Returns the current item.
     */
    public function setDescription($description);

    /**
     * Retrieve the description.
     *
     * @return \Charcoal\Translator\Translation|null
     */
    public function description();

    /**
     * Set notes about the UI item.
     *
     * @param  mixed $notes Notes.
     * @return UiItemInterface Returns the current item.
     */
    public function setNotes($notes);

    /**
     * Retrieve the notes.
     *
     * @return \Charcoal\Translator\Translation|null
     */
    public function notes();

    /**
     * Show/hide the UI item's title.
     *
     * @param  boolean $show Show (TRUE) or hide (FALSE) the title.
     * @return UiItemInterface Returns the current item.
     */
    public function setShowTitle($show);

    /**
     * Determine if the title is to be displayed.
     *
     * @return boolean
     */
    public function showTitle();

    /**
     * Show/hide the UI item's sub-title.
     *
     * @param  boolean $show Show (TRUE) or hide (FALSE) the sub-title.
     * @return UiItemInterface Returns the current item.
     */
    public function setShowSubtitle($show);

    /**
     * Determine if the sub-title is to be displayed.
     *
     * @return boolean
     */
    public function showSubtitle();

    /**
     * Show/hide the UI item's description.
     *
     * @param  boolean $show Show (TRUE) or hide (FALSE) the description.
     * @return UiItemInterface Returns the current item.
     */
    public function setShowDescription($show);

    /**
     * Determine if the description is to be displayed.
     *
     * @return boolean
     */
    public function showDescription();

    /**
     * Show/hide the UI item's notes.
     *
     * @param  boolean $show Show (TRUE) or hide (FALSE) the notes.
     * @return UiItemInterface Returns the current item.
     */
    public function setShowNotes($show);

    /**
     * Determine if the notes is to be displayed.
     *
     * @return boolean
     */
    public function showNotes();

    /**
     * Show/hide the UI item's header.
     *
     * @param  boolean $show Show (TRUE) or hide (FALSE) the header.
     * @return UiItemInterface Returns the current item.
     */
    public function setShowHeader($show);

    /**
     * Determine if the header is to be displayed.
     *
     * @return boolean
     */
    public function showHeader();

    /**
     * Show/hide the UI item's footer.
     *
     * @param  boolean $show Show (TRUE) or hide (FALSE) the footer.
     * @return UiItemInterface Returns the current item.
     */
    public function setShowFooter($show);

    /**
     * Determine if the footer is to be displayed.
     *
     * @return boolean
     */
    public function showFooter();
}

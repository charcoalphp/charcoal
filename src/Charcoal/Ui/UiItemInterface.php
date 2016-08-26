<?php

namespace Charcoal\Ui;

// From 'charcoal-config'
use \Charcoal\Config\EntityInterface;

// From 'charcoal-view'
use \Charcoal\View\ViewableInterface;

/**
 * Defines a UI Item
 *
 * - Also implements the Entity Interface.
 *   - for `ArrayAccess`, `Serializable` and `JsonSerializable`.
 *   - also add `keys()`, `data()`, `keys`, `delegates` and `separator`) methods.
 * - Also implements the Viewable Interface.
 *   - add a required `view` dependency. Typically provided from DI container `['view']`.
 *   - also add `template_ident` property.
 *
 */
interface UiItemInterface extends EntityInterface, ViewableInterface
{
    /**
     * Activates/deactivates the item.
     *
     * @param boolean $active Activate (TRUE) or deactivate (FALSE) the item.
     * @return UiItemInterface Chainable
     */
    public function setActive($active);

    /**
     * Determine if the item is active.
     *
     * @return boolean
     */
    public function active();

    /**
     * @param string $template The UI item's template (identifier).
     * @return UiItemInterface Chainable
     */
    public function setTemplate($template);

    /**
     * @return string
     */
    public function template();

    /**
     * @param mixed $title The group title.
     * @return UiItemInterface Chainable
     */
    public function setTitle($title);

    /**
     * Get the title. If unset, returns the label.
     *
     * @return TranslationString
     */
    public function title();

    /**
     * @param mixed $subtitle The group subtitle.
     * @return UiItemInterface Chainable
     */
    public function setSubtitle($subtitle);

    /**
     * @return TranslationString
     */
    public function subtitle();

    /**
     * @param mixed $description The group description.
     * @return UiItemInterface Chainable
     */
    public function setDescription($description);

    /**
     * @return TranslationString
     */
    public function description();

    /**
     * @param mixed $notes The group notes.
     * @return UiItemInterface Chainable
     */
    public function setNotes($notes);

    /**
     * @return TranslationString
     */
    public function notes();

    /**
     * @param boolean $show The show title flag.
     * @return FormGroup Chainable
     */
    public function setShowTitle($show);

    /**
     * @return boolean
     */
    public function showTitle();

    /**
     * @param boolean $show The show subtitle flag.
     * @return FormGroup Chainable
     */
    public function setShowSubtitle($show);

    /**
     * @return boolean
     */
    public function showSubtitle();

    /**
     * @param boolean $show The show description flag.
     * @return FormGroup Chainable
     */
    public function setShowDescription($show);

    /**
     * @return boolean
     */
    public function showDescription();

    /**
     * @param boolean $show The show notes flag.
     * @return FormGroup Chainable
     */
    public function setShowNotes($show);

    /**
     * Ensure there are are notes to show, if notes are to be shown.
     * @return boolean
     */
    public function showNotes();

    /**
     * @param boolean $show The show header flag.
     * @return FormGroup Chainable
     */
    public function setShowHeader($show);

    /**
     * @return boolean
     */
    public function showHeader();

    /**
     * @param boolean $show The show footer flag.
     * @return FormGroup Chainable
     */
    public function setShowFooter($show);

    /**
     * @return boolean
     */
    public function showFooter();
}

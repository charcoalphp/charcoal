<?php

namespace Charcoal\Ui;

// Module `charcoal-config` dependencies
use \Charcoal\Config\EntityInterface;

// Module `charcoal-view` dependencies
use \Charcoal\View\ViewableInterface;

/**
 * Basic UI Item Interface.
 *
 * -Also implements Entity Interface.
 *   - for `ArrayAccess`, `Serializable` and `JsonSerializable`.
 *   - also add `keys()`, `data()`, `keys`, `delegates` and `separator`) methods.
 * -Also implements Viewable Interface.
 *   - add a required `view` dependency. Typically provided from DI container `['view']`.
 *   - also add `template_ident` property.
 *
 */
interface UiItemInterface extends EntityInterface, ViewableInterface
{
    /**
     * @param mixed $description The group title.
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
}

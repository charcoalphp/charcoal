<?php

namespace Charcoal\Admin\Widget\FormGroup;

use RuntimeException;
// From Pimple
use Pimple\Container;
// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;
// From 'charcoal-ui'
use Charcoal\Ui\FormGroup\AbstractFormGroup;
// From 'charcoal-admin'
use Charcoal\Admin\Ui\NestedWidgetContainerInterface;
use Charcoal\Admin\Ui\NestedWidgetContainerTrait;

/**
 * Nested Widget Form Group
 *
 * Allows UI widgets to be embedded into a form group and rendered using the current object, if any.
 *
 * Based on WidgetFormGroup
 * https://bitbucket.org/beneroch/charcoal-utils/src/faa819a/src/Utils/Widget/FormGroup/WidgetFormGroup.php
 * from _beneroch/charcoal-utils_.
 *
 * Usage:
 * ```json
 * {
 *     "title": "My Nested Collection",
 *     "type": "charcoal/admin/widget/form-group/nested-widget",
 *     "widget_data": {
 *         "type": "charcoal/admin/widget/table",
 *         "obj_type": "foobar/model/item",
 *         "collection_ident": "grouped",
 *         "sortable": true
 *     },
 *     "renderable_data": {
 *         "collection_config": {
 *             "filters": [
 *                 {
 *                     "property": "category",
 *                     "val": "{{ id }}"
 *                 }
 *             ],
 *             "list_actions": [
 *                 {
 *                     "ident": "create",
 *                     "url": "object/edit?obj_type=foobar/model/item&form_data[category]={{ id }}"
 *                 }
 *             ]
 *         }
 *     }
 * }
 * ```
 */
class NestedWidgetFormGroup extends AbstractFormGroup implements
    NestedWidgetContainerInterface
{
    use NestedWidgetContainerTrait;

    /**
     * @var string
     */
    private $widgetId;

    /**
     * Store the widget factory instance for the current class.
     */
    private ?\Charcoal\Factory\FactoryInterface $widgetFactory = null;

    /**
     * Whether notes shoudl be display before or after the form fields.
     */
    private bool $showNotesAbove = false;

    /**
     * Retrieve the widget's ID.
     *
     * @return string
     */
    public function widgetId()
    {
        if (!$this->widgetId) {
            $this->widgetId = 'nested_widget_' . uniqid();
        }

        return $this->widgetId;
    }

    /**
     * Retrieve the current form group
     */
    public function currentFromGroup(): static
    {
        return $this;
    }

    /**
     * Set the widget's ID.
     *
     * @param  string $widgetId The widget identifier.
     */
    public function setWidgetId($widgetId): static
    {
        $this->widgetId = $widgetId;

        return $this;
    }

    /**
     * @return Translation|string|null
     */
    #[\Override]
    public function description(): string
    {
        return $this->renderTemplate((string)parent::description());
    }

    /**
     * @return Translation|string|null
     */
    #[\Override]
    public function notes(): string
    {
        return $this->renderTemplate((string)parent::notes());
    }

    /**
     * Show/hide the widget's notes.
     *
     * @param  boolean|string $show Whether to show or hide notes.
     * @return FormGroupWidget Chainable
     */
    #[\Override]
    public function setShowNotes($show)
    {
        $this->showNotesAbove = ($show === 'above');

        return parent::setShowNotes($show);
    }

    public function showNotesAbove(): bool
    {
        return $this->showNotesAbove && $this->showNotes();
    }

    /**
     * @param  Container $container The DI container.
     * @return void
     */
    #[\Override]
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setWidgetFactory($container['widget/factory']);

        // Satisfies Charcoal\View\ViewableInterface dependencies
        $this->setView($container['view']);
    }

    /**
     * Set the widget factory.
     *
     * @param FactoryInterface $factory The factory to create widgets.
     */
    protected function setWidgetFactory(FactoryInterface $factory): static
    {
        $this->widgetFactory = $factory;

        return $this;
    }

    /**
     * Retrieve the widget factory.
     *
     * @throws RuntimeException If the widget factory was not previously set.
     */
    protected function widgetFactory(): \Charcoal\Factory\FactoryInterface
    {
        if (!$this->widgetFactory instanceof \Charcoal\Factory\FactoryInterface) {
            throw new RuntimeException(sprintf(
                'Widget Factory is not defined for "%s"',
                static::class
            ));
        }

        return $this->widgetFactory;
    }
}

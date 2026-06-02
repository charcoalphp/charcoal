<?php

namespace Charcoal\Admin\Widget\FormGroup;

use RuntimeException;
// From 'charcoal-admin'
use Charcoal\Admin\Ui\NestedWidgetContainerInterface;
use Charcoal\Admin\Ui\NestedWidgetContainerTrait;
use Charcoal\Admin\Ui\ObjectContainerInterface;
use Charcoal\Admin\Ui\ObjectContainerTrait;
// From 'charcoal-ui'
use Charcoal\Ui\FormGroup\AbstractFormGroup;
// From 'charcoal-config'
use Charcoal\Config\ConfigurableInterface;
// from 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;
// from 'charcoal-translator'
use Charcoal\Translator\Translation;
// From 'pimple'
use Pimple\Container;
// from 'charcoal-attachment'
use Charcoal\Attachment\Traits\ConfigurableAttachmentsTrait;

/**
 * Attachment widget, as form group.
 */
class AttachmentFormGroup extends AbstractFormGroup implements
    ConfigurableInterface,
    NestedWidgetContainerInterface,
    ObjectContainerInterface
{
    use ConfigurableAttachmentsTrait;
    use NestedWidgetContainerTrait;
    use ObjectContainerTrait {
        ObjectContainerTrait::createOrLoadObj as createOrCloneOrLoadObj;
    }

    /**
     * @var string
     */
    private $widgetId;

    /**
     * Store the widget factory instance for the current class.
     */
    private ?\Charcoal\Factory\FactoryInterface $widgetFactory = null;

    /**
     * Whether notes should be display before or after the form fields.
     */
    private bool $showNotesAbove = false;

    /**
     * Set the widget's data.
     *
     * @param array $data The widget data.
     */
    #[\Override]
    public function setData(array $data): static
    {
        /**
         * @todo Kinda hacky, but works with the concept of form.
         *     Should work embeded in a form group or in a dashboard.
         */
        $data = array_merge($_GET, $data);

        parent::setData($data);

        /** Merge any available presets */
        $data = $this->mergePresets($data);

        parent::setData($data);

        return $this;
    }

    /**
     * Retrieve the default nested widget options.
     */
    public function defaultWidgetData(): array
    {
        return [
            'type'               => 'charcoal/admin/widget/attachment',
            'group'              => $this['group'],
            'attachment_options' => $this['attachmentOptions'],
            'attachable_objects' => $this['attachableObjects'],
            'preset'             => $this['preset'],
            'obj'                => $this->obj(),
            'objType'            => $this->objType()
        ];
    }

    /**
     * Retrieve the widget's ID.
     *
     * @return string
     */
    public function widgetId()
    {
        if (!$this->widgetId) {
            $this->widgetId = 'attachment_widget_' . uniqid();
        }

        return $this->widgetId;
    }

    /**
     * Set the widget's ID.
     *
     * @param string $widgetId The widget identifier.
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
     * @param boolean|string $show Whether to show or hide notes.
     * @return self Chainable
     */
    #[\Override]
    public function setShowNotes($show): static
    {
        $this->showNotesAbove = ($show === 'above');
        parent::setShowNotes($show);

        return $this;
    }

    public function showNotesAbove(): bool
    {
        return $this->showNotesAbove && $this->showNotes();
    }

    /**
     * @param Container $container The DI container.
     * @return void
     */
    #[\Override]
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setWidgetFactory($container['widget/factory']);
        $this->setModelFactory($container['model/factory']);

        if (isset($container['attachments/config'])) {
            $this->setConfig($container['attachments/config']);
        } elseif (isset($container['config']['attachments'])) {
            $this->setConfig($container['config']['attachments']);
        }

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

<?php

namespace Charcoal\Admin\Widget;

use ArrayIterator;
use RuntimeException;
use InvalidArgumentException;

// From Pimple
use Pimple\Container;

// From 'bobthecow/mustache.php'
use Mustache_LambdaHelper as LambdaHelper;

// From 'charcoal-config'
use Charcoal\Config\ConfigurableInterface;

// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

// From 'charcoal-core'
use Charcoal\Loader\CollectionLoader;
use Charcoal\Model\ModelFactory;

// From 'charcoal-translator'
use Charcoal\Translator\Translation;

// From 'charcoal-admin'
use Charcoal\Admin\AdminWidget;
use Charcoal\Admin\Ui\ObjectContainerInterface;
use Charcoal\Admin\Ui\ObjectContainerTrait;

// From 'beneroch/charcoal-attachments'
use Charcoal\Attachment\Interfaces\AttachmentContainerInterface;
use Charcoal\Attachment\Traits\ConfigurableAttachmentsTrait;

/**
 *
 */
class AttachmentWidget extends AdminWidget implements
    ConfigurableInterface,
    ObjectContainerInterface
{
    use ConfigurableAttachmentsTrait;
    use ObjectContainerTrait {
        ObjectContainerTrait::createOrLoadObj as createOrCloneOrLoadObj;
    }

    /**
     * The widget's title.
     *
     * @var Translation|string|null
     */
    private $title;

    /**
     * The group identifier.
     *
     * The group is used to create multiple widget instance on the same page.
     *
     * @var string
     */
    protected $group;

    /**
     * Whether the widget is editable or only viewable.
     *
     * @var boolean
     */
    private $readOnly = false;

    /**
     * The attachment heading (property or template).
     *
     * @var Translation|string|null
     */
    protected $attachmentHeading;

    /**
     * The attachment preview  (property or template).
     *
     * @var Translation|string|null
     */
    protected $attachmentPreview;

    /**
     * Flag wether the attachment heading should be displayed.
     *
     * @var boolean
     */
    private $showAttachmentHeading = true;

    /**
     * Flag wether the attachment preview should be displayed.
     *
     * @var boolean
     */
    private $showAttachmentPreview = false;

    /**
     * The widgets's available attachment types.
     *
     * @var array
     */
    protected $attachableObjects;

    /**
     * Track the state of data merging.
     *
     * @var boolean
     */
    private $isMergingData = false;

    /**
     * Store the factory instance for the current class.
     *
     * @var FactoryInterface
     */
    private $widgetFactory;

    /**
     * Inject dependencies from a DI Container.
     *
     * @param Container $container A dependencies container instance.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setWidgetFactory($container['widget/factory']);

        if (isset($container['attachments/config'])) {
            $this->setConfig($container['attachments/config']);
        } elseif (isset($container['config']['attachments'])) {
            $this->setConfig($container['config']['attachments']);
        }
    }

    /**
     * Create or load the object.
     *
     * @return ModelInterface
     */
    protected function createOrLoadObj()
    {
        $obj = $this->createOrCloneOrLoadObj();

        $obj->setData([
            'attachment_widget' => $this
        ]);

        return $obj;
    }

    /**
     * Attachment types with their collections.
     *
     * @return array
     */
    public function attachmentTypes()
    {
        $attachableObjects = $this->attachableObjects();

        $out = [];

        if (!$attachableObjects) {
            return $out;
        }

        $i = 0;
        foreach ($attachableObjects as $attType => $attMeta) {
            $i++;
            $label = $attMeta['label'];

            $out[] = [
                'id'     => (isset($attMeta['att_id']) ? $attMeta['att_id'] : null),
                'ident'  => $this->createIdent($attType),
                'label'  => $label,
                'val'    => $attType,
                'active' => ($i == 1)
            ];
        }

        return $out;
    }

    /**
     * Attachment by groups.
     *
     * @return \Generator
     */
    public function attachments()
    {
        $attachments = $this->obj()->attachments($this->group());

        foreach ($attachments as $attachment) {
            $GLOBALS['widget_template'] = (string)$attachment->rawPreview();

            yield $attachment;

            $GLOBALS['widget_template'] = '';
        }
    }

    /**
     * Determine the number of attachments.
     *
     * @return boolean
     */
    public function hasAttachments()
    {
        return count(iterator_to_array($this->attachments()));
    }

    // Setters
    // =========================================================================

    /**
     * Set the widget's data.
     *
     * @param array $data The widget data.
     * @return self
     */
    public function setData(array $data)
    {
        $this->isMergingData = true;
        /**
         * @todo Kinda hacky, but works with the concept of form.
         *     Should work embeded in a form group or in a dashboard.
         */
        $data = array_merge($_GET, $data);

        parent::setData($data);

        /** Merge any available presets */
        $data = $this->mergePresets($data);

        parent::setData($data);

        $this->isMergingData = false;

        return $this;
    }

    /**
     * Set the attachment's default heading.
     *
     * @param  string $heading The attachment heading template.
     * @throws InvalidArgumentException If provided argument is not of type 'string'.
     * @return self
     */
    public function setAttachmentHeading($heading)
    {
        $this->attachmentHeading = $heading;

        return $this;
    }

    /**
     * Set the attachment's default preview.
     *
     * @param  string $preview The attachment preview template.
     * @throws InvalidArgumentException If provided argument is not of type 'string'.
     * @return self
     */
    public function setAttachmentPreview($preview)
    {
        $this->attachmentPreview = $preview;

        return $this;
    }

    /**
     * Set whether to show a heading for each attached object.
     *
     * @param  boolean $show The show heading flag.
     * @return self
     */
    public function setShowAttachmentHeading($show)
    {
        $this->showAttachmentHeading = !!$show;

        return $this;
    }

    /**
     * Set whether to show a preview for each attached object.
     *
     * @param  boolean $show The show preview flag.
     * @return self
     */
    public function setShowAttachmentPreview($show)
    {
        $this->showAttachmentPreview = !!$show;

        return $this;
    }

    /**
     * Set the widget's attachment grouping.
     *
     * Prevents the relationship from deleting all non related attachments.
     *
     * @param  string $group The group identifier.
     * @throws InvalidArgumentException If the group key is invalid.
     * @return self
     */
    public function setGroup($group)
    {
        if (!is_string($group) && $group !== null) {
            throw new InvalidArgumentException(sprintf(
                'Attachment group must be string, received %s',
                is_object($group) ? get_class($group) : gettype($group)
            ));
        }

        $this->group = $group;

        return $this;
    }

    /**
     * Set whether the widget is read-only.
     *
     * @param  boolean $readOnly The read-only flag.
     * @return self
     */
    public function setReadOnly($readOnly)
    {
        $this->readOnly = !!$readOnly;

        return $this;
    }

    /**
     * Set an widget factory.
     *
     * @param FactoryInterface $factory The factory to create widgets.
     * @return self
     */
    protected function setWidgetFactory(FactoryInterface $factory)
    {
        $this->widgetFactory = $factory;

        return $this;
    }

    /**
     * Set the widget's title.
     *
     * @param  mixed $title The title for the current widget.
     * @return self
     */
    public function setTitle($title)
    {
        $this->title = $this->translator()->translation($title);

        return $this;
    }

    /**
     * Set how many attachments are displayed per page.
     *
     * @param  integer $num The number of results to retrieve, per page.
     * @throws InvalidArgumentException If the parameter is not numeric or < 0.
     * @return self
     */
    public function setNumPerPage($num)
    {
        if (!is_numeric($num)) {
            throw new InvalidArgumentException(
                'Num-per-page needs to be numeric.'
            );
        }

        $num = (int)$num;

        if ($num < 0) {
            throw new InvalidArgumentException(
                'Num-per-page needs to be >= 0.'
            );
        }

        $this->numPerPage = $num;

        return $this;
    }

    /**
     * Set the current page listing of attachments.
     *
     * @param integer $page The current page. Start at 0.
     * @throws InvalidArgumentException If the parameter is not numeric or < 0.
     * @return self
     */
    public function setPage($page)
    {
        if (!is_numeric($page)) {
            throw new InvalidArgumentException(
                'Page number needs to be numeric.'
            );
        }

        $page = (int)$page;

        if ($page < 0) {
            throw new InvalidArgumentException(
                'Page number needs to be >= 0.'
            );
        }

        $this->page = $page;

        return $this;
    }

    /**
     * Set the widget's available attachment types.
     *
     * Specificy the object as a KEY (ident) to whom you
     * can add filters, label and orders.
     *
     * @param  array|AttachableInterface[] $attachableObjects A list of available attachment types.
     * @return self|boolean
     */
    public function setAttachableObjects($attachableObjects)
    {
        if (!$this->isMergingData) {
            $attachableObjects = $this->mergePresetAttachableObjects($attachableObjects);
        }

        if (empty($attachableObjects) || is_string($attachableObjects)) {
            return false;
        }

        $out = [];
        foreach ($attachableObjects as $attType => $attMeta) {
            $label      = '';
            $filters    = [];
            $orders     = [];
            $numPerPage = 0;
            $page       = 1;
            $attOption  = [ 'label', 'filters', 'orders', 'num_per_page', 'page' ];
            $attData    = array_diff_key($attMeta, $attOption);

            // Disable an attachable model
            if (isset($attMeta['active']) && !$attMeta['active']) {
                continue;
            }

            // Useful for replacing a pre-defined attachment type
            if (isset($attMeta['attachment_type'])) {
                $attType = $attMeta['attachment_type'];
            } else {
                $attMeta['attachment_type'] = $attType;
            }

            // Useful for attaching a pre-existing attachment
            $attId = (isset($attMeta['attachment_id']) ? $attMeta['attachment_id'] : null);

            if (isset($attMeta['label'])) {
                $label = $this->translator()->translation($attMeta['label']);
            }

            if (isset($attMeta['filters'])) {
                $filters = $attMeta['filters'];
            }

            if (isset($attMeta['orders'])) {
                $orders = $attMeta['orders'];
            }

            if (isset($attMeta['num_per_page'])) {
                $numPerPage = $attMeta['num_per_page'];
            }

            if (isset($attMeta['page'])) {
                $page = $attMeta['page'];
            }

            $out[$attType] = [
                'att_id'     => $attId,
                'label'      => $label,
                'filters'    => $filters,
                'orders'     => $orders,
                'page'       => $page,
                'numPerPage' => $numPerPage,
                'data'       => $attData
            ];
        }

        $this->attachableObjects = $out;

        return $this;
    }

    // Getters
    // =========================================================================

    /**
     * Retrieve the widget factory.
     *
     * @throws RuntimeException If the widget factory was not previously set.
     * @return FactoryInterface
     */
    public function widgetFactory()
    {
        if (!isset($this->widgetFactory)) {
            throw new RuntimeException(sprintf(
                'Widget Factory is not defined for "%s"',
                get_class($this)
            ));
        }

        return $this->widgetFactory;
    }

    /**
     * Retrieve the attachment's default heading.
     *
     * @return Translation|string|null
     */
    public function attachmentHeading()
    {
        return $this->attachmentHeading;
    }

    /**
     * Retrieve the attachment's default preview.
     *
     * @return Translation|string|null
     */
    public function attachmentPreview()
    {
        return $this->attachmentPreview;
    }

    /**
     * Determine if the widget displays a heading for each attached objects.
     *
     * @return boolean
     */
    public function showAttachmentHeading()
    {
        if (!$this->showAttachmentHeading && !$this->showAttachmentPreview()) {
            return true;
        }

        return $this->showAttachmentHeading;
    }

    /**
     * Determine if the widget displays a preview for each attached objects.
     *
     * @return boolean
     */
    public function showAttachmentPreview()
    {
        return $this->showAttachmentPreview;
    }

    /**
     * Retrieve the widget's attachment grouping.
     *
     * @return string
     */
    public function group()
    {
        if (!$this->group) {
            $this->group = AttachmentContainerInterface::DEFAULT_GROUPING;
        }

        return $this->group;
    }

    /**
     * Determine if the widget is read-only.
     *
     * @return boolean
     */
    public function readOnly()
    {
        return $this->readOnly;
    }

    /**
     * Retrieve the widget's title.
     *
     * @return Translation|string|null
     */
    public function title()
    {
        return $this->title;
    }

    /**
     * Retrieve the widget's available attachment types.
     *
     * @return array
     */
    public function attachableObjects()
    {
        return $this->attachableObjects;
    }

    /**
     * Retrieve the current widget's options as a JSON object.
     *
     * @return string A JSON string.
     */
    public function widgetOptions()
    {
        $options = [
            'attachable_objects'      => $this->attachableObjects(),
            'attachment_heading'      => $this->attachmentHeading(),
            'attachment_preview'      => $this->attachmentPreview(),
            'show_attachment_heading' => ($this->showAttachmentHeading() ? 1 : 0),
            'show_attachment_preview' => ($this->showAttachmentPreview() ? 1 : 0),
            'title'                   => $this->title(),
            'obj_type'                => $this->obj()->objType(),
            'obj_id'                  => $this->obj()->id(),
            'group'                   => $this->group()
        ];

        return json_encode($options, true);
    }

    // Utilities
    // =========================================================================

    /**
     * Generate an HTML-friendly identifier.
     *
     * @param  string $string A dirty string to filter.
     * @return string
     */
    public function createIdent($string)
    {
        return preg_replace('~/~', '-', $string);
    }

    /**
     * Parse the given data and recursively merge presets from attachments config.
     *
     * @param  array $data The widget data.
     * @return array Returns the merged widget data.
     */
    protected function mergePresets(array $data)
    {
        if (isset($data['attachable_objects'])) {
            $data['attachable_objects'] = $this->mergePresetAttachableObjects($data['attachable_objects']);
        }

        if (isset($data['preset'])) {
            $data = $this->mergePresetWidget($data);
        }

        return $data;
    }

    /**
     * Parse the given data and merge the widget preset.
     *
     * @param  array $data The widget data.
     * @return array Returns the merged widget data.
     */
    private function mergePresetWidget(array $data)
    {
        if (!isset($data['preset']) || !is_string($data['preset'])) {
            return $data;
        }

        $widgetIdent = $data['preset'];
        if ($this->hasObj()) {
            $widgetIdent = $this->obj()->render($widgetIdent);
        }

        $presetWidgets = $this->config('widgets');
        if (!isset($presetWidgets[$widgetIdent])) {
            return $data;
        }

        $widgetData = $presetWidgets[$widgetIdent];
        if (isset($widgetData['attachable_objects'])) {
            $widgetData['attachable_objects'] = $this->mergePresetAttachableObjects($widgetData['attachable_objects']);
        }

        return array_replace_recursive($widgetData, $data);
    }
}

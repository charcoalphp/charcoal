<?php

namespace Charcoal\Admin\Widget;

use ArrayIterator;

// Dependencies from Pimple
use \Pimple\Container;

// Dependency from 'charcoal-factory'
use \Charcoal\Factory\FactoryInterface;

// Dependencies from 'charcoal-core'
use \Charcoal\Loader\CollectionLoader;
use \Charcoal\Model\ModelFactory;

// Dependencies from 'charcoal-admin'
use \Charcoal\Admin\AdminWidget;
use \Charcoal\Admin\Ui\ObjectContainerInterface;
use \Charcoal\Admin\Ui\ObjectContainerTrait;

// Dependency from 'charcoal-translation'
use \Charcoal\Translation\TranslationString;

/**
 *
 */
class AttachmentWidget extends AdminWidget implements
    ObjectContainerInterface
{
    use ObjectContainerTrait {
        ObjectContainerTrait::createOrLoadObj as createOrCloneOrLoadObj;
    }

    /**
     * Store the factory instance for the current class.
     *
     * @var FactoryInterface
     */
    protected $widgetFactory;

    /**
     * The group identifier.
     *
     * The group is used to create multiple widget instance on the same page.
     *
     * @var string
     */
    protected $group;

    /**
     * The widget's title.
     *
     * @var TranslationString|string[]
     */
    protected $title;

    /**
     * The attachment preview.
     *
     * @var string
     */
    protected $preview;

    /**
     * Flag wether the attachment previews should be displayed.
     *
     * @var boolean
     */
    private $showPreviews = false;

    /**
     * The widgets's available attachment types.
     *
     * @var array
     */
    protected $attachableObjects;

    /**
     * Inject dependencies from a DI Container.
     *
     * @param Container $container A dependencies container instance.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setModelFactory($container['model/factory']);
        $this->setWidgetFactory($container['widget/factory']);
    }

    /**
     * Create or load the object.
     *
     * @return ModelInterface
     */
    protected function createOrLoadObj()
    {
        $obj = $this->createOrCloneOrLoadObj();

        $obj->setAttachmentWidget($this);

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
     * @return Collection
     */
    public function attachments()
    {
        $attachments = $this->obj()->attachments($this->group());

        foreach ($attachments as $attachment) {
            $GLOBALS['widget_template'] = (string)$attachment->rawPreview();

            yield $attachment;
        }
    }

    /**
     * Determine the number of attachments.
     *
     * @return boolean
     */
    public function hasAttachments()
    {
        return !!count($this->attachments());
    }



// Setters
// =============================================================================

    /**
     * Set the widget's data.
     *
     * @param array|Traversable $data The widget data.
     * @return self
     */
    public function setData($data)
    {
        /**
         * @todo Kinda hacky, but works with the concept of form.
         *     Should work embeded in a form group or in a dashboard.
         */
        $data = array_merge($_GET, $data);

        parent::setData($data);

        return $this;
    }

    /**
     * Set the attachment's default preview.
     *
     * @param  string $preview The attachment preview template.
     * @throws InvalidArgumentException If provided argument is not of type 'string'.
     * @return string
     */
    public function setPreview($preview)
    {
        if (TranslationString::isTranslatable($preview)) {
            $this->preview = new TranslationString($preview);
        } else {
            $this->preview = null;
        }

        return $this;
    }

    /**
     * Set whether to show previews of attached objects.
     *
     * @param boolean $show The show attachment previews flag.
     * @return string
     */
    public function setShowPreviews($show)
    {
        $this->showPreviews = !!$show;

        return $this;
    }

    /**
     * Determine if the widget shows previews of attached objects.
     *
     * @return boolean
     */
    public function showPreviews()
    {
        return $this->showPreviews;
    }

    /**
     * Set the widget's attachment grouping.
     *
     * Prevents the relationship from deleting all non related attachments.
     *
     * @param string $id The group identifier.
     * @return self
     */
    public function setGroup($id)
    {
        $this->group = $id;

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
     * @param mixed $title The title for the current widget.
     * @return self
     */
    public function setTitle($title)
    {
        if (TranslationString::isTranslatable($title)) {
            $this->title = new TranslationString($title);
        } else {
            $this->title = null;
        }

        return $this;
    }

    /**
     * Set how many attachments are displayed per page.
     *
     * @param integer $num The number of results to retrieve, per page.
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
     * @param array|AttachableInterface[] $attachableObjects A list of available attachment types.
     * @return self
     */
    public function setAttachableObjects($attachableObjects)
    {
        if (!$attachableObjects) {
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

            if (isset($attMeta['label'])) {
                if (TranslationString::isTranslatable($attMeta['label'])) {
                    $label = new TranslationString($attMeta['label']);
                }
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
// =============================================================================

    /**
     * Retrieve the widget factory.
     *
     * @throws Exception If the widget factory was not previously set.
     * @return FactoryInterface
     */
    public function widgetFactory()
    {
        if (!isset($this->widgetFactory)) {
            throw new Exception(
                sprintf('Widget Factory is not defined for "%s"', get_class($this))
            );
        }

        return $this->widgetFactory;
    }

    /**
     * Retrieve the attachment's default preview.
     *
     * @return string|null
     */
    public function preview()
    {
        return $this->preview;
    }

    /**
     * Retrieve the widget's attachment grouping.
     *
     * @return string
     */
    public function group()
    {
        if (!$this->group) {
            $this->group = 'generic';
        }
        return $this->group;
    }

    /**
     * Retrieve the widget's title.
     *
     * @return TranslationString|string[]
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
        if (!$this->attachableObjects) {
            return false;
        }

        return $this->attachableObjects;
    }

    /**
     * Retrieve the current widget's options as a JSON object.
     *
     * @return string A JSON string.
     */
    public function widgetOptions()
    {
        $out = [
            'attachable_objects' => $this->attachableObjects(),
            'title'              => $this->title(),
            'preview'            => $this->preview(),
            'obj_type'           => $this->obj()->objType(),
            'obj_id'             => $this->obj()->id(),
            'group'              => $this->group()
        ];

        return json_encode($out, true);
    }



// Utilities
// =============================================================================

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
     * Determine if the widget has an object assigned to it.
     *
     * @return boolean
     */
    public function hasObj()
    {
        return !!($this->obj()->id());
    }
}

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
    use ObjectContainerTrait;

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
        foreach ($attachableObjects as $k => $val) {
            $i++;
            $label = $val['label'];

            $out[] = [
                'ident'  => $this->createIdent($k),
                'label'  => $label,
                'val'    => $k,
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
        $group = $this->group();

        return $this->obj()->attachments($group);
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
        $this->title = $this->translatable($title);

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
        foreach ($attachableObjects as $k => $opts) {
            $label      = '';
            $filters    = [];
            $orders     = [];
            $numPerPage = 0;
            $page       = 1;

            if (isset($opts['label'])) {
                $label = $this->translatable($opts['label']);
            }

            if (isset($opts['filters'])) {
                $filters = $opts['filters'];
            }

            if (isset($opts['orders'])) {
                $orders = $opts['orders'];
            }

            if (isset($opts['num_per_page'])) {
                $numPerPage = $opts['num_per_page'];
            }

            if (isset($opts['page'])) {
                $page = $opts['page'];
            }

            $out[$k] = [
                'label'      => $label,
                'filters'    => $filters,
                'orders'     => $orders,
                'page'       => $page,
                'numPerPage' => $numPerPage
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
     * Parse the property value as a "L10N" value type.
     *
     * @param  mixed $val The value being localized.
     * @return TranslationString|null
     */
    public function translatable($val)
    {
        if (
            !isset($val) ||
            (is_string($val) && !strlen(trim($val))) ||
            (is_array($val) && !count(array_filter($val, 'strlen')))
        ) {
            return null;
        }

        return new TranslationString($val);
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

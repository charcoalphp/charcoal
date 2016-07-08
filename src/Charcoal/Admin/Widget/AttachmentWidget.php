<?php
// Namespace
namespace Charcoal\Admin\Widget;

use ArrayIterator;

// Dependencies from `pimple`
use \Pimple\Container;

// From Charcoal\App
use Charcoal\App\AppConfig;

// From Charcoal\Core
use \Charcoal\Loader\CollectionLoader;
use \Charcoal\Model\ModelFactory;

// From Charcoal\Admin
use \Charcoal\Admin\AdminWidget;
use \Charcoal\Admin\Ui\ObjectContainerInterface;
use \Charcoal\Admin\Ui\ObjectContainerTrait;

// From Charcoal\Translation
use \Charcoal\Translation\TranslationString;


class AttachmentWidget extends AdminWidget implements
    ObjectContainerInterface
{
    use ObjectContainerTrait;

    /**
     * AppConfig
     * @var Charcoal\App\AppConfig $appConfig
     */
    protected $appConfig;

    /**
     * WidgetFactory instance.
     * @var FactoryInterface $widgetFactory;
     */
    protected $widgetFactory;

    /**
     * Group ident.
     * The group is used to create multiple widget
     * instance on the same page.
     * @var string $group
     */
    protected $group;

    /**
     * TranslationString
     * @see TranslationAwareTrait
     * @var TranslationString $title
     */
    protected $title;

    /**
     * attachableObjects
     * Option for the widget
     * @var Array
     */
    protected $attachableObjects;

    /**
     * @param Container $container The DI container.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        // Fill ObjectContainerInterface dependencies
        $this->setModelFactory($container['model/factory']);

        $this->setWidgetFactory($container['widget/factory']);
        $this->setAppConfig($container['config']);
    }

    /**
     * Attachment types with their collection
     *
     * @return Array All attachment types
     */
    public function attachmentTypes()
    {
        $attachableObjects = $this->attachableObjects();
        $out = [];

        if (!$attachableObjects) {
            return $out;
        }

        $i = 0;
        foreach ($attachableObjects as $k => $val)
        {
            $i++;
            $label = $val['label'];

            $out[] = [
                'ident' => $this->createIdent($k),
                'label' => $label,
                'val' => $k,
                'active' => ($i == 1)
            ];
        }

        return $out;
    }

    /**
     * Attachment by groups.
     * @return Collection Attachments.
     */
    public function attachments()
    {
        $group = $this->group();
        return $this->obj()->attachments($group);
    }

    /**
     * Count of attachments as boolean
     * @return boolean Has attachments or not.
     */
    public function hasAttachments()
    {
        return !!(count($this->attachments()));
    }

/**
 * SETTERS
 */
    /**
     * @param array|ArrayInterface $data The widget data.
     * @return WidgetInterface Chainable.
     */
    public function setData($data)
    {
        // Kinda hacky, but works with the concept of form.
        // Should work embeded in a form group or in a dashboard.
        $data = array_merge($_GET, $data);
        parent::setData($data);
        return $this;
    }

    /**
     * Attachment groups
     * Prevents the join from deleting all non related attachments.
     * @param string $ident Group ident.
     * @return WidgetInterface Chainable.
     */
    public function setGroup($ident)
    {
        $this->group = $ident;
        return $this;
    }

    /**
     * Set widget factory
     * @param FactoryInterface $factory WidgetFactory.
     * @return WidgetInterface Chainable.
     */
    public function setWidgetFactory($factory)
    {
        $this->widgetFactory = $factory;
        return $this;
    }

    /**
     * Set app configurations.
     * @param AppConfig $config Application configurations.
     * @return WidgetInterface Chainable.
     */
    public function setAppConfig($config)
    {
        $this->appConfig = $config;
        return $this;
    }

    /**
     * Current Title as defined in the widget options
     * Title is l10n
     *
     * @param Mixed $title Title
     */
    public function setTitle($title)
    {
        $this->title = $this->translatable($title);
        return $this;
    }

    public function setNumPerPage($num)
    {
        $this->numPerPage = $num;
        return $this;
    }
    public function setPage($page)
    {
        $this->page = $page;
        return $this;
    }

    /**
     * Set in the widget options
     * Attachments must be attachable (@see AttachableInterface)
     * Specificy the object as a KEY (ident) to whom you
     * can add filters, label and orders.
     *
     * @param Array $attachableObjects From the object metadata.
     */
    public function setAttachableObjects($attachableObjects)
    {
        if (!$attachableObjects) {
            return false;
        }

        $out = [];
        foreach ($attachableObjects as $k => $opts) {
            $label = '';
            $filters = [];
            $orders = [];
            $numPerPage = 0;
            $page = 1;

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
                'label' => $label,
                'filters' => $filters,
                'orders' => $orders,
                'page' => $page,
                'numPerPage' => $numPerPage
            ];
        }

        $this->attachableObjects = $out;
    }


/**
 * GETTERS
 */
    /**
     * App configurations.
     * @return AppConfig Application configurations.
     */
    public function appConfig()
    {
        return $this->appConfig;
    }

    /**
     * WidgetFactory instance.
     * @return FactoryInterface Widget factory.
     */
    public function widgetFactory()
    {
        return $this->widgetFactory;
    }

    /**
     * Group ident.
     * @return string Group ident.
     */
    public function group()
    {
        if (!$this->group) {
            $this->group = 'generic';
        }
        return $this->group;
    }

    /**
     * Title
     *
     * @return [type] [description]
     */
    public function title()
    {
        return $this->title;
    }

    /**
     * Parsed attachableObjects
     * Data from the widget options, set with setData.
     *
     * @return Array              [description]
     */
    public function attachableObjects()
    {
        if (!$this->attachableObjects) {
            return false;
        }
        return $this->attachableObjects;
    }

    /**
     * From the object metadata. Output in the
     * HTML for javascript purposes.
     * @return Array Widget Options parsed.
     */
    public function widgetOptions()
    {
        $out = [
            'attachable_objects' => $this->attachableObjects(),
            'title' => $this->title(),
            'obj_type' => $this->obj()->objType(),
            'obj_id' => $this->obj()->id(),
            'group' => $this->group()
        ];

        return json_encode($out, true);
    }

/**
 * UTILS
 */
    /**
     * Remove slashes to create a HTML friendly ID
     *
     * @param  String $string Expects an object type
     * @return String         Ident
     */
    public function createIdent($string)
    {
        return preg_replace('~/~', '-', $string);
    }

    /**
     * TranslationString with the given text value.
     * @see \Charcoal\Translation\TranslationString.
     * @param  Mixed $txt Translatable text OR array.
     * @return TranslationString      Translatable content using the current language.
     */
    public function translatable($txt)
    {
        return new TranslationString($txt);
    }

    /**
     * Check if the widget has a object.
     * @return boolean Does the widget has a object?.
     */
    public function hasObj()
    {
        return !!($this->obj()->id());
    }

}

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


class AddAttachmentWidget extends AdminWidget implements
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
	 * TranslationString
	 * @see TranslationAwareTrait
	 * @var TranslationString $title
	 */
	protected $title;

	/**
	 * Attachments
	 * Option for the widget
	 * @var Array
	 */
	protected $attachments;

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
		$attachments = $this->attachments();
		$out = [];
		$i = 0;
		foreach ($attachments as $k => $val)
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
 * SETTERS
 */
    /**
     * @param array|ArrayInterface $data The widget data.
     * @return WidgetInterface Chainable.
     */
    public function setData($data)
    {
        $data = array_merge($_GET, $data);
        parent::setData($data);
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
     * @param Array $attachments From the object metadata.
     */
    public function setAttachments($attachments)
    {
        if (!$attachments) {
            return false;
        }

        $out = [];
        foreach ($attachments as $k => $opts) {
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

        $this->attachments = $out;
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
     * Title
     *
     * @return [type] [description]
     */
    public function title()
    {
        return $this->title;
    }

    /**
     * Parsed attachments
     * Data from the widget options, set with setData.
     *
     * @return Array              [description]
     */
    public function attachments()
    {
        if (!$this->attachments) {
            return false;
        }
        return $this->attachments;
    }

    /**
     * From the object metadata. Output in the
     * HTML for javascript purposes.
     * @return Array Widget Options parsed.
     */
    public function widgetOptions()
    {
        $out = [
            'attachments' => $this->attachments(),
            'title' => $this->title(),
            'obj_type' => $this->obj()->objType(),
            'obj_id' => $this->obj()->id()
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

<?php

namespace Charcoal\Admin\Widget;

use ArrayIterator;
use RuntimeException;
use InvalidArgumentException;
// From Pimple
use Pimple\Container;
// From Mustache
use Mustache_LambdaHelper as LambdaHelper;
// From 'charcoal-config'
use Charcoal\Config\ConfigurableInterface;
// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;
// From 'charcoal-core'
use Charcoal\Loader\CollectionLoader;
use Charcoal\Model\ModelInterface;
// From 'charcoal-translator'
use Charcoal\Translator\Translation;
// From 'charcoal-admin'
use Charcoal\Admin\AdminWidget;
use Charcoal\Admin\Ui\ObjectContainerInterface;
use Charcoal\Admin\Ui\ObjectContainerTrait;
// From 'charcoal-attachment'
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
     * The widget's active lang.
     *
     * @var string $lang
     */
    protected $lang;

    /**
     * The group identifier.
     *
     * The group is used to create multiple widget instance on the same page.
     *
     * @var string
     */
    protected $group;

    /**
     * The widget's available attachment types.
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
     * Font-awesome icons for each of the default attachment types.
     *
     * @var array
     */
    protected $defaultIcons = [
        'embed'     => 'window-maximize',
        'video'     => 'video-camera',
        'image'     => 'picture-o',
        'file'      => 'file',
        'link'      => 'link',
        'text'      => 'paragraph',
        'gallery'   => 'camera',
        'container' => 'object-group',
        'accordion' => 'list',
        'quote'     => 'quote-right',
        'resources' => 'paperclip'
    ];

    /**
     * @var array
     */
    private $attachmentOptions;

    /**
     * Inject dependencies from a DI Container.
     *
     * @param Container $container A dependencies container instance.
     * @return void
     */
    protected function setDependencies(Container $container)
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
     * Determine if the widget has any attachment types.
     *
     * @return boolean
     */
    public function hasAttachmentTypes()
    {
        return !empty($this->attachableObjects());
    }

    /**
     * Retrieve the attachment types with their collections.
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
                'id'             => (isset($attMeta['att_id']) ? $attMeta['att_id'] : null),
                'ident'          => $this->createIdent($attType),
                'skipForm'       => $attMeta['skipForm'],
                'formIdent'      => $attMeta['formIdent'],
                'quickFormIdent' => $attMeta['quickFormIdent'],
                'hasFaIcon'      => !!$attMeta['faIcon'],
                'faIcon'         => $attMeta['faIcon'],
                'label'          => $label,
                'val'            => $attType,
                'locked'         => $attMeta['locked'],
                'active'         => ($i == 1)
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
        $attachableObjects = $this->attachableObjects();
        $attachments       = $this->obj()->getAttachments([
            'group' => $this->group(),
            // If we ever need to remove the inactive attachments from the back-end display, this
            // is where we will need to add additional configurations.  This is hardcoded to
            // "false" because it's better than assuming the widget type in the AttachmentAwareTrait.
            'isActive' => false
        ]);

        foreach ($attachments as $attachment) {
            $this->setDynamicTemplate('widget_template', (string)$attachment->rawPreview());

            if (isset($attachableObjects[$attachment->objType()])) {
                $attachment->attachmentType = $attachableObjects[$attachment->objType()];
            } else {
                continue;
            }

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
        return count(iterator_to_array($this->attachments()));
    }

    /**
     * The default set of settings for attachment widget.
     *
     * @return array
     */
    public function defaultAttachmentOptions()
    {
        return [
            'header'       => null,
            'preview'      => null,
            'show_header'  => true,
            'show_preview' => false,
            'read_only'    => false,
            'show_icon'    => true
        ];
    }

    /**
     * Add (or replace) an attachment options.
     *
     * @param string $key The setting to add/replace.
     * @param mixed  $val The settings's value to apply.
     * @throws InvalidArgumentException If the identifier is not a string.
     * @return self
     */
    public function addAttachmentOption($key, $val)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException(
                'Setting key must be a string.'
            );
        }

        // Make sure default options are loaded.
        if ($this->attachmentOptions === null) {
            $this->attachmentOptions();
        }

        $this->attachmentOptions[$key] = $val;

        return $this;
    }

    /**
     * Add (or replace) an attachment options.
     *
     * @param string $key The setting to get.
     * @throws InvalidArgumentException If the identifier is not a string.
     * @return mixed|null
     */
    public function attachmentOption($key)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException(
                'Setting key must be a string.'
            );
        }

        // Make sure default options are loaded.
        if ($this->attachmentOptions === null) {
            $this->attachmentOptions();
        }

        if (isset($this->attachmentOptions[$key])) {
            return $this->attachmentOptions[$key];
        }

        return null;
    }

    /**
     * Merge (replacing or adding) attachment options.
     *
     * @param array $settings The attachment options.
     * @return self
     */
    public function mergeAttachmentOptions(array $settings)
    {
        // Make sure default options are loaded.
        if ($this->attachmentOptions === null) {
            $this->attachmentOptions();
        }

        $this->attachmentOptions = array_merge(
            $this->attachmentOptions,
            $this->parseAttachmentOptions($settings)
        );

        return $this;
    }

    /**
     * @param array $settings The Attachment options.
     * @return array Returns the parsed options.
     */
    protected function parseAttachmentOptions(array $settings)
    {
        if (isset($settings['show_header']) && isset($settings['show_preview'])) {
            if (!$settings['show_header'] && !$settings['show_preview']) {
                $settings['show_header'] = true;
            }
        }

        return $settings;
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

        if ($this->lang()) {
            $this->translator()->setLocale($this->lang());
        }

        return $this;
    }

    /**
     * Set the origin language.
     *
     * @param  string $lang The language code.
     * @throws InvalidArgumentException If the argument is not a string.
     * @return UserDataInterface Chainable
     */
    public function setLang($lang)
    {
        if ($lang !== null) {
            if (!is_string($lang)) {
                throw new InvalidArgumentException(
                    'Language must be a string'
                );
            }
        }

        $this->lang = $lang;

        return $this;
    }

    /**
     * Set the attachment widget settings.
     *
     * @param array $settings Attachments options array.
     * @return self
     */
    public function setAttachmentOptions(array $settings)
    {
        $this->attachmentOptions = array_merge(
            $this->defaultAttachmentOptions(),
            $this->parseAttachmentOptions($settings)
        );

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
            $label          = '';
            $filters        = [];
            $orders         = [];
            $numPerPage     = 0;
            $page           = 1;
            $skipForm       = 0;
            $formIdent      = null;
            $quickFormIdent = null;
            $faIcon         = '';
            $showIcon       = 1;
            $locked         = 0;
            $attOption      = ['label', 'filters', 'orders', 'num_per_page', 'page'];
            $attData        = array_diff_key($attMeta, $attOption);

            // Disable an attachable model
            if (isset($attMeta['active'])) {
                if (is_string($attMeta['active'])) {
                    $attMeta['active'] = $this->obj()->render($attMeta['active']);
                }
                if (!$attMeta['active']) {
                    continue;
                }
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

            if (isset($attMeta['skip_form'])) {
                $skipForm = $attMeta['skip_form'];
            }

            if (isset($attMeta['form_ident'])) {
                $formIdent = $attMeta['form_ident'];
            }

            $quickFormIdent = !$quickFormIdent && isset($attMeta['quick_form_ident'])
                ? $attMeta['quick_form_ident'] : $quickFormIdent;
            $quickFormIdent = !$quickFormIdent && isset($attMeta['quickFormIdent'])
                ? $attMeta['quickFormIdent'] : $quickFormIdent;

            if (isset($attMeta['fa_icon']) && !empty($attMeta['fa_icon'])) {
                $faIcon = $attMeta['fa_icon'];
                if (!is_array($faIcon)) {
                    $faIcon = [$faIcon];
                }

                foreach ($faIcon as &$icon) {
                    $icon = 'fa fa-' . $icon;
                }
            } else {
                $attParts = explode('/', $attType);
                if (isset($this->defaultIcons[end($attParts)])) {
                    $faIcon = 'fa fa-' . $this->defaultIcons[end($attParts)];
                }
            }

            if (isset($attMeta['show_icon'])) {
                $showIcon = !!$attMeta['show_icon'];
            }

            if (isset($attMeta['locked'])) {
                $locked = $attMeta['locked'];

                if (is_string($locked)) {
                    $locked = $this->obj()->render($locked);
                }
            }

            $out[$attType] = [
                'att_id'           => $attId,
                'label'            => $label,
                'skipForm'         => $skipForm,
                'formIdent'        => $formIdent,
                'quickFormIdent'   => $quickFormIdent,
                'hasFaIcon'        => !!$faIcon,
                'faIcon'           => $faIcon,
                'showIcon'         => $showIcon,
                'filters'          => $filters,
                'orders'           => $orders,
                'page'             => $page,
                'numPerPage'       => $numPerPage,
                'locked'           => $locked,
                'data'             => $attData
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
     * Retrieve the language.
     *
     * @return string
     */
    public function lang()
    {
        return $this->lang;
    }

    /**
     * Retrieve the attachment options.
     *
     * @return array
     */
    public function attachmentOptions()
    {
        if ($this->attachmentOptions === null) {
            $this->attachmentOptions = $this->defaultAttachmentOptions();
        }

        return $this->attachmentOptions;
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
     * @return array
     */
    public function widgetDataForJs()
    {
        return [
            'obj_type' => $this->obj()->objType(),
            'obj_id'   => $this->obj()->id(),
            'group'    => $this->group(),
        ];
    }

    /**
     * Retrieve the widget's options.
     *
     * @return array
     */
    public function widgetOptions()
    {
        return [
            'obj_type'           => $this->obj()->objType(),
            'obj_id'             => $this->obj()->id(),
            'group'              => $this->group(),
            'attachment_options' => $this->attachmentOptions(),
            'attachable_objects' => $this->attachableObjects(),
            'title'              => (string)$this->title(),
            'lang'               => $this->translator()->getLocale(),
        ];
    }

    /**
     * Converts the widget's {@see self::widgetOptions() options} as a JSON string.
     *
     * @return string Returns data serialized with {@see json_encode()}.
     */
    final public function widgetOptionsAsJson()
    {
        $options = (JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($this->debug()) {
            $options = ($options | JSON_PRETTY_PRINT);
        }

        return json_encode($this->widgetOptions(), $options);
    }

    /**
     * Converts the widget's {@see self::widgetOptions() options} as a JSON string, protected from Mustache.
     *
     * @return string Returns a stringified JSON object, protected from Mustache rendering.
     */
    final public function escapedWidgetOptionsAsJson()
    {
        return '{{=<% %>=}}' . $this->widgetOptionsAsJson() . '<%={{ }}=%>';
    }

    /**
     * @return array
     */
    public function defaultIcons()
    {
        return $this->defaultIcons;
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
}

<?php

namespace Charcoal\Admin\Widget;

use \ArrayIterator;
use \RuntimeException;
use \InvalidArgumentException;

use \Pimple\Container;

// From 'bobthecow/mustache.php'
use \Mustache_LambdaHelper as LambdaHelper;

// From 'charcoal-factory'
use \Charcoal\Factory\FactoryInterface;

// From 'charcoal-core'
use \Charcoal\Loader\CollectionLoader;
use \Charcoal\Model\ModelFactory;

// From 'charcoal-admin'
use \Charcoal\Admin\AdminWidget;
use \Charcoal\Admin\Ui\ObjectContainerInterface;
use \Charcoal\Admin\Ui\ObjectContainerTrait;

// From 'charcoal-translation'
use \Charcoal\Translation\TranslationString;

// From 'beneroch/charcoal-attachments'
use \Charcoal\Attachment\Interfaces\AttachmentContainerInterface;

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
    private $widgetFactory;

    /**
     * The widget's title.
     *
     * @var TranslationString|string[]
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
     * The attachment heading (property or template).
     *
     * @var string[]|string
     */
    protected $attachmentHeading;

    /**
     * The attachment preview  (property or template).
     *
     * @var string[]|string
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
        return count(iterator_to_array($this->attachments()));
    }

    /**
     * Retrieves a Closure that prepends relative URIs with the project's base URI.
     *
     * @return callable
     */
    public function withBaseUrl()
    {
        static $search;

        if ($search === null) {
            $attr = [ 'href', 'link', 'url', 'src' ];
            $uri  = [ '../', './', '/', 'data', 'fax', 'file', 'ftp', 'geo',
                      'http', 'mailto', 'sip', 'tag', 'tel', 'urn' ];

            $search = sprintf(
                '(?<=%1$s=["\'])(?!%2$s)(\S+)(?=["\'])',
                implode('=["\']|', array_map('preg_quote', $attr, [ '~' ])),
                implode('|', array_map('preg_quote', $uri, [ '~' ]))
            );
        }

        /**
         * Prepend the project's base URI to all relative URIs in HTML attributes (e.g., src, href).
         *
         * @param  string       $text   Text to parse.
         * @param  LambdaHelper $helper For rendering strings in the current context.
         * @return string
         */
        $lambda = function ($text, LambdaHelper $helper) use ($search) {
            $text = $helper->render($text);

            if (preg_match('~'.$search.'~i', $text)) {
                $base = $helper->render('{{ baseUrl }}');
                return preg_replace('~'.$search.'~i', $base.'$1', $text);
            }
            // @codingStandardsIgnoreStart
            /* elseif ($this->baseUrl instanceof \Psr\Http\Message\UriInterface) {
                if ($text && strpos($text, ':') === false && !in_array($text[0], [ '/', '#', '?' ])) {
                    return $this->baseUrl->withPath($text);
                }
            }*/
            // @codingStandardsIgnoreEnd

            return $text;
        };
        $lambda = $lambda->bindTo($this);

        return $lambda;
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
     * Set the attachment's default heading.
     *
     * @param  string $heading The attachment heading template.
     * @throws InvalidArgumentException If provided argument is not of type 'string'.
     * @return string[]|string
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
     * @return string[]|string
     */
    public function setAttachmentPreview($preview)
    {
        $this->attachmentPreview = $preview;

        return $this;
    }

    /**
     * Set whether to show a heading for each attached object.
     *
     * @param boolean $show The show heading flag.
     * @return string
     */
    public function setShowAttachmentHeading($show)
    {
        $this->showAttachmentHeading = !!$show;

        return $this;
    }

    /**
     * Set whether to show a preview for each attached object.
     *
     * @param boolean $show The show preview flag.
     * @return string
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
     * @throws RuntimeException If the widget factory was not previously set.
     * @return FactoryInterface
     */
    public function widgetFactory()
    {
        if (!isset($this->widgetFactory)) {
            throw new RuntimeException(
                sprintf('Widget Factory is not defined for "%s"', get_class($this))
            );
        }

        return $this->widgetFactory;
    }

    /**
     * Retrieve the attachment's default heading.
     *
     * @return string|null
     */
    public function attachmentHeading()
    {
        return $this->attachmentHeading;
    }

    /**
     * Retrieve the attachment's default preview.
     *
     * @return string|null
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
        if ($this->attachableObjects === null) {
            $metadata = $this->obj()->metadata();

            if (isset($metadata['attachments']['attachable_objects'])) {
                $this->setAttachableObjects($metadata['attachments']['attachable_objects']);
            } else {
                $this->attachableObjects = [];
            }
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
        $options = [
            'attachable_objects'      => $this->attachableObjects(),
            'attachment_heading'      => $this->attachmentHeading(),
            'attachment_preview'      => $this->attachmentPreview(),
            'show_attachment_heading' => ( $this->showAttachmentHeading() ? 1 : 0 ),
            'show_attachment_preview' => ( $this->showAttachmentPreview() ? 1 : 0 ),
            'title'                   => $this->title(),
            'obj_type'                => $this->obj()->objType(),
            'obj_id'                  => $this->obj()->id(),
            'group'                   => $this->group()
        ];

        return json_encode($options, true);
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

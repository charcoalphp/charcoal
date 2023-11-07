<?php

namespace Charcoal\Attachment\Object;

use Charcoal\Model\ModelInterface;
use Exception;
use ReflectionClass;
use RuntimeException;
use InvalidArgumentException;
// From PSR-7
use Psr\Http\Message\UriInterface;
// From Pimple
use Pimple\Container;
// From 'charcoal-core'
use Charcoal\Loader\CollectionLoader;
// From 'charcoal-object'
use Charcoal\Object\Content;
// From 'charcoal-translator'
use Charcoal\Translator\Translation;
// From 'charcoal-attachment'
use Charcoal\Attachment\Interfaces\AttachableInterface;
use Charcoal\Attachment\Interfaces\AttachmentContainerInterface;
use Charcoal\Attachment\Object\File;
use Charcoal\Attachment\Object\Image;
use Charcoal\Attachment\Object\Text;
use Charcoal\Attachment\Object\Embed;
use Charcoal\Attachment\Object\Video;
use Charcoal\Attachment\Object\Gallery;
use Charcoal\Attachment\Object\Accordion;
use Charcoal\Attachment\Object\Link;
use Charcoal\Attachment\Object\Container as AttachmentContainer;
use Charcoal\Attachment\Object\Join;

/**
 *
 */
class Attachment extends Content implements AttachableInterface
{
    /**
     * Default attachment types
     */
    public const FILE_TYPE      = File::class;
    public const LINK_TYPE      = Link::class;
    public const IMAGE_TYPE     = Image::class;
    public const EMBED_TYPE     = Embed::class;
    public const VIDEO_TYPE     = Video::class;
    public const TEXT_TYPE      = Text::class;
    public const GALLERY_TYPE   = Gallery::class;
    public const ACCORDION_TYPE = Accordion::class;
    public const CONTAINER_TYPE = AttachmentContainer::class;

    /**
     * The attachment type.
     *
     * @var string
     */
    protected $type;

    /**
     * The attachment heading template.
     *
     * @var Translation|string|null
     */
    protected $heading;

    /**
     * The attachment preview template.
     *
     * @var Translation|string|null
     */
    protected $preview;

    /**
     * Whether to show the title on the front-end.
     *
     * @var boolean
     */
    protected $showTitle = true;

    /**
     * @var string|string[]
     */
    protected $categories;

    /**
     * Generic information about the attachment.
     *
     * @var Translation|string|null $title       The title of the attachment.
     * @var Translation|string|null $subtitle    The subtitle of the attachment.
     * @var Translation|string|null $description The content of the attachment.
     * @var Translation|string|null $keywords    Keywords finding the attachment.
     */
    protected $title;
    protected $subtitle;
    protected $description;
    protected $keywords;

    /**
     * File related attachments.
     *
     * @var string  $file      The path of an attached file.
     * @var string  $fileLabel The label for the attached file.
     * @var integer $fileSize  The size of the attached file in bytes.
     * @var string  $fileType  The content type of the attached file.
     */
    protected $file;
    protected $fileLabel;
    protected $fileSize;
    protected $fileType;

    /**
     * Link related attachments.
     *
     * @var string $link      The URL related to the attachment.
     * @var string $linkLabel The label for the attached link.
     */
    protected $link;
    protected $linkLabel;

    /**
     * Path to a thumbnail of the attached file.
     *
     * Auto-generated thumbnail if the attached file is an image.
     *
     * @var Translation|string|null
     */
    protected $thumbnail;

    /**
     * Embedded content.
     *
     * @var Translation|string|null
     */
    protected $embed;

    /**
     * The attachment's position amongst other attachments.
     *
     * @var integer
     */
    protected $position;

    /**
     * The base URI.
     *
     * @var UriInterface|null
     */
    private $baseUrl;

    /**
     * Whether the attachment acts like a presenter (TRUE) or data model (FALSE).
     *
     * @var boolean
     */
    private $presentable = false;

    /**
     * The attachment's parent container instance.
     *
     * @var AttachmentContainerInterface|null
     */
    protected $containerObj;

    /**
     * A store of resolved attachment types.
     *
     * @var array
     */
    protected static $resolvedType = [];

    /**
     * Store the collection loader for the current class.
     *
     * @var CollectionLoader
     */
    private $collectionLoader;

    /**
     * @var ModelInterface $presenter
     */
    private $presenter;

    /**
     * Return a new section object.
     *
     * @param array $data Dependencies.
     */
    public function __construct(array $data = null)
    {
        parent::__construct($data);

        if (is_callable([ $this, 'defaultData' ])) {
            $defaultData = $this->metadata()->defaultData();
            if ($defaultData) {
                $this->setData($defaultData);
            }
        }
    }

    /**
     * Inject dependencies from a DI Container.
     *
     * @param  Container $container A dependencies container instance.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setBaseUrl($container['base-url']);
        $this->setCollectionLoader($container['model/collection/loader']);
    }

    /**
     * Determine if the model is for presentation or editing.
     *
     * @param  boolean $presenter The presenter flag.
     * @return boolean Returns TRUE if model is used for presentation; FALSE for editing.
     */
    public function isPresentable($presenter = null)
    {
        if (is_bool($presenter)) {
            $this->presentable = $presenter;
        }

        return $this->presentable;
    }

    /**
     * Retrieve the attachment's container ID (if any).
     *
     * Useful when templating a container of nested attachments.
     *
     * @return mixed|null
     */
    public function containerId()
    {
        $container = $this->containerObj();
        if ($container) {
            return $container->id();
        }

        return null;
    }

    /**
     * Determine if the attachment belongs to a container.
     *
     * @return boolean
     */
    public function hasContainerObj()
    {
        return boolval($this->containerObj);
    }

    /**
     * Retrieve the attachment's container instance.
     *
     * @return AttachmentContainerInterface|null
     */
    public function containerObj()
    {
        return $this->containerObj;
    }

    /**
     * Set the attachment's container instance.
     *
     * @param  AttachmentContainerInterface|null $obj The container object or NULL.
     * @throws InvalidArgumentException If the given object is invalid.
     * @return Attachment
     */
    public function setContainerObj($obj)
    {
        if ($obj === null) {
            $this->containerObj = null;

            return $this;
        }

        if (!$obj instanceof AttachmentContainerInterface) {
            throw new InvalidArgumentException(sprintf(
                'Container object must be an instance of %s; received %s',
                AttachmentContainerInterface::class,
                (is_object($obj) ? get_class($obj) : gettype($obj))
            ));
        }

        if (!$obj->id()) {
            throw new InvalidArgumentException(sprintf(
                'Container object must have an ID.',
                (is_object($obj) ? get_class($obj) : gettype($obj))
            ));
        }

        $this->containerObj = $obj;

        return $this;
    }

    /**
     * Retrieve the attachment type.
     *
     * @return string
     */
    public function type()
    {
        if (!$this->type) {
            $this->type = $this->objType();
        }

        return $this->type;
    }

    /**
     * Set the attachment type.
     *
     * @param  string $type The attachment type.
     * @throws InvalidArgumentException If provided argument is not of type 'string'.
     * @return string
     */
    public function setType($type)
    {
        if (!is_string($type)) {
            throw new InvalidArgumentException('Attachment type must be a string.');
        }

        $this->type = $type;

        return $this;
    }

    /**
     * Retrieve the label of the attachment type.
     *
     * @return string Returns the translated attachment type or the short name.
     */
    public function typeLabel()
    {
        $type  = $this->type();
        $label = $this->translator()->translate($type);

        if ($type === $label) {
            $label = ucfirst($this->microType());
        }

        return $label;
    }

    /**
     * Retrieve the unqualified class name.
     *
     * @return string Returns the short name of the model's class, the part without the namespace.
     */
    public function microType()
    {
        $classname = get_called_class();

        if (!isset(static::$resolvedType[$classname])) {
            $reflect = new ReflectionClass($this);

            static::$resolvedType[$classname] = strtolower($reflect->getShortName());
        }

        return static::$resolvedType[$classname];
    }

    /**
     * Retrieve the image attachment type.
     *
     * @return string
     */
    public function imageType()
    {
        return self::IMAGE_TYPE;
    }

    /**
     * Retrieve the attachment's heading template.
     *
     * @return Translation|string|null
     */
    public function heading()
    {
        $heading = $this->renderTemplate((string)$this->heading);

        if (!$heading) {
            $heading = $this->translator()->translation('{{ objType }} #{{ id }}', [
                '{{ objType }}' => $this->typeLabel(),
                '{{ id }}'      => $this->id()
            ]);
        }

        return $heading;
    }

    /**
     * Retrieve the attachment's heading as a raw value.
     *
     * @return Translation|string|null
     */
    public function rawHeading()
    {
        return $this->heading;
    }

    /**
     * Set the attachment's heading template.
     *
     * @param  string $template The attachment heading.
     * @return Attachment Chainable
     */
    public function setHeading($template)
    {
        $this->heading = $this->translator()->translation($template);

        return $this;
    }

    /**
     * Retrieve the attachment's preview template.
     *
     * @return Translation|string|null
     */
    public function preview()
    {
        if ($this->preview) {
            return $this->renderTemplate((string)$this->preview);
        }

        return '';
    }

    /**
     * Retrieve the attachment's preview as a raw value.
     *
     * @return Translation|string|null
     */
    public function rawPreview()
    {
        return $this->preview;
    }

    /**
     * Set the attachment's preview template.
     *
     * @param  string $template The attachment preview.
     * @return Attachment Chainable
     */
    public function setPreview($template)
    {
        $this->preview = $this->translator()->translation($template);

        return $this;
    }

    /**
     * Determine if the attachment type is an image.
     *
     * @return boolean
     */
    public function isImage()
    {
        return ($this->microType() === 'image');
    }

    /**
     * Determine if the attachment type is an embed object.
     *
     * @return boolean
     */
    public function isEmbed()
    {
        return ($this->microType() === 'embed');
    }

    /**
     * Determine if the attachment type is a video.
     *
     * @return boolean
     */
    public function isVideo()
    {
        return ($this->microType() === 'video');
    }

    /**
     * Determine if the attachment type is a file attachment.
     *
     * @return boolean
     */
    public function isFile()
    {
        return ($this->microType() === 'file');
    }

    /**
     * Determine if the attachment type is a text-area.
     *
     * @return boolean
     */
    public function isText()
    {
        return ($this->microType() === 'text');
    }

    /**
     * Determine if the attachment type is an image gallery.
     *
     * @return boolean
     */
    public function isGallery()
    {
        return ($this->microType() === 'gallery');
    }

    /**
     * Determine if the attachment type is an accordion.
     *
     * @return boolean
     */
    public function isAccordion()
    {
        return ($this->microType() === 'accordion');
    }

    /**
     * Determine if the attachment type is a link.
     *
     * @return boolean
     */
    public function isLink()
    {
        return ($this->microType() === 'link');
    }

    /**
     * Determine if this attachment is a container.
     *
     * @return boolean
     */
    public function isAttachmentContainer()
    {
        return ($this instanceof AttachmentContainerInterface);
    }

    // Setters
    // =============================================================================

    /**
     * Show/hide the attachment's title on the front-end.
     *
     * @param  boolean $show Show (TRUE) or hide (FALSE) the title.
     * @return UiItemInterface Chainable
     */
    public function setShowTitle($show)
    {
        $this->showTitle = !!$show;

        return $this;
    }

    /**
     * Set the attachment's title.
     *
     * @param  string $title The object title.
     * @return self
     */
    public function setTitle($title)
    {
        $this->title = $this->translator()->translation($title);

        return $this;
    }

    /**
     * Set the attachment's sub-title.
     *
     * @param  string $title The object title.
     * @return self
     */
    public function setSubtitle($title)
    {
        $this->subtitle = $this->translator()->translation($title);

        return $this;
    }

    /**
     * Set the attachment's description.
     *
     * @param  string $description The description of the object.
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $this->translator()->translation($description);

        if ($this->isPresentable() && $this->description) {
            foreach ($this->description->data() as $lang => $trans) {
                $this->description[$lang] = $this->resolveUrls($trans);
            }
        }

        return $this;
    }

    /**
     * Set the attachment's keywords.
     *
     * @param  string|string[] $keywords One or more entries.
     * @return self
     */
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;

        return $this;
    }

    /**
     * Set the path to the thumbnail associated with the object.
     *
     * @param  string $path A path to an image.
     * @return self
     */
    public function setThumbnail($path)
    {
        $this->thumbnail = $this->translator()->translation($path);

        return $this;
    }

    /**
     * Set the path to the attached file.
     *
     * @param  string $path A path to a file.
     * @return self
     */
    public function setFile($path)
    {
        $this->file = $this->translator()->translation($path);

        return $this;
    }

    /**
     * Set the URL.
     *
     * @param  string $link An external url.
     * @return self
     */
    public function setLink($link)
    {
        $this->link = $this->translator()->translation($link);

        return $this;
    }

    /**
     * Set the file label.
     *
     * @param  string $label A descriptor.
     * @return self
     */
    public function setFileLabel($label)
    {
        $this->fileLabel = $this->translator()->translation($label);

        return $this;
    }

    /**
     * Set the link label.
     *
     * @param  string $label A descriptor.
     * @return self
     */
    public function setLinkLabel($label)
    {
        $this->linkLabel = $this->translator()->translation($label);

        return $this;
    }

    /**
     * Set the size of the attached file.
     *
     * @param  integer|float $size A file size in bytes; the one of the attached.
     * @throws InvalidArgumentException If provided argument is not of type 'integer' or 'float'.
     * @return self
     */
    public function setFileSize($size)
    {
        if ($size === null) {
            $this->fileSize = null;

            return $this;
        }

        if (!is_numeric($size)) {
            throw new InvalidArgumentException('File size must be an integer or a float.');
        }

        $this->fileSize = $size;

        return $this;
    }

    /**
     * Set file extension.
     *
     * @param  string $type File extension.
     * @return self
     */
    public function setFileType($type)
    {
        $this->fileType = $type;

        return $this;
    }

    /**
     * Set the embed content.
     *
     * @param  string $embed A URI or an HTML media element.
     * @throws InvalidArgumentException If provided argument is not of type 'string'.
     * @return self
     */
    public function setEmbed($embed)
    {
        $this->embed = $this->translator()->translation($embed);

        return $this;
    }

    /**
     * @param string|\string[] $categories Category elements.
     * @return self
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;

        return $this;
    }

    // Getters
    // =============================================================================

    /**
     * Determine if the title is to be displayed on the front-end.
     *
     * @return boolean
     */
    public function showTitle()
    {
        if (is_bool($this->showTitle)) {
            return $this->showTitle;
        } else {
            return !!$this->title();
        }
    }

    /**
     * Retrieve the attachment's title.
     *
     * @return Translation|string|null
     */
    public function title()
    {
        return $this->title;
    }

    /**
     * Retrieve the attachment's sub-title.
     *
     * @return Translation|string|null
     */
    public function subtitle()
    {
        return $this->subtitle;
    }

    /**
     * Retrieve attachment's description.
     *
     * @return Translation|string|null
     */
    public function description()
    {
        return $this->description;
    }

    /**
     * Retrieve the attachment's keywords.
     *
     * @return string[]
     */
    public function keywords()
    {
        return $this->keywords;
    }

    /**
     * Retrieve the path to the thumbnail associated with the object.
     *
     * @return string|null
     */
    public function thumbnail()
    {
        return $this->thumbnail;
    }

    /**
     * Retrieve the path to the attached file.
     *
     * @return Translation|string|null
     */
    public function file()
    {
        return $this->file;
    }

    /**
     * Retrieve the attached link.
     *
     * @return Translation|string|null
     */
    public function link()
    {
        return $this->link;
    }

    /**
     * Retrieve either the attached file or link.
     *
     * @return Translation|string|null
     */
    public function fileOrLink()
    {
        return $this['file'] ?: $this['link'];
    }

    /**
     * Retrieve the attached file(s) and link(s).
     *
     * @return string[]|null
     */
    public function fileAndLink()
    {
        $prop  = $this->property('file');
        $files = $prop->parseValAsFileList($this['file']);
        $links = $prop->parseValAsFileList($this['link']);

        $items = array_merge($files, $links);
        $items = array_unique($items);
        $items = array_values($items);

        return $items;
    }

    /**
     * Basename of the associated file.
     * @return string Basename of file.
     */
    public function basename()
    {
        if (!$this->file()) {
            return '';
        }

        return basename(strval($this->file()));
    }

    /**
     * Retrieve the file label.
     *
     * @return string|null
     */
    public function fileLabel()
    {
        return $this->fileLabel;
    }

    /**
     * Retrieve the link label.
     *
     * @return string|null
     */
    public function linkLabel()
    {
        return $this->linkLabel;
    }

    /**
     * Retrieve the attached file's size.
     *
     * @return integer Returns the size of the file in bytes, or FALSE in case of an error.
     */
    public function fileSize()
    {
        return $this->fileSize;
    }

    /**
     * File type / extension
     * @return string File extension.
     */
    public function fileType()
    {
        return $this->fileType;
    }

    /**
     * Retrieve the embed content.
     *
     * @return string
     */
    public function embed()
    {
        return $this->embed;
    }

    /**
     * @return string|\string[]
     */
    public function categories()
    {
        return $this->categories;
    }

    /**
     * @return ModelInterface|mixed
     */
    public function presenter()
    {
        return $this->presenter;
    }

    /**
     * @param ModelInterface|mixed $presenter Presenter for Attachment.
     * @return self
     */
    public function setPresenter($presenter)
    {
        $this->presenter = $presenter;

        return $this;
    }

    // Events
    // =============================================================================

    /**
     * Event called before _deleting_ the attachment.
     *
     * @see    Charcoal\Source\StorableTrait::preDelete() For the "create" Event.
     * @see    Charcoal\Attachment\Traits\AttachmentAwareTrait::removeJoins
     * @return boolean
     */
    public function preDelete()
    {
        $joinCollection = $this->collectionLoader()
            ->reset()
            ->setModel(Join::class)
            ->addFilter('attachment_id', $this['id'])
            ->load();

        foreach ($joinCollection as $joinModel) {
            $joinModel->delete();
        }

        return parent::preDelete();
    }

    // Utilities
    // =============================================================================

    /**
     * Set the base URI of the project.
     *
     * @see    \Charcoal\Admin\Support\setBaseUrl::baseUrl()
     * @param  UriInterface $uri The base URI.
     * @return self
     */
    protected function setBaseUrl(UriInterface $uri)
    {
        $this->baseUrl = $uri;

        return $this;
    }

    /**
     * Retrieve the base URI of the project.
     *
     * @throws RuntimeException If the base URI is missing.
     * @return UriInterface|null
     */
    public function baseUrl()
    {
        if (!isset($this->baseUrl)) {
            throw new RuntimeException(sprintf(
                'The base URI is not defined for [%s]',
                get_class($this)
            ));
        }

        return $this->baseUrl;
    }

    /**
     * Prepend the base URI to the given path.
     *
     * @param  string $uri A URI path to wrap.
     * @return UriInterface|null
     */
    public function createAbsoluteUrl($uri)
    {
        if (!isset($uri)) {
            return null;
        }

        $uri = strval($uri);
        if ($this->isRelativeUri($uri)) {
            $parts = parse_url($uri);
            $path  = isset($parts['path']) ? $parts['path'] : '';
            $query = isset($parts['query']) ? $parts['query'] : '';
            $hash  = isset($parts['fragment']) ? $parts['fragment'] : '';

            return $this->baseUrl()->withPath($path)->withQuery($query)->withFragment($hash);
        }

        return $uri;
    }

    /**
     * Prepend the base URI to the given path.
     *
     * @param  string $text A string to parse relative URIs.
     * @return UriInterface|null
     */
    protected function resolveUrls($text)
    {
        static $search;

        if ($search === null) {
            $attr   = [ 'href', 'link', 'url', 'src' ];
            $scheme = [ '../', './', '/', 'data', 'ftp', 'http', 'mailto', 'sftp', 'ssh', 'tel', 'urn' ];

            $search = sprintf(
                '(?<=%1$s=")(?!%2$s)(\S+)(?=")',
                implode('="|', array_map('preg_quote', $attr, [ '~' ])),
                implode('|', array_map('preg_quote', $scheme, [ '~' ]))
            );
        }

        $text = preg_replace_callback(
            '~' . $search . '~i',
            function ($matches) {
                return $this->createAbsoluteUrl($matches[1]);
            },
            $text
        );

        return $text;
    }

    /**
     * Determine if the given URI is relative.
     *
     * @see    \Charcoal\Admin\Support\BaseUrlTrait::isRelativeUri()
     * @param  string $uri A URI path to test.
     * @return boolean
     */
    protected function isRelativeUri($uri)
    {
        if (!$uri) {
            return false;
        }

        if (\parse_url($uri, PHP_URL_SCHEME)) {
            return false;
        }

        if (\preg_match('/^([\/\#\?]|[a-z][a-z0-9+.-]*:)/i', $uri)) {
            return false;
        }

        return true;
    }

    /**
     * Set a model collection loader.
     *
     * @param  CollectionLoader $loader The collection loader.
     * @return self
     */
    protected function setCollectionLoader(CollectionLoader $loader)
    {
        $this->collectionLoader = $loader;

        return $this;
    }

    /**
     * Retrieve the model collection loader.
     *
     * @throws Exception If the collection loader was not previously set.
     * @return CollectionLoader
     */
    public function collectionLoader()
    {
        if (!isset($this->collectionLoader)) {
            throw new Exception(sprintf(
                'Collection Loader is not defined for "%s"',
                get_class($this)
            ));
        }

        return $this->collectionLoader;
    }
}

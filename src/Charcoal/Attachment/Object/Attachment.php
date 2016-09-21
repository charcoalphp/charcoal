<?php

namespace Charcoal\Attachment\Object;

use \ReflectionClass;

use \Pimple\Container;

// Dependency from 'charcoal-core'
use \Charcoal\Loader\CollectionLoader;

// Dependency from 'charcoal-base'
use \Charcoal\Object\Content;

// Dependency from 'charcoal-translation'
use \Charcoal\Translation\TranslationString;

// Local Dependencies
use \Charcoal\Attachment\Interfaces\AttachableInterface;

use \Charcoal\Attachment\Object\File;
use \Charcoal\Attachment\Object\Image;
use \Charcoal\Attachment\Object\Text;
use \Charcoal\Attachment\Object\Embed;
use \Charcoal\Attachment\Object\Video;
use \Charcoal\Attachment\Object\Gallery;
use \Charcoal\Attachment\Object\Accordion;
use \Charcoal\Attachment\Object\Link;
use \Charcoal\Attachment\Object\Container as AttachmentContainer;
use \Charcoal\Attachment\Object\Join;

/**
 *
 */
class Attachment extends Content implements AttachableInterface
{
    /**
     * Default attachment types
     */
    const FILE_TYPE      = File::class;
    const LINK_TYPE      = Link::class;
    const IMAGE_TYPE     = Image::class;
    const EMBED_TYPE     = Embed::class;
    const VIDEO_TYPE     = Video::class;
    const TEXT_TYPE      = Text::class;
    const GALLERY_TYPE   = Gallery::class;
    const ACCORDION_TYPE = Accordion::class;
    const CONTAINER_TYPE = AttachmentContainer::class;

    /**
     * Glyphicons (from Bootstrap) for each of the default attachment types.
     *
     * @var array
     */
    protected $glyphs = [
        'embed'     => 'glyphicon-blackboard',
        'video'     => 'glyphicon-film',
        'image'     => 'glyphicon-picture',
        'file'      => 'glyphicon-file',
        'link'      => 'glyphicon-link',
        'text'      => 'glyphicon-font',
        'gallery'   => 'glyphicon-duplicate',
        'container' => 'glyphicon-list',
        'accordion' => 'glyphicon-list'
    ];

    /**
     * A store of resolved attachment types.
     *
     * @var array $resolved
     */
    protected static $resolved = [];

    /**
     * The attachment ID.
     *
     * @var integer
     */
    protected $id;

    /**
     * The attachment type.
     *
     * @var string
     */
    protected $type;

    /**
     * The attachment heading template.
     *
     * @var TranslationString|string|null
     */
    protected $heading;

    /**
     * The attachment preview template.
     *
     * @var TranslationString|string|null
     */
    protected $preview;

    /**
     * Generic information about the attachment.
     *
     * @var TranslationString|string[] $title       The title of the attachment.
     * @var TranslationString|string[] $subtitle    The subtitle of the attachment.
     * @var TranslationString|string[] $description The content of the attachment.
     * @var TranslationString|string[] $keywords    Keywords finding the attachment.
     */
    protected $title;
    protected $subtitle;
    protected $description;
    protected $keywords;

    /**
     * File related attachments.
     *
     * @var file    $file     The path of an attached file.
     * @var integer $fileSize The size of the attached file in bytes.
     * @var string  $fileType The content type of the attached file.
     */
    protected $file;
    protected $fileSize;
    protected $fileType;
    protected $link;

    /**
     * Path to a thumbnail of the attached file.
     *
     * Auto-generated thumbnail if the attached file is an image.
     *
     * @var string
     */
    protected $thumbnail;

    /**
     * Embedded content.
     *
     * @var string
     */
    protected $embed;

    /**
     * The attachment's position amongst other attachments.
     *
     * @var integer
     */
    protected $position;

    /**
     * Store the collection loader for the current class.
     *
     * @var CollectionLoader
     */
    private $collectionLoader;

    /**
     * Return a new section object.
     *
     * @param array $data Dependencies.
     */
    public function __construct(array $data = null)
    {
        parent::__construct($data);

        if (is_callable([ $this, 'defaultData' ])) {
            $defaultData = $this->defaultData();
            if ($defaultData) {
                $this->setData($defaultData);
            }
        }
    }

    /**
     * Inject dependencies from a DI Container.
     *
     * @param Container $container A dependencies container instance.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setCollectionLoader($container['model/collection/loader']);
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
     * Retrieve the unqualified class name.
     *
     * @return string Returns the short name of the model's class, the part without the namespace.
     */
    public function microType()
    {
        $classname = get_called_class();

        if (!isset(static::$resolved[$classname])) {
            $reflect = new ReflectionClass($this);

            static::$resolved[$classname] = strtolower($reflect->getShortName());
        }

        return static::$resolved[$classname];
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
     * Retrieve the glyphicon for the current attachment type.
     *
     * @return string
     */
    public function glyphicon()
    {
        $type = $this->microType();

        if (isset($this->glyphs[$type])) {
            return $this->glyphs[$type];
        }

        return '';
    }

    /**
     * Retrieve the attachment's heading template.
     *
     * @return string
     */
    public function heading()
    {
        $heading = $this->render((string)$this->heading);

        if (!$heading) {
            $heading = sprintf(
                '%1$s #%2$s',
                ucfirst($this->microType()),
                $this->id()
            );
        }

        return $heading;
    }

    /**
     * Retrieve the attachment's heading as a raw value.
     *
     * @return string
     */
    public function rawHeading()
    {
        return $this->heading;
    }

    /**
     * Set the attachment's heading template.
     *
     * @param  string $template The attachment heading.
     * @throws InvalidArgumentException If provided argument is not of type 'string'.
     * @return string
     */
    public function setHeading($template)
    {
        if (TranslationString::isTranslatable($template)) {
            $this->heading = new TranslationString($template);
        } else {
            $this->heading = null;
        }

        return $this;
    }

    /**
     * Retrieve the attachment's preview template.
     *
     * @return string
     */
    public function preview()
    {
        if ($this->preview) {
            return $this->render((string)$this->preview);
        }

        return '';
    }

    /**
     * Retrieve the attachment's preview as a raw value.
     *
     * @return string
     */
    public function rawPreview()
    {
        return $this->preview;
    }

    /**
     * Set the attachment's preview template.
     *
     * @param  string $template The attachment preview.
     * @throws InvalidArgumentException If provided argument is not of type 'string'.
     * @return string
     */
    public function setPreview($template)
    {
        if (TranslationString::isTranslatable($template)) {
            $this->preview = new TranslationString($template);
        } else {
            $this->preview = null;
        }

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



// Setters
// =============================================================================

    /**
     * Set the attachment's title.
     *
     * @param  string|string[] $title The object title.
     * @return self
     */
    public function setTitle($title)
    {
        if (TranslationString::isTranslatable($title)) {
            $this->title = new TranslationString($title);
        }

        return $this;
    }

    /**
     * Set the attachment's sub-title.
     *
     * @param  string|string[] $title The object title.
     * @return self
     */
    public function setSubtitle($title)
    {
        if (TranslationString::isTranslatable($title)) {
            $this->subtitle = new TranslationString($title);
        }

        return $this;
    }

    /**
     * Set the attachment's description.
     *
     * @param  string|string[] $description The description of the object.
     * @return self
     */
    public function setDescription($description)
    {
        if (TranslationString::isTranslatable($description)) {
            $this->description = new TranslationString($description);
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
     * Set the path to the attached file.
     *
     * @param string $path A path to an image.
     * @return self
     */
    public function setFile($path)
    {
        $this->file = $path;

        return $this;
    }

    /**
     * Set the URL.
     *
     * @param string $link An external url.
     * @return self
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Set the size of the attached file.
     *
     * @param integer|float $size A file size in bytes; the one of the attached.
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
     * Set the embed content.
     *
     * @param string $embed A URI or an HTML media element.
     * @throws InvalidArgumentException If provided argument is not of type 'string'.
     * @return self
     */
    public function setEmbed($embed)
    {
        if ($embed === null) {
            $this->embed = null;

            return $this;
        }

        if (!is_string($embed)) {
            throw new InvalidArgumentException('Embedded content must be a string.');
        }

        $this->embed = $embed;

        return $this;
    }


// Getters
// =============================================================================

    /**
     * Retrieve the attachment's title.
     *
     * @return string|TranslationString
     */
    public function title()
    {
        return $this->title;
    }

    /**
     * Retrieve the attachment's sub-title.
     *
     * @return string|TranslationString
     */
    public function subtitle()
    {
        return $this->subtitle;
    }

    /**
     * Retrieve attachment's description.
     *
     * @return string|TranslationString
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
     * Retrieve the path to the attached file.
     *
     * @return string
     */
    public function file()
    {
        return $this->file;
    }

    /**
     * Retrieve the attached link.
     *
     * @return string
     */
    public function link()
    {
        return $this->link;
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
        return basename($this->file());
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
     * Retrieve the embed content.
     *
     * @return string
     */
    public function embed()
    {
        return $this->embed;
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
        $attId = $this->id();
        $joinProto = $this->modelFactory()->create(Join::class);
        $loader = $this->collectionLoader();
        $loader->setModel($joinProto);

        $collection = $loader->addFilter('attachment_id', $attId)->load();

        foreach ($collection as $obj) {
            $obj->delete();
        }

        return parent::preDelete();
    }



// Utilities
// =============================================================================

    /**
     * Set a model collection loader.
     *
     * @param CollectionLoader $loader The collection loader.
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
            throw new Exception(
                sprintf('Collection Loader is not defined for "%s"', get_class($this))
            );
        }

        return $this->collectionLoader;
    }
}

<?php
namespace Charcoal\Attachment\Object;

use \ReflectionClass;

// Dependencies from `charcoal-base`
// Configurable, indexable
use \Charcoal\Object\Content;
use \Charcoal\Loader\CollectionLoader;

// From Charcoal\Translation
use \Charcoal\Translation\TranslationString;

// From Charcoal\Attachment
// Actual available attachments.
use \Charcoal\Attachment\Object\File;
use \Charcoal\Attachment\Object\Image;
use \Charcoal\Attachment\Object\Text;
use \Charcoal\Attachment\Object\Video;
use \Charcoal\Attachment\Object\Gallery;
use \Charcoal\Attachment\Object\Link;
use \Charcoal\Attachment\Object\Join;


/**
 *
 */
class Attachment extends Content
{
    /**
     * File classes
     */
    const FILE_TYPE = File::class;
    const LINK_TYPE = Link::class;
    const IMAGE_TYPE = Image::class;
    const VIDEO_TYPE = Video::class;
    const TEXT_TYPE = Text::class;
    const GALLERY_TYPE = Gallery::class;

    /**
     * Glyph icons from bootstrap.
     * Array
     * @var array $glyphs.
     */
    private $glyphs = [
        'video'     => 'glyphicon-facetime-video',
        'image'     => 'glyphicon-picture',
        'file'      => 'glyphicon-file',
        'link'      => 'glyphicon-file',
        'text'      => 'glyphicon-font',
        'gallery'   => 'glyphicon-duplicate'
    ];

    /**
     * Attachment ID
     *
     * @var integer $id ID
     */
    protected $id;

    /**
     * Attachment type
     * Could be:
     * - Image
     * - Text
     * - Video
     *
     * @var strin $type
     */
    protected $type;


    /**
     * Informations about the attachment
     * Keywords for search purpose. Other
     * general informations about the attachments
     * - description, title, subtitle
     *
     * @var string $title           l10n
     * @var string $subtitle        l10n
     * @var string $description     l10n
     * @var string $keywords        l10n
     */
    protected $title;
    protected $subtitle;
    protected $description;
    protected $keywords;

    /**
     * File related
     *
     * @var file    $file           The actual file.
     * @var integer $fileSize       Size of the file.
     * @var string  $contentType    Content type of file.
     */
    protected $file;
    protected $fileSize;
    protected $contentType;

    /**
     * Auto-generated thumbnail when possible
     *
     * @var string/image
     */
    protected $thumbnail;

    /**
     * Embed video
     *
     * @var text $embed
     */
    protected $embed;


    /**
     * Position of the attachment
     *
     * @var Integer $position       Position.
     */
    protected $position;

    /**
     * From CONTENT
     */
    protected $active;
    protected $created;
    protected $createdBy;
    protected $lastModified;
    protected $lastModifiedBy;

    public function preDelete()
    {
        $id = $this->id();
        $join = $this->modelFactory()->create(Join::class);
        $loader = new CollectionLoader([
                    'logger'=>$this->logger,
                    'factory'=>$this->modelFactory()
        ]);
        $loader->setModel($join);
        $collection = $loader->addFilter('attachment_id', $id)->load();
        foreach ($collection as $c) {
            $c->delete();
        }

        return parent::preDelete();
    }

    /**
     * Different depending on the type of attachment.
     *
     * @return string OBjType.
     */
    public function type()
    {
        if (!$this->type) {
            $this->type = $this->objType();
        }
        return $this->type;
    }

    /**
     * Unqualified class name.
     * Returns only the end value of the current objType in lowercase.
     * @return string ObjType without namespace.
     */
    public function microType()
    {
        $reflect = new ReflectionClass($this);
        return strtolower($reflect->getShortName());
    }

    /**
     * Type of file
     * Used in template as logic for display type
     * Backend logic.
     * @return boolean
     */
    public function isImage()
    {
        return ($this->microType() == 'image');
    }
    public function isVideo()
    {
        return ($this->microType() == 'video');
    }
    public function isFile()
    {
        return ($this->microType() == 'file');
    }
    public function isText()
    {
        return ($this->microType() == 'text');
    }
    public function isGallery()
    {
        return ($this->microType() == 'gallery');
    }
    public function isLink()
    {
        return ($this->microType() == 'link');
    }

    public function imageType()
    {
        return Attachment::IMAGE_TYPE;
    }

    public function glyphicon()
    {
        $microType = $this->microType();
        if (isset($this->glyphs[ $microType ])) {
            return $this->glyphs[ $microType ];
        }

        return '';
    }


/**
 * SETTERS
 */
    public function setTitle($title)
    {
        $this->title = $this->translatable($title);
        return $this;
    }
    public function setSubtitle($subtitle)
    {
        $this->subtitle = $this->translatable($subtitle);
        return $this;
    }
    public function setDescription($description)
    {
        $this->description = $this->translatable($description);
        return $this;
    }
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;
        return $this;
    }
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }
    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }
    public function setFileSize($fs)
    {
        $this->fileSize = $fs;
        return $this;
    }
    public function setEmbed($embed)
    {
        $this->embed = $embed;
        return $this;
    }

/**
 * GETTERS
 */
    public function title()
    {
        return $this->title;
    }
    public function subtitle()
    {
        return $this->subtitle;
    }
    public function description()
    {
        return $this->description;
    }
    public function keywords()
    {
        return $this->keywords;
    }
    public function file()
    {
        return $this->file;
    }
    public function fileSize()
    {
        return $this->fileSize;
    }
    public function embed()
    {
        return $this->embed;
    }

/**
 * UTILS
 */
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
}

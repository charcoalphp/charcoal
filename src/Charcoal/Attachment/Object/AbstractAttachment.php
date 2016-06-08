<?php
namespace Charcoal\Attachment\Object;


// Dependencies from `charcoal-base`
// Configurable, indexable
use \Charcoal\Object\Content;

// From Charcoal\Translation
use \Charcoal\Translation\TranslationString;

// From Charcoal\Attachment
// Actual available attachments.
use \Charcoal\Attachment\Object\File;
use \Charcoal\Attachment\Object\Image;
use \Charcoal\Attachment\Object\Text;
use \Charcoal\Attachment\Object\Video;


/**
 *
 */
class AbstractAttachment extends Content
{
    /**
     * File classes
     */
	const FILE_TYPE = File::class;
	const IMAGE_TYPE = Image::class;
	const VIDEO_TYPE = Video::class;
	const TEXT_TYPE = Text::class;

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
	 * @var string $title 			l10n
	 * @var string $subtitle 		l10n
	 * @var string $description 	l10n
	 * @var string $keywords 		l10n
	 */
	protected $title;
	protected $subtitle;
	protected $description;
	protected $keywords;

	/**
	 * File related
	 *
	 * @var file	$file			The actual file.
	 * @var integer $fileSize 		Size of the file.
	 * @var string  $contentType 	Content type of file.
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
	 * @var Integer $position 		Position.
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
	 * Type of file
	 * Used in template as logic for display type
	 * Backend logic.
	 * @return boolean
	 */
	public function isImage()
	{
		return ($this->type() == Attachment::IMAGE_TYPE);
	}
	public function isVideo()
	{
		return ($this->type() == Attachment::VIDEO_TYPE);
	}
	public function isFile()
	{
		return ($this->type() == Attachment::FILE_TYPE);
	}
	public function isText()
	{
		return ($this->type() == Attachment::TEXT_TYPE);
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

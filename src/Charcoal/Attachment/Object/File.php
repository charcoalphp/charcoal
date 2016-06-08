<?php
namespace Charcoal\Attachment\Object;

// From Charcoal\Attachment
use \Charcoal\Attachment\Object\Attachment;

/**
 * File attachment
 * Usually PDFs, Doc, Docx, etc.
 * The file attachment is displayed differently
 * than the Image attachment, but is still basicly the same.
 *
 */
class File extends Attachment
{
	/**
	 * Generate thumbnail if necessary
	 *
	 * @return Boolean 			Depends on parent function.
	 */
	public function preSave()
	{
		$this->generateThumbnail();
		return parent::preSave();
	}

	/**
	 * Generate thumbnail if necessary
	 *
	 * @param  Array 	$cfg   	This can happen.
	 * @return Boolean 			Depends on parent function.
	 */
	public function preUpdate(array $properties = NULL)
	{
		$this->generateThumbnail();
		return parent::preUpdate($properties);
	}

    /**
     * Thumbnail generation for files. This might be
     * linked to the fileType.
     * @return Boolean Success or failure.
     */
	public function generateThumbnail()
	{
		// @todo Generate thumbnail from config or whatever.
		// File attachments should have a placeholder defined.

		return true;
	}
}

<?php
namespace Charcoal\Attachment\Object;

// From Charcoal\Attachment
use \Charcoal\Attachment\Object\Attachment;

/**
 * Image attachment
 * Uses a file input, title and description.
 *
 */
class Image extends Attachment
{

    /**
     * From bootstrap. Glyphicon used to identify the attachment type.
     * @return string Glypicon.
     */
    public function glyphicon()
    {
        return 'glyphicon-picture';
    }

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
     * Thumbnail generation for images. From
     * the original image of the object.
     * @todo Define the thumbnail generation pattern.
     * @return Boolean Success or failure.
     */
	public function generateThumbnail()
	{
		// @todo Generate thumbnail from the main image (or not.).

		return true;
	}
}

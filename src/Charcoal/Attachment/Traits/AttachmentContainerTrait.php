<?php
namespace Charcoal\Attachment\Traits;

// Dependencies from 'charcoal-core'
use \Charcoal\Model\ModelInterface;
use \Charcoal\Loader\CollectionLoader;

use \Charcoal\Translation\TranslationString;

// Local Dependencies
use \Charcoal\Attachment\Interfaces\AttachableInterface;
use \Charcoal\Attachment\Object\Join;
use \Charcoal\Attachment\Object\Attachment;
use \Charcoal\Attachment\Object\File;
use \Charcoal\Attachment\Object\Image;
use \Charcoal\Attachment\Object\Text;
use \Charcoal\Attachment\Object\Video;

/**
 * Provides support for attachments to objects.
 *
 * Used by objects that can have an attachment to other objects.
 * This is the glue between the {@see Join} object and the current object.
 *
 * Abstract method needs to be implemented.
 *
 * Implementation of {@see \Charcoal\Attachment\Interfaces\AttachmentAwareInterface}
 *
 * ## Required Services
 *
 * - "model/factory" — {@see \Charcoal\Model\ModelFactory}
 * - "model/collection/loader" — {@see \Charcoal\Loader\CollectionLoader}
 */
trait AttachmentContainerTrait
{
    protected $attachmentConfig;
    protected $attachableObjects;

    /**
     * Alias of {@see \Charcoal\Source\StorableTrait::id()}
     *
     * Retrieve the container's (unique) ID; useful when templating the container's attachments.
     *
     * @return mixed
     */
    public function containerId()
    {
        return $this->id();
    }

    /**
     * Gets the attachments config from
     * the object metadata.
     *
     * @return array
     */
    public function attachmentConfig()
    {
        if ($this->attachmentConfig) {
            return $this->attachmentConfig;
        }

        $meta = $this->metadata();
        $this->attachmentConfig = isset($meta['attachments']) ? $meta['attachments'] : [];

        return $this->attachmentConfig;
    }

    /**
     * Returns attachable objects
     *
     * @return array Attachable Objects
     */
    public function attachableObjects()
    {
        if ($this->attachableObjects) {
            return $this->attachableObjects;
        }

        $cfg = $this->attachmentConfig();

        if (!isset($cfg['attachable_objects'])) {
            $this->attachableObjects = [];
            return $this->attachableObjects;
        }

        $out = [];
        foreach ($cfg['attachable_objects'] as $ident => $val) {
            if (!isset($val['attachment_type'])) {
                $val['attachment_type'] = $ident;
            }

            $val['attachmentType'] = $val['attachment_type'];
            $val['label'] = isset($val['label']) ? new TranslationString($val['label']) : '';

            $out[] = $val;
        }
        $this->attachableObjects = $out;
        return $this->attachableObjects;
    }

    /**
     * Returns true
     * @return boolean True.
     */
    public function isAttachmentContainer()
    {
        return true;
    }

    /**
     * Objects metadata.
     * Default behavior in Content class.
     *
     * @return string
     */
    abstract function metadata();
}

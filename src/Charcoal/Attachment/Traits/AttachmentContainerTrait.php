<?php
namespace Charcoal\Attachment\Traits;

use \UnexpectedValueException;

// From 'charcoal-core'
use \Charcoal\Model\ModelInterface;
use \Charcoal\Loader\CollectionLoader;

// From 'charcoal-translation'
use \Charcoal\Translation\TranslationString;

// From 'beneroch/charcoal-attachments'
use \Charcoal\Attachment\Interfaces\AttachmentContainerInterface;
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
    /**
     * The container's configuration.
     *
     * @var array
     */
    protected $attachmentConfig;

    /**
     * The container's accepted attachment types.
     *
     * @var array
     */
    protected $attachableObjects;

    /**
     * The container's group identifier.
     *
     * The group is used to create multiple widget instance on the same page.
     *
     * @var string
     */
    protected $group;

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
     * Retrieve the attachments configuration from this object's metadata.
     *
     * @return array
     */
    public function attachmentConfig()
    {
        if ($this->attachmentConfig === null) {
            $metadata = $this->metadata();
            $this->attachmentConfig = (isset($metadata['attachments']) ? $metadata['attachments'] : []);
        }

        return $this->attachmentConfig;
    }

    /**
     * Retrieve the widget's attachment grouping.
     *
     * @throws UnexpectedValueException If the grouping is invalid.
     * @return string
     */
    public function attachmentGroup()
    {
        if ($this->group === null) {
            $cfg   = $this->attachmentConfig();
            $group = AttachmentContainerInterface::DEFAULT_GROUPING;
            if (isset($cfg['default_group'])) {
                $group = $cfg['default_group'];
            // If the 'default_group' is not set, search for it.
            } elseif (isset($cfg['default_widget'])) {
                $widget   = $cfg['default_widget'];
                $metadata = $this->metadata();
                $found    = false;
                if (isset($metadata['admin']['form_groups'][$widget]['group'])) {
                    $group = $metadata['admin']['form_groups'][$widget]['group'];
                    $found = true;
                }

                if (!$found && isset($metadata['admin']['forms'])) {
                    foreach ($metadata['admin']['forms'] as $form) {
                        if (isset($form['groups'][$widget]['group'])) {
                            $group = $form['groups'][$widget]['group'];
                            $found = true;
                            break;
                        }
                    }
                }

                if (!$found && isset($metadata['admin']['dashboards'])) {
                    foreach ($metadata['admin']['dashboards'] as $dashboard) {
                        if (isset($dashboard['widgets'][$widget]['group'])) {
                            $group = $dashboard['widgets'][$widget]['group'];
                            $found = true;
                            break;
                        }
                    }
                }
            }

            if (!is_string($group)) {
                throw new UnexpectedValueException('The attachment grouping must be a string.');
            }

            $this->group = $group;
        }

        return $this->group;
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

        $this->attachableObjects = [];

        $cfg = $this->attachmentConfig();
        if (isset($cfg['attachable_objects'])) {
            foreach ($cfg['attachable_objects'] as $attType => $attMeta) {
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

                // Alias
                $attMeta['attachmentType'] = $attMeta['attachment_type'];

                if (isset($attMeta['label']) && TranslationString::isTranslatable($attMeta['label'])) {
                    $attMeta['label'] = new TranslationString($attMeta['label']);
                } else {
                    $attMeta['label'] = ucfirst(basename($attType));
                }

                $this->attachableObjects[] = $attMeta;
            }
        }

        return $this->attachableObjects;
    }

    /**
     * Determine if this attachment is a container.
     *
     * @return boolean
     */
    public function isAttachmentContainer()
    {
        return true;
    }

    /**
     * Objects metadata.
     *
     * @see    \Charcoal\Model\DescribableInterface::metadata()
     * @return \Charcoal\Model\MetadataInterface
     */
    abstract public function metadata();
}

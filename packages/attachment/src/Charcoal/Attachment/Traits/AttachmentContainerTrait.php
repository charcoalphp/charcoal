<?php

namespace Charcoal\Attachment\Traits;

use UnexpectedValueException;
// From 'charcoal-attachment'
use Charcoal\Attachment\Interfaces\AttachmentContainerInterface;

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
 * - "translator" — {@see \Charcoal\Translator\Translator}
 */
trait AttachmentContainerTrait
{
    /**
     * The container's attachable metadata.
     *
     * @var array
     */
    protected $attachmentsMetadata;

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
     * Retrieve the attachments configuration from this object's metadata.
     *
     * @return array
     */
    public function attachmentsMetadata()
    {
        if ($this->attachmentsMetadata === null) {
            $this->attachmentsMetadata = [];

            $metadata = $this->metadata();
            if (isset($metadata['attachments'])) {
                $this->attachmentsMetadata = $this->mergePresets($metadata['attachments']);
            }
        }

        return $this->attachmentsMetadata;
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
            $config = $this->attachmentsMetadata();
            $group  = AttachmentContainerInterface::DEFAULT_GROUPING;

            if (isset($config['group'])) {
                $group = $config['group'];
                // If the 'default_group' is not set, search for it.
            } elseif (isset($config['default_group'])) {
                $group = $config['default_group'];
            // If the 'default_group' is not set, search for it.
            } elseif (isset($config['default_widget'])) {
                $widget   = $config['default_widget'];
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
     * Retrieve the attachment types with their collections.
     *
     * @return array
     */
    public function attachmentTypes()
    {
        return array_values($this->attachableObjects());
    }

    /**
     * Returns attachable objects
     *
     * @return array Attachable Objects
     */
    public function attachableObjects()
    {
        if ($this->attachableObjects === null) {
            $this->attachableObjects = [];

            $config = $this->attachmentsMetadata();
            if (isset($config['attachable_objects'])) {
                foreach ($config['attachable_objects'] as $attType => $attMeta) {
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

                    if (isset($attMeta['label'])) {
                        $attMeta['label'] = $this->translator()->translation($attMeta['label']);
                    } else {
                        $attMeta['label'] = ucfirst(basename($attType));
                    }

                    $faIcon = '';
                    if (isset($attMeta['fa_icon']) && !empty($attMeta['fa_icon'])) {
                        $faIcon = 'fa fa-' . $attMeta['fa_icon'];
                    }

                    $attMeta['faIcon'] = $faIcon;
                    $attMeta['hasFaIcon'] = !!$faIcon;

                    // Custom forms
                    if (isset($attMeta['form_ident'])) {
                        $attMeta['formIdent'] = $attMeta['form_ident'];
                    } else {
                        $attMeta['formIdent'] = null;
                    }

                    if (isset($attMeta['quick_form_ident'])) {
                        $attMeta['quickFormIdent'] = $attMeta['quick_form_ident'];
                    } else {
                        $attMeta['quickFormIdent'] = null;
                    }

                    $this->attachableObjects[$attType] = $attMeta;
                }
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

    /**
     * Retrieve the translator service.
     *
     * @see    \Charcoal\Translator\TranslatorAwareTrait
     * @return \Charcoal\Translator\Translator
     */
    abstract protected function translator();
}

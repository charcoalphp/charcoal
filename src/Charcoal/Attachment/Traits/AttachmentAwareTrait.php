<?php

namespace Charcoal\Attachment\Traits;

use InvalidArgumentException;

// From 'charcoal-core'
use Charcoal\Model\ModelInterface;
use Charcoal\Loader\CollectionLoader;

// From 'charcoal-admin'
use Charcoal\Admin\Widget\AttachmentWidget;

// From 'beneroch/charcoal-attachments'
use Charcoal\Attachment\Interfaces\AttachableInterface;
use Charcoal\Attachment\Interfaces\AttachmentContainerInterface;

use Charcoal\Attachment\Object\Join;
use Charcoal\Attachment\Object\Attachment;

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
trait AttachmentAwareTrait
{
    /**
     * A store of cached attachments, by ID.
     *
     * @var Attachment[] $attachmentCache
     */
    protected static $attachmentCache = [];

    /**
     * Store a collection of node objects.
     *
     * @var Collection|Attachment[]
     */
    protected $attachments = [];

    /**
     * Store the widget instance currently displaying attachments.
     *
     * @var AttachmentWidget
     */
    protected $attachmentWidget;

    /**
     * Retrieve the objects associated to the current object.
     *
     * @param  string|null $group Filter the attachments by a group identifier.
     * @param  string|null $type  Filter the attachments by type.
     * @throws InvalidArgumentException If the $group or $type is invalid.
     * @return Collection|Attachment[]
     */
    public function attachments($group = null, $type = null)
    {
        if ($group === null) {
            $group = 0;
        } elseif (!is_string($group)) {
            throw new InvalidArgumentException('The $group must be a string.');
        }

        if ($type === null) {
            $type = 0;
        } else {
            if (!is_string($type)) {
                throw new InvalidArgumentException('The $type must be a string.');
            }

            $type = preg_replace('/([a-z])([A-Z])/', '$1-$2', $type);
            $type = strtolower(str_replace('\\', '/', $type));
        }

        if (isset($this->attachments[$group][$type])) {
            return $this->attachments[$group][$type];
        }

        $objType = $this->objType();
        $objId   = $this->id();

        $joinProto = $this->modelFactory()->get(Join::class);
        $joinTable = $joinProto->source()->table();

        $attProto = $this->modelFactory()->get(Attachment::class);
        $attTable = $attProto->source()->table();

        if (!$attProto->source()->tableExists() || !$joinProto->source()->tableExists()) {
            return [];
        }

        $widget = $this->attachmentWidget();

        $query = '
            SELECT
                attachment.*,
                joined.attachment_id AS attachment_id,
                joined.position AS position
            FROM
                `'.$attTable.'` AS attachment
            LEFT JOIN
                `'.$joinTable.'` AS joined
            ON
                joined.attachment_id = attachment.id
            WHERE
                1 = 1';

        // Disable `active` check in admin
        if (!$widget instanceof AttachmentWidget) {
            $query .= '
            AND
                attachment.active = 1';
        }

        if ($type) {
            $query .= '
            AND
                attachment.type = "'.$type.'"';
        }

        $query .= '
            AND
                joined.object_type = "'.$objType.'"
            AND
                joined.object_id = "'.$objId.'"';

        if ($group) {
            $query .= '
            AND
                joined.group = "'.$group.'"';
        }

        $query .= '
            ORDER BY joined.position';

        $loader = $this->collectionLoader();
        $loader->setModel($attProto);
        $loader->setDynamicTypeField('type');

        if ($widget instanceof AttachmentWidget) {
            $callable = function ($att) use ($widget) {
                if ($this instanceof AttachableInterface) {
                    $att->setContainerObj($this);
                }

                $kind = $att->type();
                $attachables = $widget->attachableObjects();

                if (isset($attachables[$kind]['data'])) {
                    $att->setData($attachables[$kind]['data']);
                }

                if (!$att->rawHeading()) {
                    $att->setHeading($widget->attachmentHeading());
                }

                if (!$att->rawPreview()) {
                    $att->setPreview($widget->attachmentPreview());
                }
            };
        } else {
            $callable = function ($att) {
                if ($this instanceof AttachableInterface) {
                    $att->setContainerObj($this);
                }
            };
        }

        $loader->setCallback($callable->bindTo($this));

        $collection = $loader->loadFromQuery($query);

        $this->attachments[$group][$type] = $collection;

        return $this->attachments[$group][$type];
    }

    /**
     * Determine if the current object has any nodes.
     *
     * @return boolean Whether $this has any nodes (TRUE) or not (FALSE).
     */
    public function hasAttachments()
    {
        return !!($this->numAttachments());
    }

    /**
     * Count the number of nodes associated to the current object.
     *
     * @return integer
     */
    public function numAttachments()
    {
        return count($this->attachments());
    }

    /**
     * Attach an node to the current object.
     *
     * @param AttachableInterface|ModelInterface $attachment An attachment or object.
     * @return boolean|self
     */
    public function addAttachment($attachment)
    {
        if (!$attachment instanceof AttachableInterface && !$attachment instanceof ModelInterface) {
            return false;
        }

        $join = $this->modelFactory()->create(Join::class);

        $objId   = $this->id();
        $objType = $this->objType();
        $attId   = $attachment->id();

        $join->setAttachmentId($attId);
        $join->setObjId($objId);
        $join->setObjType($objType);

        $join->save();

        return $this;
    }

    /**
     * Remove all joins linked to a specific attachment.
     *
     * @return boolean
     */
    public function removeJoins()
    {
        $joinProto = $this->modelFactory()->get(Join::class);

        $loader = $this->collectionLoader();
        $loader
            ->setModel($joinProto)
            ->addFilter('object_type', $this->objType())
            ->addFilter('object_id', $this->id());

        $collection = $loader->load();

        foreach ($collection as $obj) {
            $obj->delete();
        }

        return true;
    }

    /**
     * Retrieve the attachment widget.
     *
     * @return AttachmentWidget
     */
    protected function attachmentWidget()
    {
        return $this->attachmentWidget;
    }

    /**
     * Set the attachment widget.
     *
     * @param  AttachmentWidget $widget The widget displaying attachments.
     * @return string
     */
    protected function setAttachmentWidget(AttachmentWidget $widget)
    {
        $this->attachmentWidget = $widget;

        return $this;
    }



    // Abstract Methods
    // =========================================================================

    /**
     * Retrieve the object's type identifier.
     *
     * @return string
     */
    abstract public function objType();

    /**
     * Retrieve the object's unique ID.
     *
     * @return mixed
     */
    abstract public function id();

    /**
     * Retrieve the object model factory.
     *
     * @return \Charcoal\Factory\FactoryInterface
     */
    abstract public function modelFactory();

    /**
     * Retrieve the model collection loader.
     *
     * @return \Charcoal\Loader\CollectionLoader
     */
    abstract public function collectionLoader();
}

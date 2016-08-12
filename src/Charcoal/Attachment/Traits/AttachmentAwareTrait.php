<?php
namespace Charcoal\Attachment\Traits;

// From 'charcoal-core'
use \Charcoal\Model\ModelInterface;
use \Charcoal\Loader\CollectionLoader;

// From 'charcoal-admin'
use \Charcoal\Admin\Widget\AttachmentWidget;

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
trait AttachmentAwareTrait
{
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
     * @return Collection|Attachment[]
     */
    public function attachments($group = null)
    {
        if (!isset($group)) {
            $group = 0;
        }

        if (isset($this->attachments[$group])) {
            return $this->attachments[$group];
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

        $query = 'SELECT
                attachment.*,
                joined.attachment_id as attachment_id,
                joined.position as position
            FROM
                `'.$attTable.'` as attachment
            LEFT JOIN
                `'.$joinTable.'` as joined
            ON
                joined.attachment_id = attachment.id
            WHERE
                joined.object_type = "'.$objType.'"
            AND
                joined.object_id = "'.$objId.'"
            AND
                attachment.active = 1';

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

        $widget = $this->attachmentWidget();
        if ($widget instanceof AttachmentWidget) {
            $callable = function ($att) {
                $type = $att->type();
                $attachables = $this->attachableObjects();

                if (isset($attachables[$type]['data'])) {
                    $att->setData($attachables[$type]['data']);
                }

                if (!$att->rawPreview()) {
                    $att->setPreview($this->preview());
                }
            };
            $loader->setCallback($callable->bindTo($widget));
        }

        $collection = $loader->loadFromQuery($query);

        $this->attachments[$group] = $collection;

        return $this->attachments[$group];
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
    public function attachmentWidget()
    {
        return $this->attachmentWidget;
    }

    /**
     * Set the attachment widget.
     *
     * @param  AttachmentWidget $widget The widget displaying attachments.
     * @return string
     */
    public function setAttachmentWidget(AttachmentWidget $widget)
    {
        $this->attachmentWidget = $widget;

        return $this;
    }



// Abstract Methods
// =============================================================================

    /**
     * Retrieve the object's type identifier.
     *
     * @return string
     */
    abstract function objType();

    /**
     * Retrieve the object's unique ID.
     *
     * @return mixed
     */
    abstract function id();

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

<?php

namespace Charcoal\Attachment\Object;

// Dependency from 'charcoal-core'
use \Charcoal\Model\AbstractModel;

/**
 * Relationship table to join attachments and objects.
 */
class Join extends AbstractModel
{
    /**
     * The source object type.
     *
     * @var string
     */
    protected $objectType;

    /**
     * The source object ID.
     *
     * @var mixed
     */
    protected $objectId;

    /**
     * The attached object ID.
     *
     * @var mixed
     */
    protected $attachmentId;

    /**
     * The attached group ID.
     *
     * @var string
     */
    protected $group;

    /**
     * The attachment's position amongst other attachments.
     *
     * @var integer
     */
    protected $position;



// Setters
// =============================================================================

    /**
     * Set the source object type.
     *
     * @param string $type The object type identifier.
     * @throws InvalidArgumentException If provided argument is not of type 'string'.
     * @return self
     */
    public function setObjectType($type)
    {
        if (!is_string($type)) {
            throw new InvalidArgumentException('Object type must be a string.');
        }

        $this->objectType = $type;

        return $this;
    }

    /**
     * Set the source object ID.
     *
     * @param mixed $id The object ID to join the attachment to.
     * @throws InvalidArgumentException If provided argument is not a string or numerical value.
     * @return self
     */
    public function setObjectId($id)
    {
        if (!is_scalar($id)) {
            throw new InvalidArgumentException(
                'Object ID must be a string or numerical value.'
            );
        }

        $this->objectId = $id;

        return $this;
    }

    /**
     * Set the related attachment ID.
     *
     * @param mixed $id The object ID to attach.
     * @throws InvalidArgumentException If provided argument is not a string or numerical value.
     * @return self
     */
    public function setAttachmentId($id)
    {
        if (!is_scalar($id)) {
            throw new InvalidArgumentException(
                'Attachment ID must be a string or numerical value.'
            );
        }

        $this->attachmentId = $id;

        return $this;
    }

    /**
     * Set the relations group ID.
     *
     * @param mixed $id The group ID describing the relationship.
     * @throws InvalidArgumentException If provided argument is not a string.
     * @return self
     */
    public function setGroup($id)
    {
        if (!is_string($id)) {
            throw new InvalidArgumentException(
                'Grouping ID must be a string.'
            );
        }

        $this->group = $id;

        return $this;
    }

    /**
     * Define the relation's position amongst other attachments
     * related to the object to attach to.
     *
     * @param integer $position The position (for ordering purpose).
     * @throws InvalidArgumentException If the position is not an integer (or numeric integer string).
     * @return self
     */
    public function setPosition($position)
    {
        if ($position === null) {
            $this->position = null;
            return $this;
        }

        if (!is_numeric($position)) {
            throw new InvalidArgumentException(
                'Position must be an integer.'
            );
        }

        $this->position = (int)$position;

        return $this;
    }



// Getters
// =============================================================================

    /**
     * Retrieve the source object type.
     *
     * @return string
     */
    public function objectType()
    {
        return $this->objectType;
    }

    /**
     * Retrieve the source object ID.
     *
     * @return mixed
     */
    public function objectId()
    {
        return $this->objectId;
    }

    /**
     * Retrieve the related attachment ID.
     *
     * @return mixed
     */
    public function attachmentId()
    {
        return $this->attachmentId;
    }

    /**
     * Retrieve the relationship grouping ID.
     *
     * @return mixed
     */
    public function group()
    {
        return $this->group;
    }

    /**
     * Retrieve the attachment's position.
     *
     * @return integer
     */
    public function position()
    {
        return $this->position;
    }
}

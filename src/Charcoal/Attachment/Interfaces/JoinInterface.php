<?php

namespace Charcoal\Attachment\Interfaces;

/**
 * Defines an intermediate table for object relationships.
 */
interface JoinInterface
{
    /**
     * Retrieve the source object of the relationship.
     *
     * @return ModelInterface|null
     */
    public function getObject();

    /**
     * Retrieve the related object of the relationship.
     *
     * @return ModelInterface|null
     */
    public function getAttachment();

    /**
     * Retrieve the parent relationship, if any.
     *
     * @return JoinInterface|null
     */
    public function getParent();

    /**
     * Retrieve the master relationship (top-level), if any.
     *
     * @return JoinInterface|null
     */
    public function getMaster();

    /**
     * Retrieve this relationship's ancestors.
     *
     * @return JoinInterface[]
     */
    public function hierarchy();



// Setters
// =============================================================================

    /**
     * Set the source object type.
     *
     * @param  string $type The object type identifier.
     * @return JoinInterface Chainable
     */
    public function setObjectType($type);

    /**
     * Set the source object ID.
     *
     * @param  mixed $id The object ID to join the attachment to.
     * @return JoinInterface Chainable
     */
    public function setObjectId($id);

    /**
     * Set the related attachment ID.
     *
     * @param  mixed $id The object ID to attach.
     * @return JoinInterface Chainable
     */
    public function setAttachmentId($id);

    /**
     * Set the relationship's group ID.
     *
     * @param  mixed $id The group ID describing the relationship.
     * @return JoinInterface Chainable
     */
    public function setGroup($id);

    /**
     * Set the relationship's position.
     *
     * @param  integer $position A position.
     * @return JoinInterface Chainable
     */
    public function setPosition($position);

    /**
     * Enable/Disable the relationship.
     *
     * @param  boolean $active The active flag.
     * @return JoinInterface Chainable
     */
    public function setActive($active);



// Getters
// =============================================================================

    /**
     * Retrieve the source object type.
     *
     * @return string
     */
    public function objectType();

    /**
     * Retrieve the source object ID.
     *
     * @return mixed
     */
    public function objectId();

    /**
     * Retrieve the related attachment ID.
     *
     * @return mixed
     */
    public function attachmentId();

    /**
     * Retrieve the relationship's group ID.
     *
     * @return mixed
     */
    public function group();

    /**
     * Retrieve the relationship's position.
     *
     * @return integer
     */
    public function position();

    /**
     * Determine if the relationship is enabled.
     *
     * @return boolean
     */
    public function active();
}

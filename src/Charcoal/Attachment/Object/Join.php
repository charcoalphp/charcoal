<?php
namespace Charcoal\Attachment\Object;

// From Charcoal-Core
use \Charcoal\Model\AbstractModel;

/**
 * Third table to join attachments and objects.
 * Extends AbstractModel in order to be saved
 * and have metadata.
 */
class Join extends AbstractModel
{
	/**
	 * What object has the attachment?
	 * @var string 	objType
	 * @var mixed 	objId
	 */
	protected $objectType;
	protected $objectId;

	/**
	 * What object is attached?
	 * @var mixed 	attachmentId
	 */
	protected $attachmentId;

	/**
	 * Position of the said attachment
	 * Position should be set by objType and objId
	 * Without giving shit about the attachmentType/Id
	 * @var integer $position
	 */
	protected $position;


/**
 * Setters
 */
	public function setObjectType($type)
	{
		$this->objectType = $type;
		return $this;
	}
	public function setObjectId($id)
	{
		$this->objectId = $id;
		return $this;
	}
	public function setAttachmentId($id)
	{
		$this->attachmentId = $id;
		return $this;
	}

    /**
     * @param integer $position The position (for ordering purpose).
     * @throws InvalidArgumentException If the position is not an integer (or numeric integer string).
     * @return Content Chainable
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

/**
 * Getter
 */
	public function objectType()
	{
		return $this->objectType;
	}
	public function objectId()
	{
		return $this->objectId;
	}
	public function attachmentId()
	{
		return $this->attachmentId;
	}
    /**
     * @return integer
     */
    public function position()
    {
        return $this->position;
    }
}

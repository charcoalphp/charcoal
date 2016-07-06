<?php
namespace Charcoal\Attachment\Traits;

// From Charcoal
use \Charcoal\Loader\CollectionLoader;

// From Charcoal\Attachment
use \Charcoal\Attachment\Object\Join;
use \Charcoal\Attachment\Object\Attachment;
use \Charcoal\Attachment\Object\File;
use \Charcoal\Attachment\Object\Image;
use \Charcoal\Attachment\Object\Text;
use \Charcoal\Attachment\Object\Video;

/**
 * AttachmentAwareTrait used on objects that can have
 * an attachment. This is the glue between the Join object
 * and the current object.
 *
 * Abstract method needs to be implemented.
 * @see AttachmentAwareInterface
 */
trait AttachmentAwareTrait
{
	/**
	 * Optimize.
	 * @var Collection Mixed
	 */
	protected $attachments = [];

	/**
	 * Attachments of the current object.
     * @param string $group Group ident | null.
	 * @return Collection MIXED.
	 */
	public function attachments($group = null)
	{
        if ($group && isset($this->attachments[$group])) {
            return $this->attachments[$group];
        }
		if (!$group && isset($this->attachments[0])) {
			return $this->attachments[0];
		}
		$objType = $this->objType();
		$id = $this->id();

		$join = $this->modelFactory()->get(Join::class);
		$joinTable = $join->source()->table();

		$attachment = $this->modelFactory()->get(Attachment::class);


        if (!$attachment->source()->tableExists() || !$join->source()->tableExists()) {
            return [];
        }

		$attachmentTable = $attachment->source()->table();

		$obj = $this->modelFactory()->get($objType);
		$objTable = $obj->source()->table();

		$q = 'SELECT
				attachment.*,
				joined.attachment_id as attachment_id,
				joined.position as position
			FROM
				`'.$attachmentTable.'` as attachment
			LEFT JOIN
				`'.$joinTable.'` as joined
			ON
				joined.attachment_id = attachment.id
			WHERE
				joined.object_type = \''.$objType.'\'
			AND
				joined.object_id = \''.$id.'\'
			AND
				attachment.active = 1';

        if ($group) {
            $q .= '
            AND
            joined.group = \''.$group.'\'';
        }

        $q .= '
			ORDER BY joined.position';

		$this->logger->debug($q);

		$loader = $this->collectionLoader(Attachment::class);
		$loader->setDynamicTypeField('type');
		$collection = $loader->loadFromQuery($q);

        $group = $group ?: 0;

		$this->attachments[$group] = $collection;

		return $this->attachments[$group];
	}

    /**
     * Does the object has attachments or not? Returns
     * a boolean with the number of attachments.
     * @return boolean Num of attachments.
     */
	public function hasAttachments()
	{
		return !!($this->numAttachments());
	}

    /**
     * Number of attachments associated with the current object.
     * Only a count on the attachments() method.
     * @return Integer Number of attachments.
     */
	public function numAttachments()
	{
		return count($this->attachments());
	}

    /**
     * Add attachment to the current object.
     * @param Attachment $attachment Attachment to be added.
     */
	public function addAttachment($attachment)
	{
		if (!$attachment) {
			return false;
		}

		$join = $this->modelFactory()->create(Join::class);

		$id = $this->id();
		$objType = $this->objType();
		$attachmentId = $attachment->id();

		$join->setAttachmentId($attachmentId);
		$join->setObjId($id);
		$join->setObjType($objType);

		$join->save();

		return $this;
	}

/**
 * UTILS
 */
    /**
     * @param string $objType
     * @return CollectionLoader
     */
    public function collectionLoader($objType)
    {
        $obj = $this->modelFactory()->create($objType);
        $loader = new CollectionLoader([
            'logger'=>$this->logger,
            'factory' => $this->modelFactory()
        ]);
        $loader->setModel($obj);
        return $loader;
    }

    /**
     * Remove all joins linked to a specific attachment.
     * @return boolean true.
     */
    public function removeJoins()
    {
        $loader = $this->collectionLoader(Join::class);
        $loader->addFilter('object_type', $this->objType())->addFilter('object_id', $this->id());
        $collection = $loader->load();

        foreach ($collection as $o) {
            $o->delete();
        }

        return true;
    }

/**
 * ABSTRACTS
 */

    /**
     * Base.
     * @return string   obj_type
     * @return mixed    obj_id
     */
    abstract function objType();
    abstract function id();

    /**
     * Current modelFactory.
     * @return FactoryInterface ModelFactory
     */
    abstract function modelFactory();
}

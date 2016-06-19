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
     * Protos to be kept in an associative array.
     * @var array $proto
     */
    protected $proto = [];

    /**
     * Collection loader
     * @var CollectLoader $loader
     */
    protected $loader;

	/**
	 * Optimize.
	 * @var Collection Mixed
	 */
	protected $attachments;

	/**
	 * Attachments of the current object.
	 * @return Collection MIXED.
	 */
	public function attachments()
	{
		if ($this->attachments) {
			return $this->attachments;
		}
		$objType = $this->objType();
		$id = $this->id();

		$join = $this->proto(Join::class);
		$joinTable = $join->source()->table();

		$attachment = $this->proto(Attachment::class);

        if (!$attachment->source()->tableExists()) {
            return [];
        }

		$attachmentTable = $attachment->source()->table();

		$obj = $this->proto($objType);
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
				attachment.active = 1
			ORDER BY joined.position';

		$this->logger->debug($q);

		$loader = $this->collection(Attachment::class);
		$loader->setDynamicTypeField('type');
		$collection = $loader->loadFromQuery($q);

		$this->attachments = $collection;
		return $this->attachments;
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

		$join = $this->obj(Join::class);

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
     * Returns a model prototype
     * Not to be used when calling multiple object
     * instances.
     *
     * @param  string $objType Class name.
     * @return Object          A proto of the wanted object.
     */
    public function proto($objType)
    {
        if (isset($this->proto[$objType])) {
            return $this->proto[$objType];
        }
        $this->proto[$objType] = $this->obj($objType);

        return $this->proto[$objType];
    }

    /**
     * Return new instance of objType no matter what
     *
     * @param  string   $objType
     * @return Object   Object of the specified objType.
     */
    public function obj($objType)
    {
        $factory = $this->modelFactory();
        $obj = $factory->create($objType);
        return $obj;
    }

    /**
     * @param string $objType
     * @return CollectionLoader
     */
    public function collection($objType)
    {
        $obj = $this->obj($objType);
        $loader = new CollectionLoader([
            'logger'=>$this->logger,
            'factory' => $this->modelFactory()
        ]);
        $loader->setModel($obj);
        return $loader;
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

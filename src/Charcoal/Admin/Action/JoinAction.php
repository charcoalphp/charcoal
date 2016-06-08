<?php
namespace Charcoal\Admin\Action;

use \Exception;

use \Pimple\Container;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

// From Charcoal\Admin
use \Charcoal\Admin\AdminAction;

// From Charcoal\Attachment
// Actual available attachments.
use \Charcoal\Attachment\Object\Join;

class JoinAction extends AdminAction
{
	/**
	 * @param RequestInterface  $request  A PSR-7 compatible Request instance.
	 * @param ResponseInterface $response A PSR-7 compatible Response instance.
	 * @return ResponseInterface
	 */
	public function run(RequestInterface $request, ResponseInterface $response)
	{
		$params = $request->getParams();

		if (!isset($params['attachments']) || !isset($params['obj_id']) || !isset($params['obj_type'])) {
			$this->setSuccess(false);
			return $response;
		}

		$attachments = $params['attachments'];
		$objId = $params['obj_id'];
		$objType = $params['obj_type'];

		// Need more attachments...
		if (!count($attachments)) {
			$this->setSuccess(false);
			return $response;
		}

		// Try loading the object
		try {
			$obj = $this->obj($objType)->load($objId);
		} catch (Exception $e) {
			// Invalid object
			$this->setSuccess(false);
			return $response;
		}

		// Clean all previously attached object and start it NEW
		$loader = $this->collection(Join::class);
		$loader->addFilter('object_type', $objType)
            ->addFilter('object_id', $objId)
            ->addOrder('position', 'asc');
		$existing_joins = $loader->load();
		foreach ($existing_joins as $j)
		{
			$j->delete();
		}


		$count = count($attachments);
		$i = 0;
		for (; $i<$count; $i++) {
			$attachmentId = $attachments[$i]['attachment_id'];
			$position = $attachments[$i]['position'];

			$join = $this->obj(Join::class)
                ->setObjectType($objType)
                ->setObjectId($objId)
                ->setAttachmentId($attachmentId)
                ->setPosition($position);

			$join->save();
		}


		$this->setSuccess(true);

		return $response;
	}
}

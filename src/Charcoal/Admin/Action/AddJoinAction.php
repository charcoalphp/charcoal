<?php
namespace Charcoal\Admin\Action;

use \Exception;

use \Pimple\Container;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

// From Charcoal\Admin
use \Charcoal\Admin\AdminAction;

// From Charcoal
use \Charcoal\Loader\CollectionLoader;

// From Charcoal\Attachment
// Actual available attachments.
use \Charcoal\Attachment\Object\Join;

class AddJoinAction extends AdminAction
{
    /**
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        $params = $request->getParams();

        if (!isset($params['attachments']) || !isset($params['obj_id']) || !isset($params['obj_type']) || !isset($params['group'])) {
            $this->setSuccess(false);
            return $response;
        }

        $attachments = $params['attachments'];
        $objId = $params['obj_id'];
        $objType = $params['obj_type'];
        $group = $params['group'];

        // Need more attachments...
        if (!count($attachments)) {
            $this->setSuccess(false);
            return $response;
        }

        // Try loading the object
        try {
            $obj = $this->modelFactory()->create($objType)->load($objId);
        } catch (Exception $e) {
            // Invalid object
            $this->setSuccess(false);
            return $response;
        }

        $joinProto = $this->modelFactory()->create(Join::class);
        if (!$joinProto->source()->tableExists()) {
            $joinProto->source()->createTable();
        }

        // Clean all previously attached object and start it NEW

        $loader = new CollectionLoader([
            'logger'=>$this->logger,
            'factory' => $this->modelFactory()
        ]);
        $loader->setModel($joinProto);

        $loader->addFilter('object_type', $objType)
            ->addFilter('object_id', $objId)
            ->addFilter('group', $group)
            ->addOrder('position', 'asc');
        $existing_joins = $loader->load();

        $nextPosition = count($existing_joins);

        $count = count($attachments);
        $i = 0;
        for (; $i<$count; $i++) {
            $attachmentId = $attachments[$i]['attachment_id'];

            $join = $this->modelFactory()->create(Join::class)
                ->setObjectType($objType)
                ->setObjectId($objId)
                ->setAttachmentId($attachmentId)
                ->setGroup($group)
                ->setPosition($nextPosition);

            $join->save();
        }


        $this->setSuccess(true);

        return $response;
    }
}

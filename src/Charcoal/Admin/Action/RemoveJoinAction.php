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

class RemoveJoinAction extends AdminAction
{
    /**
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        $params = $request->getParams();

        if (!isset($params['attachment_id']) || !isset($params['obj_id']) || !isset($params['obj_type']) || !isset($params['group'])) {
            $this->setSuccess(false);
            return $response;
        }

        $attachmentId = $params['attachment_id'];
        $objId = $params['obj_id'];
        $objType = $params['obj_type'];
        $group = $params['group'];

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

        $loader = new CollectionLoader([
            'logger'=>$this->logger,
            'factory' => $this->modelFactory()
        ]);
        $loader->setModel($joinProto);

        $loader->addFilter('object_type', $objType)
            ->addFilter('object_id', $objId)
            ->addFilter('attachment_id', $attachmentId)
            ->addFilter('group', $group);

        $existing_joins = $loader->load();

        // Should be just one, tho.
        foreach ($existing_joins as $j)
        {
            $j->delete();
        }


        $this->setSuccess(true);

        return $response;
    }
}

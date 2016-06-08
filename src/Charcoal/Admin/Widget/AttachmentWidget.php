<?php

namespace Charcoal\Admin\Widget;

use ArrayIterator;
// Dependencies from `pimple`
use \Pimple\Container;

use \Charcoal\Admin\AdminWidget;
use \Charcoal\Admin\Widget\TableWidget;

use \Charcoal\Loader\CollectionLoader;
use \Charcoal\Model\ModelFactory;

use Charcoal\Admin\Widget\AddAttachmentWidget;

/**
 * The main difference between this widget and
 * the addAttachmentWidget is the file manager
 * provided with this widget. That "file manager"
 * is yet to be developed, as it is in an experimental
 * phase right now.
 * @date 8/06/2016
 * @author Bene <ben@locomotive.ca>
 */
class AttachmentWidget extends AddAttachmentWidget
{
	/**
	 * All available attachments.
	 *
	 * @var Collection Attachments
	 */
	protected $attachmentCollection;

    /**
     * @return PaginationWidget
     */
    public function tableWidget($type)
    {
    	$table = $this->widgetFactory()->create('charcoal/admin/widget/table-grid');
        $table->setData([
            'collection_ident' => 'attachment',
            'obj_type' => $type
        ]);
        $table->setCollection($this->attachmentCollection($type));
        return $table;
    }

    /**
     * Attachment types with their collection
     * Overrides AddAttachmentWidget attachmentTypes function.
     * @return Array All attachment types
     */
	public function attachmentTypes()
	{
		$attachments = $this->attachments();
		$out = [];
		$i = 0;
		foreach ($attachments as $k => $val)
		{
			$i++;
			$label = $val['label'];

			$out[] = [
				'ident' => $this->createIdent($k),
				'label' => $label,
				'val' => $k,
				'collection' => $this->attachmentCollection($k),
				'table' => $this->tableWidget($k),
				'active' => ($i == 1)
			];
		}
		return $out;
	}

    /**
     * Returns a collection of an object
     *
     * @param  string $type  Object type. Must be Attachable.
     * @return array         Collection of object | empty
     */
    public function attachmentCollection($type)
    {
    	$attachments = $this->attachments();
    	if (!isset($attachments[$type])) {
    		return false;
    	}

    	$filters = $attachments[$type]['filters'];
    	$orders = $attachments[$type]['orders'];

    	$factory = $this->modelFactory();
    	$obj = $factory->create($type);

    	$loader = $this->attachmentCollectionLoader($type);
		$loader->setDynamicTypeField('type');

    	$loader->addFilter('type', $type);

    	foreach ($filters as $f) {
    		$loader->addFilter($f);
    	}
    	foreach ($orders as $o) {
    		$loader->addOrder($o);
    	}

    	if ($attachments[$type]['numPerPage']) {
    		$loader->setNumPerPage($attachments[$type]['numPerPage']);
    		$loader->setPage($attachments[$type]['page']);
    	}

    	$alreadyUsed = $this->objectAttachments();
    	$loader->addFilter([
			'property' => 'id',
			'val' => $alreadyUsed,
			'operator' => 'NOT IN'
		]);

    	$this->attachmentCollection[$type] = $loader->load();



    	return $this->attachmentCollection[$type];
    }

    /**
     * Base loader to prevent multiple metadata loads
     * Adds filter active and order position
     * @return CollectionLoader
     */
    public function attachmentCollectionLoader($type)
    {
    	$factory = $this->modelFactory();
		$obj = $factory->create($type);
    	$loader = new CollectionLoader([
            'logger'=>$this->logger,
			'factory' => $this->modelFactory()
        ]);
        $loader->setModel($obj);
        $loader->addFilter('active', true)->addOrder('position', 'asc');

        return $loader;
    }

    /**
     * Object Attachment IDs
     * @return Array IDs of attachments.
     */
    public function objectAttachments()
    {
    	if ($this->objectAttachments) {
    		return $this->objectAttachments;
    	}

    	$attachments = $this->obj()->attachments();
    	$out = [];

    	foreach ($attachments as $a) {
    		$out[] = $a->id();
    		// $out[] = $a->type().$a->id();
    	}

    	$this->objectAttachments = $out;
    	return $this->objectAttachments;
    }

}

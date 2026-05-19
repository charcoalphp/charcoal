<?php

declare(strict_types=1);

namespace Charcoal\Admin\Widget;

// From 'charcoal-object'
use Charcoal\Object\ObjectRevisionInterface;
// From 'charcoal-admin'
use Charcoal\Admin\AdminWidget;
use Charcoal\Admin\Ui\ObjectRevisionsInterface;
use Charcoal\Admin\Ui\ObjectRevisionsTrait;

/**
 * Class ObjectRevisionWidget
 */
class ObjectRevisionsWidget extends AdminWidget implements
    ObjectRevisionsInterface
{
    use ObjectRevisionsTrait;

    /**
     * @var string
     */
    protected $objType;

    /**
     * @var string|integer
     */
    protected $objId;

    #[\Override]
    public function active(): bool
    {
        return parent::active() && $this->objType() && $this->objId();
    }

    /**
     * @return string
     */
    public function objType()
    {
        return $this->objType;
    }

    /**
     * @param  string $objType ObjType for ObjectRevisionsWidget.
     */
    public function setObjType($objType): static
    {
        $this->objType = $objType;

        return $this;
    }

    /**
     * @return integer|string
     */
    public function objId()
    {
        return $this->objId;
    }

    /**
     * @param  string|integer $objId ObjId for ObjectRevisionsWidget.
     */
    public function setObjId($objId): static
    {
        $this->objId = $objId;

        return $this;
    }

    /**
     * Retrieve the default data sources (when setting data on an entity).
     *
     * @return string[]
     */
    #[\Override]
    protected function defaultDataSources(): array
    {
        return [
            static::DATA_SOURCE_REQUEST,
            static::DATA_SOURCE_OBJECT,
        ];
    }

    /**
     * Retrieve the accepted metadata from the current request.
     */
    protected function acceptedRequestData(): array
    {
        return ['obj_type', 'obj_id', 'template'];
    }
}

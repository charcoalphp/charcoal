<?php

namespace Charcoal\Attachment\Object;

use Exception;
use LogicException;
use RuntimeException;
use InvalidArgumentException;
// From Pimple
use Pimple\Container as ServiceContainer;
// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;
// From 'charcoal-core'
use Charcoal\Model\AbstractModel;
// From 'charcoal-attachment'
use Charcoal\Attachment\Interfaces\AttachmentContainerInterface;
use Charcoal\Attachment\Interfaces\JoinInterface;
use Charcoal\Attachment\Object\Attachment;

/**
 * Intermediate table for object to attachment relationships.
 */
class Join extends AbstractModel implements
    JoinInterface
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
     * The related object ID.
     *
     * @var mixed
     */
    protected $attachmentId;

    /**
     * The relationship's group ID.
     *
     * @var string
     */
    protected $group;

    /**
     * Whether the relationship is enabled.
     *
     * @var boolean
     */
    protected $active = true;

    /**
     * The relationship's position amongst other attachments.
     *
     * @var integer
     */
    protected $position = 0;

    /**
     * The parent relationship.
     *
     * @var JoinInterface|null
     */
    protected $parentRelation;

    /**
     * The source object of the relationship.
     *
     * @var ModelInterface|null
     */
    protected $sourceObject;

    /**
     * The related object of the relationship.
     *
     * @var ModelInterface|null
     */
    protected $relatedObject;

    /**
     * Track whether the parent relationship is resolved.
     *
     * @var boolean
     */
    protected $isParentRelationResolved = false;

    /**
     * Track whether the source object is resolved.
     *
     * @var boolean
     */
    protected $isSourceObjectResolved = false;

    /**
     * Track whether the related object is resolved.
     *
     * @var boolean
     */
    protected $isRelatedObjectResolved = false;

    /**
     * Store relationship's ancestry.
     *
     * @var JoinInterface[]|null
     */
    private $hierarchy;

    /**
     * Store the factory instance.
     *
     * @var FactoryInterface
     */
    private $modelFactory;

    /**
     * Set the model's dependencies.
     *
     * @param  ServiceContainer $container Service container.
     * @return void
     */
    protected function setDependencies(ServiceContainer $container)
    {
        parent::setDependencies($container);

        $this->setModelFactory($container['model/factory']);
    }



// Utilities
// =============================================================================

    /**
     * Retrieve the source object of the relationship.
     *
     * @throws LogicException If the relationship is broken or incomplete.
     * @return ModelInterface|null
     */
    public function getObject()
    {
        if ($this->sourceObject === null && $this->isSourceObjectResolved === false) {
            $this->isSourceObjectResolved = true;

            try {
                $model = $this->modelFactory()->create($this->objectType())->load($this->objectId());
                if ($model->id()) {
                    $this->sourceObject = $model;
                }
            } catch (Exception $e) {
                throw new LogicException(sprintf(
                    'Could not load the source object of the relationship: %s',
                    $e->getMessage()
                ), 0, $e);
            }
        }

        return $this->sourceObject;
    }

    /**
     * Retrieve the related object of the relationship.
     *
     * @throws LogicException If the relationship is broken or incomplete.
     * @return ModelInterface|null
     */
    public function getAttachment()
    {
        if ($this->relatedObject === null && $this->isRelatedObjectResolved === false) {
            $this->isRelatedObjectResolved = true;

            try {
                $model = $this->modelFactory()->create(Attachment::class)->load($this->attachmentId());
                if ($model->id()) {
                    $this->relatedObject = $model;

                    $type = $model->type();
                    if ($type !== $model->objType()) {
                        $this->relatedObject = $this->modelFactory()->create($type)->setData($model->data());
                    }
                }
            } catch (Exception $e) {
                throw new LogicException(sprintf(
                    'Could not load the related object of the relationship: %s',
                    $e->getMessage()
                ), 0, $e);
            }
        }

        return $this->relatedObject;
    }

    /**
     * Retrieve the parent relationship, if any.
     *
     * @todo   Add support for multiple parents.
     * @throws LogicException If the relationship is broken or incomplete.
     * @return JoinInterface|null
     */
    public function getParent()
    {
        if ($this->parentRelation === null && $this->isParentRelationResolved === false) {
            $this->isParentRelationResolved = true;

            try {
                $source = $this->getObject();
                if ($source instanceof AttachmentContainerInterface) {
                    $model = $this->modelFactory()->create(self::class)->loadFrom('attachment_id', $source->id());
                    if ($model->id()) {
                        $this->parentRelation = $model;
                    }
                }
            } catch (Exception $e) {
                throw new LogicException(sprintf(
                    'Could not load the parent relationship: %s',
                    $e->getMessage()
                ), 0, $e);
            }
        }

        return $this->parentRelation;
    }

    /**
     * Retrieve the master relationship (top-level), if any.
     *
     * @todo   Add support for multiple masters.
     * @throws LogicException If the relationship is broken or incomplete.
     * @return JoinInterface|null
     */
    public function getMaster()
    {
        $hierarchy = $this->invertedHierarchy();
        if (isset($hierarchy[0])) {
            return $hierarchy[0];
        } else {
            return null;
        }
    }

    /**
     * Reset this relationship's hierarchy.
     *
     * The relationship's hierarchy can be rebuilt with {@see self::hierarchy()}.
     *
     * @return self
     */
    public function resetHierarchy()
    {
        $this->hierarchy = null;

        return $this;
    }

    /**
     * Retrieve this relationship's ancestors (from immediate parent to master).
     *
     * @return JoinInterface[]
     */
    public function hierarchy()
    {
        if ($this->hierarchy === null) {
            $hierarchy = [];

            $parent = $this->getParent();
            while ($parent) {
                $hierarchy[] = $parent;
                $parent = $parent->getParent();
            }

            $this->hierarchy = $hierarchy;
        }

        return $this->hierarchy;
    }

    /**
     * Retrieve this relationship's ancestors, inverted from master to immediate.
     *
     * @return JoinInterface[]
     */
    public function invertedHierarchy()
    {
        return array_reverse($this->hierarchy());
    }



// Setters
// =============================================================================

    /**
     * Set the source object type.
     *
     * @param  string $type The object type identifier.
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
     * @param  mixed $id The object ID to join the attachment to.
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
     * @param  mixed $id The object ID to attach.
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
     * Set the relationship's group ID.
     *
     * @param  mixed $id The group ID describing the relationship.
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
     * Set the relationship's position.
     *
     * Define the relation's sorting position amongst other attachments
     * related to the source object and grouping.
     *
     * @param  integer $position A position.
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

    /**
     * Enable/Disable the relationship.
     *
     * @param  boolean $active The active flag.
     * @return self
     */
    public function setActive($active)
    {
        $this->active = !!$active;

        return $this;
    }

    /**
     * Set an model factory.
     *
     * @param  FactoryInterface $factory The factory to create models.
     * @return self
     */
    protected function setModelFactory(FactoryInterface $factory)
    {
        $this->modelFactory = $factory;

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
     * Retrieve the relationship's group ID.
     *
     * @return mixed
     */
    public function group()
    {
        return $this->group;
    }

    /**
     * Retrieve the relationship's position.
     *
     * @return integer
     */
    public function position()
    {
        return $this->position;
    }

    /**
     * Determine if the relationship is enabled.
     *
     * @return boolean
     */
    public function active()
    {
        return $this->active;
    }

    /**
     * Retrieve the model factory.
     *
     * @throws RuntimeException If the model factory is missing.
     * @return FactoryInterface
     */
    public function modelFactory()
    {
        if (!isset($this->modelFactory)) {
            throw new RuntimeException(sprintf(
                'Model Factory is not defined for [%s]',
                get_class($this)
            ));
        }

        return $this->modelFactory;
    }
}

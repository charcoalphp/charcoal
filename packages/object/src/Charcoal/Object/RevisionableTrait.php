<?php

namespace Charcoal\Object;

use InvalidArgumentException;
// From 'charcoal-core'
use Charcoal\Loader\CollectionLoader;
// From 'charcoal-object'
use Charcoal\Object\ObjectRevision;
use Charcoal\Object\ObjectRevisionInterface;

/**
 *
 */
trait RevisionableTrait
{
    /**
     * @var boolean $revisionEnabled
     */
    protected $revisionEnabled = true;

    /**
     * The class name of the object revision model.
     *
     * Must be a fully-qualified PHP namespace and an implementation of
     * {@see \Charcoal\Object\ObjectRevisionInterface}. Used by the model factory.
     *
     * @var string
     */
    private $objectRevisionClass = ObjectRevision::class;

    /**
     * @param  boolean $enabled The (revision) enabled flag.
     * @return RevisionableInterface Chainable
     */
    public function setRevisionEnabled($enabled)
    {
        $this->revisionEnabled = (bool)$enabled;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getRevisionEnabled()
    {
        return $this->revisionEnabled;
    }

    /**
     * Create a revision collection loader.
     */
    public function createRevisionObjectCollectionLoader(): \Charcoal\Loader\CollectionLoader
    {
        return new CollectionLoader([
            'logger'  => $this->logger,
            'factory' => $this->modelFactory(),
            'model'   => $this->getRevisionObjectPrototype(),
        ]);
    }

    /**
     * Create a revision object.
     *
     * @return ObjectRevisionInterface
     */
    public function createRevisionObject()
    {
        return $this->modelFactory()->create($this->getObjectRevisionClass());
    }

    /**
     * Retrieve the revision object prototype.
     *
     * @return ObjectRevisionInterface
     */
    public function getRevisionObjectPrototype()
    {
        return $this->modelFactory()->get($this->getObjectRevisionClass());
    }

    /**
     * Set the class name of the object revision model.
     *
     * @param  string $className The class name of the object revision model.
     * @throws InvalidArgumentException If the class name is not a string.
     * @return AbstractPropertyDisplay Chainable
     */
    protected function setObjectRevisionClass($className)
    {
        if (!is_string($className)) {
            throw new InvalidArgumentException(
                'Route class name must be a string.'
            );
        }

        $this->objectRevisionClass = $className;
        return $this;
    }

    /**
     * Retrieve the class name of the object revision model.
     *
     * @return string
     */
    public function getObjectRevisionClass()
    {
        return $this->objectRevisionClass;
    }

    /**
     * Alias of {@see self::getObjectRevisionClass()}.
     *
     * @return string
     */
    public function objectRevisionClass()
    {
        return $this->getObjectRevisionClass();
    }

    /**
     * @see \Charcoal\Object\ObjectRevision::create_fromObject()
     * @return ObjectRevision
     */
    public function generateRevision()
    {
        $rev = $this->createRevisionObject();

        $rev->createFromObject($this);
        if (!empty($rev->getDataDiff())) {
            $rev->save();
        }

        return $rev;
    }

    /**
     * @see \Charcoal\Object\ObejctRevision::lastObjectRevision
     * @return ObjectRevision
     */
    public function latestRevision()
    {
        $rev = $this->createRevisionObject();

        return $rev->lastObjectRevision($this);
    }

    /**
     * @see \Charcoal\Object\ObejctRevision::objectRevisionNum()
     *
     * @todo Should return NULL if source does not exist.
     *
     * @param  integer $revNum The revision number.
     * @return ObjectRevision
     */
    public function revisionNum($revNum)
    {
        $rev = $this->createRevisionObject();

        return $rev->objectRevisionNum($this, intval($revNum));
    }

    /**
     * Retrieves all revisions for the current objet
     *
     * @param  callable $callback Optional object callback.
     * @return array
     */
    public function allRevisions(?callable $callback = null)
    {
        $loader = $this->createRevisionObjectCollectionLoader();
        $loader
            ->addOrder('revTs', 'desc')
            ->addFilters([
                [
                    'property' => 'targetType',
                    'value'    => $this->objType(),
                ],
                [
                    'property' => 'targetId',
                    'value'    => $this->id(),
                ],
            ]);

        if ($callback !== null) {
            $loader->setCallback($callback);
        }

        $revisions = $loader->load();
        return $revisions->objects();
    }

    /**
     * @param  integer $revNum The revision number to revert to.
     * @throws InvalidArgumentException If revision number is invalid.
     * @return boolean Success / Failure.
     */
    public function revertToRevision($revNum): bool
    {
        if (!$revNum) {
            throw new InvalidArgumentException(
                'Invalid revision number'
            );
        }

        $rev = $this->revisionNum(intval($revNum));

        if (!$rev->id()) {
            return false;
        }

        if (isset($obj['lastModifiedBy'])) {
            $obj['lastModifiedBy'] = $rev->getRevUser();
        }

        $this->setData($rev->getDataObj());
        $this->update();

        return true;
    }

    /**
     * Retrieve the object model factory.
     *
     * @return \Charcoal\Factory\FactoryInterface
     */
    abstract public function modelFactory();

    /**
     * @return \Charcoal\Model\MetadataInterface
     */
    abstract public function metadata();
}

<?php

namespace Charcoal\Tests\Mock;

// From 'charcoal-core'
use Charcoal\Source\StorableTrait;
use Charcoal\Tests\Mock\StorableMock;

/**
 *
 */
class BadStorableMock extends GenericModel
{
    private bool $failAfter = false;
    private bool $failBefore = false;

    public function failBefore()
    {
        $this->failBefore = true;
    }

    public function failAfter()
    {
        $this->failAfter = true;
    }

    /**
     * Event called before {@see self::save() creating} the object.
     *
     * @see    StorableTrait::preSave()
     * @return boolean TRUE to proceed with creation; FALSE to stop creation.
     */
    protected function preSave()
    {
        return $this->failBefore;
    }

    /**
     * Event called after {@see self::save() creating} the object.
     *
     * @see    StorableTrait::postSave()
     * @return boolean TRUE to indicate object was created.
     */
    protected function postSave()
    {
        return $this->failAfter;
    }

    /**
     * Event called before {@see self::update() updating} the object.
     *
     * @see    StorableTrait::preUpdate()
     * @param  string[] $keys Optional list of properties to update.
     * @return boolean TRUE to proceed with update; FALSE to stop update.
     */
    protected function preUpdate(array $keys = null)
    {
        return $this->failBefore;
    }

    /**
     * Event called after {@see self::update() updating} the object.
     *
     * @see    StorableTrait::postUpdate()
     * @param  string[] $keys Optional list of properties to update.
     * @return boolean TRUE to indicate object was updated.
     */
    protected function postUpdate(array $keys = null)
    {
        return $this->failAfter;
    }

    /**
     * Event called before {@see self::delete() deleting} the object.
     *
     * @see    StorableTrait::preDelete()
     * @return boolean TRUE to proceed with deletion; FALSE to stop deletion.
     */
    protected function preDelete()
    {
        return $this->failBefore;
    }

    /**
     * Event called after {@see self::delete() deleting} the object.
     *
     * @see    StorableTrait::postDelete()
     * @return boolean TRUE to indicate object was deleted.
     */
    protected function postDelete()
    {
        return $this->failAfter;
    }
}

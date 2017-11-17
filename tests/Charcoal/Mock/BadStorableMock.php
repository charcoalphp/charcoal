<?php

namespace Charcoal\Tests\Mock;

// From 'charcoal-core'
use Charcoal\Source\StorableTrait;
use Charcoal\Tests\Mock\StorableMock;

/**
 *
 */
class BadStorableMock extends StorableMock
{
    const FAIL_AFTER  = false;
    const FAIL_BEFORE = true;

    /**
     * Whether to fail before or after an event.
     *
     * @var boolean
     */
    private $fail = self::FAIL_BEFORE;

    /**
     * Create new storable mock.
     *
     * @param boolean $fail TRUE to fail on pre-event, FALSE to fail on post-event.
     */
    public function __construct($fail = self::FAIL_BEFORE)
    {
        $this->fail = (bool)$fail;

        parent::__construct();
    }

    /**
     * Create new storable mock to fail on before events.
     *
     * @return static
     */
    public static function createToFailBefore()
    {
        return new self(self::FAIL_BEFORE);
    }

    /**
     * Create new storable mock to fail on after events.
     *
     * @return static
     */
    public static function createToFailAfter()
    {
        return new self(self::FAIL_AFTER);
    }

    /**
     * Event called before {@see self::save() creating} the object.
     *
     * @see    StorableTrait::preSave()
     * @return boolean TRUE to proceed with creation; FALSE to stop creation.
     */
    protected function preSave()
    {
        return $this->fail;
    }

    /**
     * Event called after {@see self::save() creating} the object.
     *
     * @see    StorableTrait::postSave()
     * @return boolean TRUE to indicate object was created.
     */
    protected function postSave()
    {
        return !$this->fail;
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
        return $this->fail;
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
        return !$this->fail;
    }

    /**
     * Event called before {@see self::delete() deleting} the object.
     *
     * @see    StorableTrait::preDelete()
     * @return boolean TRUE to proceed with deletion; FALSE to stop deletion.
     */
    protected function preDelete()
    {
        return $this->fail;
    }

    /**
     * Event called after {@see self::delete() deleting} the object.
     *
     * @see    StorableTrait::postDelete()
     * @return boolean TRUE to indicate object was deleted.
     */
    protected function postDelete()
    {
        return !$this->fail;
    }
}

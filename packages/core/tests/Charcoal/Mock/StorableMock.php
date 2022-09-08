<?php

namespace Charcoal\Tests\Mock;

use ArrayAccess;

// From PSR-3
use Psr\Log\NullLogger;

// From 'charcoal-core'
use Charcoal\Source\SourceInterface;
use Charcoal\Source\StorableInterface;
use Charcoal\Source\StorableTrait;
use Charcoal\Tests\Mock\SourceMock;

/**
 *
 */
class StorableMock implements
    ArrayAccess,
    StorableInterface
{
    use StorableTrait;

    /**
     * Model Datastore.
     *
     * @var array
     */
    private $data = [];

    /**
     * The logger instance.
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Create new storable mock.
     */
    public function __construct()
    {
        $this->logger = new NullLogger();
    }

    /**
     * Convert the current class name in "type-ident" format.
     *
     * @return string
     */
    public function objType()
    {
        $model = get_class($this);
        $model = preg_replace('/([a-z])([A-Z])/', '$1-$2', $model);
        $model = strtolower(str_replace('\\', '/', $model));
        return $model;
    }

    /**
     * Create a datasource repository for the model.
     *
     * @return SourceInterface A new repository.
     */
    protected function createSource()
    {
        return new SourceMock([
            'logger' => $this->logger
        ]);
    }

    /**
     * Whether an offset exists.
     *
     * @param  mixed $offset The offset to check for.
     * @return boolean Returns TRUE on success or FALSE on failure.
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * Offset to retrieve.
     *
     * @param  mixed $offset The offset to retrieve.
     * @return mixed The offset's value.
     */
    public function offsetGet($offset)
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }

    /**
     * Assign a value to the specified offset.
     *
     * @param  mixed $offset The offset to assign the value to.
     * @param  mixed $value  The value to set.
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if ($offset === null) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    /**
     * Unset an offset.
     *
     * @param  mixed $offset The offset to unset.
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }
}

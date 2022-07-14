<?php

namespace Charcoal\Source;

use RuntimeException;
// From 'charcoal-core'
use Charcoal\Model\ModelInterface;

/**
 * Provides awareness for an object model.
 */
trait ModelAwareTrait
{
    /**
     * The source's object model.
     *
     * @var ModelInterface
     */
    private $model;

    /**
     * Set the source's model.
     *
     * @param ModelInterface $model The source's model.
     * @return self
     */
    public function setModel(ModelInterface $model)
    {
        $this->model = $model;
        return $this;
    }

    /**
     * Retrieve the source's model.
     *
     * @throws RuntimeException If not model was previously set.
     * @return ModelInterface
     */
    public function model()
    {
        if ($this->model === null) {
            throw new RuntimeException(
                'Model is missing for source'
            );
        }
        return $this->model;
    }

    /**
     * Determine if the source has a model.
     *
     * @return boolean
     */
    public function hasModel()
    {
        return ($this->model !== null);
    }
}

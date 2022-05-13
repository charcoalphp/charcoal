<?php

namespace Charcoal\Source;

// From 'charcoal-core'
use Charcoal\Model\ModelInterface;

/**
 * Describes awareness for an object model.
 */
interface ModelAwareInterface
{
    /**
     * Set the source's model.
     *
     * @param ModelInterface $model The source's model.
     * @return self
     */
    public function setModel(ModelInterface $model);

    /**
     * Retrieve the source's model.
     *
     * @throws \Exception If not model was previously set.
     * @return ModelInterface
     */
    public function model();

    /**
     * Determine if the source has a model.
     *
     * @return boolean
     */
    public function hasModel();
}

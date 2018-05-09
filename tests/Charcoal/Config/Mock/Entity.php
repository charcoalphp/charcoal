<?php

namespace Charcoal\Tests\Config\Mock;

// From 'charcoal-config'
use Charcoal\Config\AbstractEntity;

/**
 * Mock object of {@see \Charcoal\Config\AbstractEntity}
 */
class Entity extends AbstractEntity
{
    /**
     * Create a new Entity.
     *
     * @param array $data Data to pre-populate the entity.
     */
    public function __construct(array $data = null)
    {
        if (!empty($data)) {
            $this->setData($data);
        }
    }
}

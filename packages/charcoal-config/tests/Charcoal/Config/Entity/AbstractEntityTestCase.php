<?php

namespace Charcoal\Tests\Config\Entity;

// From 'charcoal-config'
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Config\Mock\MacroEntity;
use Charcoal\Config\AbstractEntity;

/**
 * Base AbstractEntity Test
 */
abstract class AbstractEntityTestCase extends AbstractTestCase
{
    /**
     * Create a concrete MacroEntity instance.
     *
     * @param  array $data Data to assign to the object.
     * @return MacroEntity
     */
    public function createEntity(array $data = null)
    {
        return new MacroEntity($data);
    }

    /**
     * Create a mock instance of AbstractEntity.
     *
     * @param  array $data Data to assign to the object.
     * @return AbstractEntity
     */
    public function mockEntity(array $data = null)
    {
        $obj = $this->getMockForAbstractClass(AbstractEntity::class);

        if ($data !== null) {
            $obj->setData($data);
        }

        return $obj;
    }
}

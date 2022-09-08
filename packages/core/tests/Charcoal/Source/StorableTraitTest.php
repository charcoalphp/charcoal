<?php

namespace Charcoal\Tests\Source;

use InvalidArgumentException;
use RuntimeException;

// From PSR-3
use Psr\Log\NullLogger;

// From 'charcoal-factory'
use Charcoal\Factory\GenericFactory as Factory;

// From 'charcoal-core'
use Charcoal\Source\SourceInterface;
use Charcoal\Source\StorableInterface;
use Charcoal\Source\StorableTrait;

use Charcoal\Tests\Mock\BadStorableMock;
use Charcoal\Tests\Mock\StorableMock;
use Charcoal\Tests\Mock\SourceMock;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\ReflectionsTrait;

/**
 * Test {@see StorableTrait} and {@see StorableInterface}.
 */
class StorableTraitTest extends AbstractTestCase
{
    use ReflectionsTrait;

    /**
     * The tested class.
     *
     * @var StorableMock
     */
    public $obj;

    /**
     * Setup the test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->obj = new StorableMock();
    }

    /**
     * Create datasource repository for testing.
     *
     * @return SourceMock
     */
    final protected function createSource()
    {
        return new SourceMock([
            'logger' => new NullLogger()
        ]);
    }

    /**
     * Test the primary object key.
     *
     * Assertions:
     * 1. Default state
     * 2. Mutated state
     * 3. Chainable method
     *
     * @covers \Charcoal\Source\StorableTrait::setKey
     * @covers \Charcoal\Source\StorableTrait::key
     *
     * @return void
     */
    public function testKey()
    {
        $obj = $this->obj;

        /** 1. Default Value */
        $this->assertEquals('id', $obj->key());

        /** 2. Mutated Value */
        $that = $obj->setKey('foo_b4r');
        $this->assertEquals('foo_b4r', $obj->key());

        /** 3. Chainable */
        $this->assertSame($that, $obj);
    }

    /**
     * Test for invalid data type when assigning a primary object key.
     *
     * @covers \Charcoal\Source\StorableTrait::setKey
     *
     * @return void
     */
    public function testKeyWithInvalidDataType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->obj->setKey(null);
    }

    /**
     * Test for invalid character set when assigning a primary object key.
     *
     * @covers \Charcoal\Source\StorableTrait::setKey
     *
     * @return void
     */
    public function testKeyWithInvalidCharacters()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->obj->setKey('foo-bar');
    }

    /**
     * Test the unique object ID.
     *
     * Assertions:
     * 1. Default state
     * 2. Mutated state
     * 3. Chainable method
     *
     * @covers \Charcoal\Source\StorableTrait::setId
     * @covers \Charcoal\Source\StorableTrait::id
     *
     * @return void
     */
    public function testId()
    {
        $obj = $this->obj;

        /** 1. Default Value */
        $this->assertNull($obj->id());

        /** 2. Mutated Value */
        $that = $obj->setId('xyzzy');
        $this->assertEquals('xyzzy', $obj->id());

        $obj->setId(false);
        $this->assertEquals(false, $obj->id());

        $obj->setId(42);
        $this->assertEquals(42, $obj->id());

        /** 3. Chainable */
        $this->assertSame($that, $obj);
    }

    /**
     * Test for invalid data type when assigning a unique object ID.
     *
     * @covers \Charcoal\Source\StorableTrait::setId
     *
     * @return void
     */
    public function testIdWithInvalidDataType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->obj->setId(null);
    }

    /**
     * Test the unique object ID with an alternate primary key.
     *
     * @covers \Charcoal\Source\StorableTrait::setId
     * @covers \Charcoal\Source\StorableTrait::id
     *
     * @return void
     */
    public function testAltId()
    {
        $obj = $this->obj;

        $obj->setKey('foo')->setId(42);

        $this->assertEquals(42, $obj->id());
        $this->assertEquals(42, $obj['foo']);
    }

    /**
     * Test repository factory.
     *
     * @covers \Charcoal\Source\StorableTrait::setSourceFactory
     * @covers \Charcoal\Source\StorableTrait::sourceFactory
     *
     * @return void
     */
    public function testSourceFactory()
    {
        $obj = $this->obj;

        $factory = new Factory([
            'base_class' => SourceInterface::class,
            'arguments'  => [[
                'logger' => new NullLogger()
            ]]
        ]);

        $this->callMethodWith($obj, 'setSourceFactory', $factory);
        $this->assertSame($factory, $this->callMethod($obj, 'sourceFactory'));
    }

    /**
     * Test for missing repository factory.
     *
     * @covers \Charcoal\Source\StorableTrait::sourceFactory
     *
     * @return void
     */
    public function testMissingSourceFactory()
    {
        $this->expectException(RuntimeException::class);
        $this->callMethod($this->obj, 'sourceFactory');
    }

    /**
     * Test object repository.
     *
     * Assertions:
     * 1. Default state is NULL
     * 2. Create repository if state is NULL
     * 3. Mutated state
     * 4. Storable can create a repository
     * 5. Chainable method
     *
     * @covers \Charcoal\Source\StorableTrait::createSource
     * @covers \Charcoal\Source\StorableTrait::setSource
     * @covers \Charcoal\Source\StorableTrait::source
     *
     * @return void
     */
    public function testSource()
    {
        $obj = $this->obj;

        /** 1. Default state is NULL */
        $this->assertNull($this->getPropertyValue($obj, 'source'));

        /** 2. Create repository if state is NULL */
        $src1 = $obj->source();
        $this->assertInstanceOf(SourceInterface::class, $src1);
        $this->assertSame($src1, $this->getPropertyValue($obj, 'source'));

        /** 3. Mutated state */
        $src2 = $this->createSource();
        $that = $obj->setSource($src2);
        $this->assertSame($src2, $obj->source());
        $this->assertSame($src2, $this->getPropertyValue($obj, 'source'));

        /** 4. Storable can create a repository */
        $this->assertInstanceOf(SourceInterface::class, $this->callMethod($obj, 'createSource'));

        /** 5. Chainable */
        $this->assertSame($that, $obj);
    }

    /**
     * Test object save.
     *
     * Assertions:
     * 1. Success
     * 2. Fail Early
     * 3. Fail Late
     *
     * @covers \Charcoal\Source\StorableTrait::save
     * @covers \Charcoal\Source\StorableTrait::preSave
     * @covers \Charcoal\Source\StorableTrait::postSave
     *
     * @return void
     */
    public function testSave()
    {
        $src = $this->createSource();

        /** 1. Success */
        $obj = $this->obj;
        $obj->setSource($src);
        $this->assertTrue($obj->save());

        /** 2. Fail Early */
        $obj = BadStorableMock::createToFailBefore();
        $obj->setSource($src);
        $this->assertFalse($obj->save());

        /** 3. Fail Early */
        $obj = BadStorableMock::createToFailAfter();
        $obj->setSource($src);
        $this->assertFalse($obj->save());
    }

    /**
     * Test object update.
     *
     * Assertions:
     * 1. Success
     * 2. Fail Early
     * 3. Fail Late
     *
     * @covers \Charcoal\Source\StorableTrait::update
     * @covers \Charcoal\Source\StorableTrait::preUpdate
     * @covers \Charcoal\Source\StorableTrait::postUpdate
     *
     * @return void
     */
    public function testUpdate()
    {
        $src = $this->createSource();

        /** 1. Success */
        $obj = $this->obj;
        $obj->setSource($src);
        $this->assertTrue($obj->update());

        /** 2. Fail Early */
        $obj = BadStorableMock::createToFailBefore();
        $obj->setSource($src);
        $this->assertFalse($obj->update());

        /** 3. Fail Early */
        $obj = BadStorableMock::createToFailAfter();
        $obj->setSource($src);
        $this->assertFalse($obj->update());
    }

    /**
     * Test object delete.
     *
     * Assertions:
     * 1. Success
     * 2. Fail Early
     * 3. Fail Late
     *
     * @covers \Charcoal\Source\StorableTrait::delete
     * @covers \Charcoal\Source\StorableTrait::preDelete
     * @covers \Charcoal\Source\StorableTrait::postDelete
     *
     * @return void
     */
    public function testDelete()
    {
        $src = $this->createSource();

        /** 1. Success */
        $obj = $this->obj;
        $obj->setSource($src);
        $this->assertTrue($obj->delete());

        /** 2. Fail Early */
        $obj = BadStorableMock::createToFailBefore();
        $obj->setSource($src);
        $this->assertFalse($obj->delete());

        /** 3. Fail Early */
        $obj = BadStorableMock::createToFailAfter();
        $obj->setSource($src);
        $this->assertFalse($obj->delete());
    }
}

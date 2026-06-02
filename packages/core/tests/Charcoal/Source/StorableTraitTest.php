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
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Source\StorableTrait::class, 'setKey')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Source\StorableTrait::class, 'key')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Source\StorableTrait::class, 'setId')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Source\StorableTrait::class, 'id')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Source\StorableTrait::class, 'setSourceFactory')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Source\StorableTrait::class, 'sourceFactory')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Source\StorableTrait::class, 'createSource')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Source\StorableTrait::class, 'setSource')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Source\StorableTrait::class, 'source')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Source\StorableTrait::class, 'save')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Source\StorableTrait::class, 'preSave')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Source\StorableTrait::class, 'postSave')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Source\StorableTrait::class, 'update')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Source\StorableTrait::class, 'preUpdate')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Source\StorableTrait::class, 'postUpdate')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Source\StorableTrait::class, 'delete')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Source\StorableTrait::class, 'preDelete')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Source\StorableTrait::class, 'postDelete')]
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
     */
    protected function setUp(): void
    {
        $this->obj = new StorableMock();
    }

    /**
     * Create datasource repository for testing.
     */
    final protected function createSource(): \Charcoal\Tests\Mock\SourceMock
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
     */
    public function testKey(): void
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
     */
    public function testKeyWithInvalidDataType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->obj->setKey(null);
    }

    /**
     * Test for invalid character set when assigning a primary object key.
     *
     */
    public function testKeyWithInvalidCharacters(): void
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
     */
    public function testId(): void
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
     */
    public function testIdWithInvalidDataType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->obj->setId(null);
    }

    /**
     * Test the unique object ID with an alternate primary key.
     *
     */
    public function testAltId(): void
    {
        $obj = $this->obj;

        $obj->setKey('foo')->setId(42);

        $this->assertEquals(42, $obj->id());
        $this->assertEquals(42, $obj['foo']);
    }

    /**
     * Test repository factory.
     *
     */
    public function testSourceFactory(): void
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
     */
    public function testMissingSourceFactory(): void
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
     */
    public function testSource(): void
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
     */
    public function testSave(): void
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
     */
    public function testUpdate(): void
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
     */
    public function testDelete(): void
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

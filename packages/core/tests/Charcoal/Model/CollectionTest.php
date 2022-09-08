<?php

namespace Charcoal\Tests\Model;

use ArrayIterator;
use ArrayObject;
use CachingIterator;
use InvalidArgumentException;
use LogicException;
use ReflectionClass;

// From 'mockery/mockery'
use Mockery as m;

// From 'charcoal-core'
use Charcoal\Model\Model;
use Charcoal\Model\ModelInterface;
use Charcoal\Model\Collection;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class CollectionTest extends AbstractTestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    const OBJ_1 = '40ea';
    const OBJ_2 = '69c6';
    const OBJ_3 = '71b5';
    const OBJ_4 = 'dce3';
    const OBJ_5 = 'ea9f';

    /**
     * @var Model[] Ordered array of models.
     */
    protected $arr;

    /**
     * @var Model[] Associative arry of models.
     */
    protected $map;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->map = [
            self::OBJ_1 => m::mock(Model::class, [ 'id' => self::OBJ_1 ]),
            self::OBJ_2 => m::mock(Model::class, [ 'id' => self::OBJ_2 ]),
            self::OBJ_3 => m::mock(Model::class, [ 'id' => self::OBJ_3 ]),
            self::OBJ_4 => m::mock(Model::class, [ 'id' => self::OBJ_4 ]),
            self::OBJ_5 => m::mock(Model::class, [ 'id' => self::OBJ_5 ]),
        ];

        $i = 1;
        foreach ($this->map as &$mock) {
            $mock->shouldReceive('offsetGet')
                 ->with('position')
                 ->andReturn($i++);
        }

        $this->arr = array_values($this->map);
    }

    // Test \Charcoal\Model\CollectionInterface
    // =============================================================================================

    /**
     * @return void
     */
    public function testCollectionIsConstructed()
    {
        $c = new Collection;
        $this->assertSame([], $c->all());

        $c = new Collection(null);
        $this->assertSame([], $c->all());
    }

    /**
     * @return void
     */
    public function testConstructMethodWithAcceptableData()
    {
        [$o1] = $this->arr;
        $c = new Collection($o1);
        $this->assertSame([ self::OBJ_1 => $o1 ], $c->all());
    }

    /**
     * @return void
     */
    public function testConstructMethodWithUnacceptableData()
    {
        $this->expectException(InvalidArgumentException::class);
        $c = new Collection('foo');
    }

    /**
     * @return void
     */
    public function testConstructMethodFromArray()
    {
        $c = new Collection($this->arr);
        $this->assertEquals($this->map, $c->all());
    }

    /**
     * @return void
     */
    public function testConstructMethodFromTraversable()
    {
        $c = new Collection(new ArrayObject($this->arr));
        $this->assertEquals($this->map, $c->all());
    }

    /**
     * @return void
     */
    public function testConstructMethodFromCollection()
    {
        $c = new Collection(new Collection($this->arr));
        $this->assertEquals($this->map, $c->all());
    }

    /**
     * @return void
     */
    public function testValues()
    {
        $c = new Collection($this->arr);
        $this->assertEquals($this->arr, $c->values());
    }

    /**
     * @return void
     */
    public function testKeys()
    {
        $c = new Collection($this->arr);
        $this->assertEquals(array_keys($this->map), $c->keys());
    }

    /**
     * @return void
     */
    public function testBaseCollection()
    {
        $c = new Collection($this->arr);

        $this->assertInstanceOf(Collection::class, $c->toBase());
    }

    /**
     * @return void
     */
    public function testEmptyCollection()
    {
        $c = new Collection;

        $this->assertTrue($c->isEmpty());
        $this->assertEquals(null, $c->first());
        $this->assertEquals(null, $c->last());
    }

    /**
     * @return void
     */
    public function testFirstItemInCollection()
    {
        [$o1, $o2, $o3, $o4, $o5] = $this->arr;
        $c = new Collection($this->arr);

        $this->assertEquals($o1, $c->first());
    }

    /**
     * @return void
     */
    public function testLastItemInCollection()
    {
        [$o1, $o2, $o3, $o4, $o5] = $this->arr;
        $c = new Collection($this->arr);

        $this->assertEquals($o5, $c->last());
    }

    /**
     * @return void
     */
    public function testArrayableItems()
    {
        $c = new Collection;

        $class = new ReflectionClass($c);
        $method = $class->getMethod('asArray');
        $method->setAccessible(true);

        $items = new Collection($this->arr);
        $array = $method->invokeArgs($c, [ $items ]);
        $this->assertSame($this->map, $array);

        $items = new ArrayIterator($this->arr);
        $array = $method->invokeArgs($c, [ $items ]);
        $this->assertSame($this->arr, $array);

        $items = $this->arr;
        $array = $method->invokeArgs($c, [ $items ]);
        $this->assertSame($this->arr, $array);
    }

    /**
     * @return void
     */
    public function testRemoveKey()
    {
        [$o1] = $this->arr;

        $c = new Collection($this->arr);

        $c->remove(self::OBJ_2);
        $this->assertFalse(isset($c[self::OBJ_2]));

        $c->remove($o1);
        $this->assertFalse(isset($c[self::OBJ_1]));
    }

    /**
     * @return void
     */
    public function testAddAcceptableData()
    {
        [$o1] = $this->arr;
        $c = new Collection;
        $this->assertSame([ $o1->id() => $o1 ], $c->add($o1)->all());
    }

    /**
     * @return void
     */
    public function testAddUnacceptableData()
    {
        $this->expectException(InvalidArgumentException::class);
        $c = new Collection;
        $c->add('foo');
    }

    /**
     * @return void
     */
    public function testGet()
    {
        [$o1, $o2, $o3, $o4, $o5] = $this->arr;
        $c = new Collection($this->arr);
        $this->assertSame($o1, $c->get(self::OBJ_1));
        $this->assertSame($o1, $c->get($o1));
    }

    /**
     * @return void
     */
    public function testHas()
    {
        [$o1, $o2, $o3, $o4, $o5] = $this->arr;
        $c = new Collection($this->arr);
        $this->assertTrue($c->has(self::OBJ_1));
        $this->assertTrue($c->offsetExists($o2));
        $this->assertFalse($c->offsetExists('missing'));
    }

    /**
     * @return void
     */
    public function testClear()
    {
        $c = new Collection($this->arr);
        $this->assertSame([], $c->clear()->all());
    }

    /**
     * @return void
     */
    public function testMergeNull()
    {
        $c = new Collection($this->arr);
        $this->assertEquals($this->map, $c->merge(null)->all());
    }

    /**
     * @return void
     */
    public function testMergeArray()
    {
        [$o1, $o2, $o3, $o4, $o5] = $this->arr;
        $c = new Collection([ $o1, $o2, $o3, $o4 ]);

        $this->assertEquals($this->map, $c->merge([ $o5 ])->all());
    }

    /**
     * @return void
     */
    public function testMergeCollection()
    {
        [$o1, $o2, $o3, $o4, $o5] = $this->arr;
        $c1 = new Collection([ $o1, $o2, $o3, $o4 ]);
        $c2 = new Collection($o5);

        $this->assertEquals($this->map, $c1->merge($c2)->all());
    }

    // Test \IteratorAggregate
    // =============================================================================================

    /**
     * @return void
     */
    public function testIterable()
    {
        $c = new Collection($this->arr);

        $i = $c->getIterator();
        $this->assertInstanceOf('ArrayIterator', $i);
        $this->assertEquals($this->map, $i->getArrayCopy());
    }

    /**
     * @return void
     */
    public function testCachingIterator()
    {
        [$o1, $o2, $o3, $o4, $o5] = $this->arr;
        $c = new Collection($this->arr);

        $i = $c->getCachingIterator(CachingIterator::FULL_CACHE);
        $this->assertInstanceOf(CachingIterator::class, $i);

        $i->next();
        $i->next();
        $this->assertEquals(
            [ self::OBJ_1 => $o1, self::OBJ_2 => $o2 ],
            $i->getCache()
        );

        $i->next();
        $this->assertEquals(
            [ self::OBJ_1 => $o1, self::OBJ_2 => $o2, self::OBJ_3 => $o3 ],
            $i->getCache()
        );
    }

    // Test \Countable
    // =============================================================================================

    /**
     * @return void
     */
    public function testCountable()
    {
        $c = new Collection($this->arr);
        $this->assertCount(count($this->arr), $c);
    }

    // Test \ArrayAccess
    // =============================================================================================

    /**
     * @return void
     */
    public function testArrayAccess()
    {
        [$o1, $o2, $o3, $o4, $o5] = $this->arr;

        $c = new Collection([ $o1, $o2, $o3, $o4 ]);
        $this->assertEquals($o1, $c[self::OBJ_1]);
        $this->assertEquals($o1, $c[0]);
        $this->assertEquals($o2, $c[self::OBJ_2]);
        $this->assertEquals($o2, $c[-3]);

        $c[] = $o5;
        $this->assertEquals($o5, $c[self::OBJ_5]);
        $this->assertEquals($o5, $c[4]);
        $this->assertEquals($o5, $c[-1]);
        $this->assertTrue(isset($c[self::OBJ_5]));

        unset($c[self::OBJ_5]);
        $this->assertFalse(isset($c[self::OBJ_5]));
        $this->assertEquals($o4, $c[-1]);
    }

    /**
     * @return void
     */
    public function testArrayAccessOffsetExists()
    {
        $c = new Collection($this->arr);
        $this->assertTrue($c->offsetExists(0));
        $this->assertTrue($c->offsetExists(1));
        $this->assertFalse($c->offsetExists(5));
    }

    /**
     * @return void
     */
    public function testArrayAccessOffsetGet()
    {
        [$o1, $o2] = $this->arr;

        $c = new Collection($this->arr);
        $this->assertEquals($o1, $c->offsetGet(0));
        $this->assertEquals($o2, $c->offsetGet(1));
    }

    /**
     * @return void
     */
    public function testArrayAccessOffsetGetWithNegativeOffset()
    {
        [$o1, $o2, $o3, $o4, $o5] = $this->arr;

        $c = new Collection([ $o1, $o2, $o3 ]);
        $this->assertEquals($o1, $c->offsetGet(-3));
        $this->assertEquals($o2, $c->offsetGet(-2));
        $this->assertEquals($o3, $c->offsetGet(-1));
    }

    /**
     * @return void
     */
    public function testArrayAccessOffsetGetOnNonExist()
    {
        $c = new Collection($this->arr);
        $this->assertEquals(null, $c->offsetGet(10));
    }

    /**
     * @return void
     */
    public function testArrayAccessOffsetSet()
    {
        [$o1, $o2] = $this->arr;
        $c = new Collection($o1);

        $c->offsetSet(null, $o2);
        $this->assertEquals($o2, $c[1]);
    }

    /**
     *
     * @return void
     */
    public function testArrayAccessOffsetSetWithOffset()
    {
        $this->expectException(LogicException::class);
        [$o1] = $this->arr;
        $c = new Collection;

        $c->offsetSet(1, $o1);
    }

    /**
     *
     * @return void
     */
    public function testArrayAccessOffsetSetWithKey()
    {
        $this->expectException(LogicException::class);
        [$o1] = $this->arr;
        $c = new Collection;

        $c->offsetSet(self::OBJ_1, $o1);
    }

    /**
     * @return void
     */
    public function testArrayAccessOffsetUnset()
    {
        $c = new Collection($this->arr);

        $c->offsetUnset(1);
        $this->assertEquals(null, $c[self::OBJ_2]);
    }

    /**
     * @return void
     */
    public function testArrayAccessOffsetUnsetWithKey()
    {
        $c = new Collection($this->arr);

        $c->offsetUnset(self::OBJ_2);
        $this->assertEquals(null, $c[self::OBJ_2]);
    }
}

<?php

namespace Charcoal\Tests\Cache\Facade;

use DateInterval;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;

// From PSR-3
use Psr\Log\NullLogger;

// From 'tedivm/stash'
use Stash\Interfaces\ItemInterface;

// From 'charcoal-cache'
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Cache\CachePoolTrait;
use Charcoal\Cache\Facade\CachePoolFacade;
use Charcoal\Cache\CacheConfig;

/**
 * Test CachePoolFacade
 *
 * This class is based on {@see \Stash\Test\AbstractPoolTest}.
 *
 * @coversDefaultClass \Charcoal\Cache\Facade\CachePoolFacade
 */
class CachePoolFacadeTest extends AbstractTestCase
{
    use CachePoolTrait;

    protected $data = [
        [ 'test', 'test' ],
    ];

    protected $multiData = [
        'key'  => 'value',
        'key1' => 'value1',
        'key2' => 'value2',
        'key3' => 'value3',
    ];

    /**
     * Prepare the cache pool.
     *
     * @return void
     */
    public function setUp(): void
    {
        static::createCachePool();
    }

    /**
     * Empty the cache pool.
     *
     * @return void
     */
    public function tearDown(): void
    {
        static::clearCachePool();
    }

    /**
     * Create a new CachePoolFacade instance.
     *
     * @param  array $args Parameters for the initialization of a CachePoolFacade.
     * @return CachePoolFacade
     */
    protected function facadeFactory(array $args = [])
    {
        if (!isset($args['cache'])) {
            $args['cache'] = static::getCachePool();
        }

        return new CachePoolFacade($args);
    }

    /**
     * @covers ::__construct
     */
    public function testConstruct()
    {
        $facade = $this->facadeFactory([
            'default_ttl' => 120,
        ]);

        $this->assertInstanceOf(CachePoolFacade::class, $facade);
    }

    /**
     * @covers ::get
     * @covers ::save
     *
     * @return void
     */
    public function testGet()
    {
        $facade = $this->facadeFactory();

        $data = $facade->get('base/one');
        $this->assertNull($data, 'New cache item returns NULL.');

        $facade->set('base/one', $this->data);
        $data = $facade->get('base/one');
        $this->assertEquals($this->data, $data);

        $func = function () {
            return $this->data;
        };
        $data = $facade->get('base/two', $func);
        $this->assertEquals($this->data, $data);
    }

    /**
     * @covers ::has
     *
     * @return void
     */
    public function testHas()
    {
        $facade = $this->facadeFactory();

        $this->assertFalse($facade->has('base/one'));

        $facade->set('base/one', $this->data);
        $this->assertTrue($facade->has('base/one'));
    }

    /**
     * @covers ::set
     * @covers ::save
     *
     * @return CachePoolFacade To use the same cache pool facade for the next test.
     */
    public function testSet()
    {
        $facade = $this->facadeFactory();

        foreach ($this->multiData as $key => $data) {
            $this->assertFalse($facade->has($key));
            $this->assertTrue($facade->set($key, $data));
        }

        foreach ($this->multiData as $key => $data) {
            $this->assertEquals($this->multiData[$key], $facade->get($key));
        }

        return $facade;
    }

    /**
     * @depends testSet
     * @covers  ::delete
     *
     * @param  CachePoolFacade $facade The cache pool facade from the previous test.
     * @return void
     */
    public function testDelete(CachePoolFacade $facade)
    {
        $keys = array_keys($this->multiData);

        $this->assertTrue($facade->delete(...$keys));

        foreach ($keys as $key) {
            $this->assertFalse($facade->has($key));
        }
    }

    /**
     * Test a numeric expiration time for this cache item.
     *
     * @covers ::save
     *
     * @dataProvider provideTtlOnSave
     *
     * @param  DateTimeInterface $expected   The expected expiration time
     *     from {@see \Stash\Interfaces\ItemInterface::getExpiration()}.
     * @param  mixed             $itemTtl    The cache item's expiration time.
     * @param  DateTimeInterface $defaultTtl The facade default expiration time.
     * @return void
     */
    public function testTtlOnSave(DateTimeInterface $expected, $itemTtl, DateTimeInterface $defaultTtl)
    {
        $stash  = static::getCachePool();
        $facade = $this->facadeFactory([
            'default_ttl' => $defaultTtl,
        ]);

        $facade->set('base/one', $this->data, $itemTtl);

        $this->assertLessThanOrEqual(
            $expected->getTimestamp(),
            $stash->getItem('base/one')->getExpiration()->getTimestamp()
        );
    }

    /**
     * Provide data for testing the expiration time per cache item.
     *
     * @used-by self::testTtlOnSave()
     * @return  array
     */
    public function provideTtlOnSave()
    {
        $data = [];
        $date = new DateTimeImmutable('now');

        $default = $date->add(new DateInterval('P50Y'));

        $interval   = new DateInterval('P1D');
        $expiration = $date->add($interval);
        $data['DateInterval'] = [ $expiration, $interval, $default ];

        $datetime   = new DateTimeImmutable('tomorrow');
        $data['DateTime'] = [ $expiration, $datetime, $default ];

        $integer    = 120;
        $interval   = DateInterval::createFromDateString('120 seconds');
        $expiration = $date->add($interval);
        $data['integer'] = [ $expiration, $integer, $default ];

        // $float      = '59.5';
        // $interval   = DateInterval::createFromDateString('59 seconds');
        // $expiration = $date->add($interval);
        // $data['float'] = [ $expiration, $float, $default ];

        $data['boolean'] = [ $default, false, $default ];
        $data['null']    = [ $default, null, $default ];

        return $data;
    }

    /**
     * @covers ::defaultTtl
     * @covers ::setDefaultTtl
     *
     * @return void
     */
    public function testSetDefaultTtl()
    {
        $time = new \DateInterval('P1D');
        $facade = $this->facadeFactory([
            'default_ttl' => $time,
        ]);
        $this->assertSame($time, $facade->defaultTtl());

        $time = new DateTime('tomorrow');
        $facade->setDefaultTtl($time);
        $this->assertEquals($time, $facade->defaultTtl());

        $time = 120;
        $facade->setDefaultTtl($time);
        $this->assertEquals($time, $facade->defaultTtl());

        $time = '59.5';
        $facade->setDefaultTtl($time);
        $this->assertEquals($time, $facade->defaultTtl());

        $val = false;
        $facade->setDefaultTtl($val);
        $this->assertEquals($val, $facade->defaultTtl());

        $val = null;
        $facade->setDefaultTtl($val);
        $this->assertEquals($val, $facade->defaultTtl());
    }
}

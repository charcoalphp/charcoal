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
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Charcoal\Cache\Facade\CachePoolFacade::class)]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Cache\Facade\CachePoolFacade::class, '__construct')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Cache\Facade\CachePoolFacade::class, 'get')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Cache\Facade\CachePoolFacade::class, 'save')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Cache\Facade\CachePoolFacade::class, 'has')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Cache\Facade\CachePoolFacade::class, 'set')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Cache\Facade\CachePoolFacade::class, 'delete')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Cache\Facade\CachePoolFacade::class, 'defaultTtl')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Cache\Facade\CachePoolFacade::class, 'setDefaultTtl')]
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
     */
    public function setUp(): void
    {
        static::createCachePool();
    }

    /**
     * Empty the cache pool.
     */
    public function tearDown(): void
    {
        static::clearCachePool();
    }

    /**
     * Create a new CachePoolFacade instance.
     *
     * @param  array $args Parameters for the initialization of a CachePoolFacade.
     */
    protected function facadeFactory(array $args = []): \Charcoal\Cache\Facade\CachePoolFacade
    {
        if (!isset($args['cache'])) {
            $args['cache'] = static::getCachePool();
        }

        return new CachePoolFacade($args);
    }

    public function testConstruct(): void
    {
        $facade = $this->facadeFactory([
            'default_ttl' => 120,
        ]);

        $this->assertInstanceOf(CachePoolFacade::class, $facade);
    }

    public function testGet(): void
    {
        $facade = $this->facadeFactory();

        $data = $facade->get('base/one');
        $this->assertNull($data, 'New cache item returns NULL.');

        $facade->set('base/one', $this->data);
        $data = $facade->get('base/one');
        $this->assertEquals($this->data, $data);

        $func = (fn() => $this->data);
        $data = $facade->get('base/two', $func);
        $this->assertEquals($this->data, $data);
    }

    public function testHas(): void
    {
        $facade = $this->facadeFactory();

        $this->assertFalse($facade->has('base/one'));

        $facade->set('base/one', $this->data);
        $this->assertTrue($facade->has('base/one'));
    }

    /**
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
     * @param  CachePoolFacade $facade The cache pool facade from the previous test.
     */
    #[\PHPUnit\Framework\Attributes\Depends('testSet')]
    public function testDelete(CachePoolFacade $facade): void
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
     *
     *
     * @param  DateTimeInterface $expected   The expected expiration time
     *     from {@see \Stash\Interfaces\ItemInterface::getExpiration()}.
     * @param  mixed             $itemTtl    The cache item's expiration time.
     * @param  DateTimeInterface $defaultTtl The facade default expiration time.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideTtlOnSave')]
    public function testTtlOnSave(DateTimeInterface $expected, $itemTtl, DateTimeInterface $defaultTtl): void
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
     */
    public static function provideTtlOnSave(): array
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

    public function testSetDefaultTtl(): void
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

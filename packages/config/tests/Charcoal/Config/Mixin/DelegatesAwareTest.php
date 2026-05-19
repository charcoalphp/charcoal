<?php

namespace Charcoal\Tests\Config\Mixin;

// From 'charcoal-config'
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Config\Mock\DelegateEntity;
use Charcoal\Tests\Config\Mock\Entity;
use Charcoal\Config\DelegatesAwareInterface;
use Charcoal\Config\DelegatesAwareTrait;

/**
 * Test DelegatesAwareTrait
 */
#[\PHPUnit\Framework\Attributes\CoversTrait(\Charcoal\Config\DelegatesAwareTrait::class)]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\DelegatesAwareTrait::class, 'setDelegates()')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\DelegatesAwareTrait::class, 'addDelegate()')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\DelegatesAwareTrait::class, 'prependDelegate()')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\DelegatesAwareTrait::class, 'hasInDelegates()')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\DelegatesAwareTrait::class, 'getInDelegates()')]
class DelegatesAwareTest extends AbstractTestCase
{
    /**
     * @var DelegateEntity
     */
    public $obj;

    /**
     * @var ArrayObject[]
     */
    public $delegates;

    /**
     * Create a DelegateEntity instance.
     */
    protected function setUp(): void
    {
        // phpcs:disable Squiz.Objects.ObjectInstantiation.NotAssigned
        $this->delegates = [
            new Entity([
                'uid'     => 'd7d5',
                'name'    => null,
            ]),
            new Entity([
                'uid'     => '2c0f',
                'level'   => null,
                'bubble'  => true,
            ]),
            new Entity([
                'uid'     => 'a215',
                'name'    => 'Charcoal',
                'level'   => 'warning',
            ]),
            new Entity([
                'uid'     => '4af9',
                'level'   => 'error',
                'logfile' => 'logs/charcoal.log',
            ]),
            new DelegateEntity([
                'uid'        => '12e8',
                'permission' => '644',
            ]),
        ];
        // phpcs:enable

        $this->obj = $this->createObject([
            'uid'     => '47df',
            'name'    => 'MyApp',
            'logfile' => null,
        ]);
    }

    /**
     * Create a DelegateEntity instance.
     *
     * @param  array $data Data to pre-populate the object.
     */
    public function createObject(?array $data = null): \Charcoal\Tests\Config\Mock\DelegateEntity
    {
        return new DelegateEntity($data);
    }

    /**
     * Asserts that the object implements DelegatesAwareInterface.
     */
    #[\PHPUnit\Framework\Attributes\CoversNothing]
    public function testDelegatesAwareInterface(): void
    {
        $this->assertInstanceOf(DelegatesAwareInterface::class, $this->obj);
    }



    // Test Delegate Collecting
    // =========================================================================
    /**
     * Asserts that the separator is disabled by default.
     */
    #[\PHPUnit\Framework\Attributes\CoversNothing]
    public function testDefaultDelegatesCollection(): void
    {
        $this->assertEmpty($this->obj->delegates());
    }

    public function testSetDelegates(): void
    {
        $obj = $this->obj;

        $that = $obj->setDelegates([ $this->delegates[0], $this->delegates[1] ]);
        $this->assertSame($obj, $that);

        $that = $obj->addDelegate($this->delegates[2]);
        $this->assertSame($obj, $that);

        $that = $obj->prependDelegate($this->delegates[3]);
        $this->assertSame($obj, $that);

        $this->assertEquals([
            0 => $this->delegates[3],
            1 => $this->delegates[0],
            2 => $this->delegates[1],
            3 => $this->delegates[2],
        ], $obj->delegates());
    }

    /**
     * @return DelegateEntity
     */
    #[\PHPUnit\Framework\Attributes\CoversNothing]
    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function testSetNestedDelegates()
    {
        $this->delegates[4]->addDelegate($this->delegates[2]);
        $this->obj->setDelegates([
            $this->delegates[1],
            $this->delegates[4],
            $this->delegates[3],
        ]);

        return $this->obj;
    }




    // Test HasInDelegates
    // =========================================================================
    /**
     *
     * @see    self::$delegates[1]['bubble']
     * @param  DelegatesAwareInterface $obj The DelegatesAwareInterface implementation to test.
     */
    #[\PHPUnit\Framework\Attributes\Depends('testSetNestedDelegates')]
    public function testHasInDelegatesReturnsTrueOnDelegatedKey(DelegatesAwareInterface $obj): void
    {
        $this->assertTrue($obj->hasInDelegates('bubble'));
    }

    /**
     * @param  DelegatesAwareInterface $obj The DelegatesAwareInterface implementation to test.
     */
    #[\PHPUnit\Framework\Attributes\Depends('testSetNestedDelegates')]
    public function testHasInDelegatesReturnsFalseOnNonexistentKey(DelegatesAwareInterface $obj): void
    {
        $this->assertFalse($obj->hasInDelegates('use_error_handler'));
    }




    // Test GetInDelegates
    // =========================================================================
    /**
     *
     * @see    self::$delegates[2]['level']
     * @param  DelegatesAwareInterface $obj The DelegatesAwareInterface implementation to test.
     */
    #[\PHPUnit\Framework\Attributes\Depends('testSetNestedDelegates')]
    public function testGetInDelegatesReturnsValueOnDelegatedKey(DelegatesAwareInterface $obj): void
    {
        $this->assertEquals(
            $this->delegates[2]['level'],
            $obj->getInDelegates('level')
        );
    }

    /**
     * @param  DelegatesAwareInterface $obj The DelegatesAwareInterface implementation to test.
     */
    #[\PHPUnit\Framework\Attributes\Depends('testSetNestedDelegates')]
    public function testGetInDelegatesReturnsNullOnNonexistentKey(DelegatesAwareInterface $obj): void
    {
        $this->assertNull($obj->getInDelegates('use_error_handler'));
    }
}

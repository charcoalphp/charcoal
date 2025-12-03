<?php

namespace Charcoal\Tests\Config\Mixin;

// From 'charcoal-config'
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\AssertionsTrait;
use Charcoal\Tests\Config\Mock\TreeEntity;
use Charcoal\Config\SeparatorAwareInterface;
use Charcoal\Config\SeparatorAwareTrait;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversMethod;
use ValueError;

#[CoversMethod(SeparatorAwareTrait::class, 'separator')]
#[CoversMethod(SeparatorAwareTrait::class, 'setSeparator')]
#[CoversMethod(SeparatorAwareTrait::class, 'hasWithSeparator')]
#[CoversMethod(SeparatorAwareTrait::class, 'getWithSeparator')]
#[CoversMethod(SeparatorAwareTrait::class, 'setWithSeparator')]
class SeparatorAwareTest extends AbstractTestCase
{
    use AssertionsTrait;

    /**
     * @var TreeEntity
     */
    public $obj;

    /**
     * @var array
     */
    public $connections;

    /**
     * Create a TreeEntity instance.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->connections = [
            'default' => [
                'driver'      => 'pdo_mysql',
                'host'        => 'web.someplace.tld',
                'dbname'      => 'mydatabase',
                'user'        => 'myusername',
                'password'    => 'mypassword',
                'charset'     => 'utf8mb4',
                'unix_socket' => '/tmp/mysql.sock',
            ],
            'customer' => [
                'driver'      => 'pdo_mysql',
                'host'        => 'customer.someplace.tld',
                'dbname'      => 'mydatabase',
                'user'        => 'myusername',
                'password'    => 'mypassword',
                'charset'     => 'utf8mb4',
                'unix_socket' => null,
            ],
        ];

        $this->obj = $this->createObject([
            'connections' => $this->connections
        ]);
    }

    /**
     * Create a TreeEntity instance.
     *
     * @param  array $data Data to pre-populate the object.
     * @return TreeEntity
     */
    public function createObject(?array $data = null)
    {
        return new TreeEntity($data);
    }

    /**
     * Asserts that the object implements SeparatorAwareInterface.
     *
     * @coversNothing
     * @return void
     */
    public function testSeparatorAwareInterface()
    {
        $this->assertInstanceOf(SeparatorAwareInterface::class, $this->obj);
    }



    // Test Seperator Token
    // =========================================================================

    /**
     * Asserts that the separator is disabled by default.
     *
     * @return void
     */
    public function testDefaultSeparatorIsEmptyString()
    {
        $this->assertEmpty($this->obj->separator());
    }

    public function testSetSeparator()
    {
        $obj  = $this->obj;
        $that = $obj->setSeparator('.');

        $this->assertSame($obj, $that);
        $this->assertEquals('.', $obj->separator());

        return $obj;
    }

    public function testMutatedSeparator()
    {
        $obj = $this->obj;

        $obj->setSeparator('/');
        $this->assertEquals(
            $this->connections['default']['host'],
            $obj['connections/default/host']
        );
    }

    public function testEmptySeparator()
    {
        $obj = $this->obj;

        $obj->setSeparator('');
        $this->assertEquals('', $obj->separator());
    }

    public function testSetSeparatorWithInvalidType()
    {
        $this->expectExceptionMessage('Separator must be a string');
        $this->expectException(InvalidArgumentException::class);

        $this->obj->setSeparator(1);
    }

    public function testSetSeparatorWithInvalidToken()
    {
        $this->expectExceptionMessage('Separator must be one-character, or empty');
        $this->expectException(InvalidArgumentException::class);

        $this->obj->setSeparator('::');
    }



    // Test HasWithSeparator
    // =========================================================================

    /**
     * @depends testSetSeparator
     *
     * @param  SeparatorAwareInterface $obj The SeparatorAwareInterface implementation to test.
     * @return void
     */
    public function testObjReturnsTrueOnHasEndKeyPath(SeparatorAwareInterface $obj)
    {
        $this->assertTrue($obj->hasWithSeparator('connections.default.driver'));
    }

    /**
     * @depends testSetSeparator
     *
     * @param  SeparatorAwareInterface $obj The SeparatorAwareInterface implementation to test.
     * @return void
     */
    public function testObjReturnsTrueOnHasMidKeyPath(SeparatorAwareInterface $obj)
    {
        $this->assertTrue($obj->hasWithSeparator('connections.default'));
    }

    /**
     * @depends testSetSeparator
     *
     * @param  SeparatorAwareInterface $obj The SeparatorAwareInterface implementation to test.
     * @return void
     */
    public function testObjReturnsTrueOnHasBaseKeyPath(SeparatorAwareInterface $obj)
    {
        $this->assertTrue($obj->hasWithSeparator('connections'));
    }

    /**
     * @depends testSetSeparator
     *
     * @param  SeparatorAwareInterface $obj The SeparatorAwareInterface implementation to test.
     * @return void
     */
    public function testObjReturnsFalseOnHasEndKeyPathToNullValue(SeparatorAwareInterface $obj)
    {
        $this->assertFalse($obj->hasWithSeparator('connections.customer.unix_socket'));
    }

    /**
     * @depends testSetSeparator
     *
     * @param  SeparatorAwareInterface $obj The SeparatorAwareInterface implementation to test.
     * @return void
     */
    public function testObjReturnsFalseOnHasNonexistentEndKeyPath(SeparatorAwareInterface $obj)
    {
        $this->assertFalse($obj->hasWithSeparator('connections.default.server_version'));
    }

    /**
     * @depends testSetSeparator
     *
     * @param  SeparatorAwareInterface $obj The SeparatorAwareInterface implementation to test.
     * @return void
     */
    public function testObjReturnsFalseOnHasNonexistentMidKeyPath(SeparatorAwareInterface $obj)
    {
        $this->assertFalse($obj->hasWithSeparator('connections.analytics.host'));
    }

    /**
     * @depends testSetSeparator
     *
     * @param  SeparatorAwareInterface $obj The SeparatorAwareInterface implementation to test.
     * @return void
     */
    public function testObjReturnsFalseOnHasNonexistentBaseKeyPath(SeparatorAwareInterface $obj)
    {
        $this->assertFalse($obj->hasWithSeparator('logging'));
    }

    /**
     * @used-by self::testHasWithSeparatorWithoutDelimiterInPhp7()
     * @used-by self::testHasWithSeparatorWithoutDelimiterInPhp5()
     *
     * @return void
     */
    public function delegatedTestHasWithSeparatorWithoutDelimiter()
    {
        $this->obj->hasWithSeparator('connections.default.host');
    }

    /**
     * @requires PHP >= 7.0
     * @return   void
     */
    public function testHasWithSeparatorWithoutDelimiterInPhp7()
    {
        $this->expectException(ValueError::class);
        $this->delegatedTestHasWithSeparatorWithoutDelimiter();
    }



    // Test GetWithSeparator
    // =========================================================================

    /**
     * @depends testSetSeparator
     *
     * @param  SeparatorAwareInterface $obj The SeparatorAwareInterface implementation to test.
     * @return void
     */
    public function testObjReturnsValueOnGetEndKeyPath(SeparatorAwareInterface $obj)
    {
        $this->assertEquals(
            $this->connections['default']['driver'],
            $obj->getWithSeparator('connections.default.driver')
        );
    }

    /**
     * @depends testSetSeparator
     *
     * @param  SeparatorAwareInterface $obj The SeparatorAwareInterface implementation to test.
     * @return void
     */
    public function testObjReturnsValueOnGetMidKeyPath(SeparatorAwareInterface $obj)
    {
        $this->assertEquals(
            $this->connections['default'],
            $obj->getWithSeparator('connections.default')
        );
    }

    /**
     * @depends testSetSeparator
     *
     * @param  SeparatorAwareInterface $obj The SeparatorAwareInterface implementation to test.
     * @return void
     */
    public function testObjReturnsValueOnGetBaseKeyPath(SeparatorAwareInterface $obj)
    {
        $this->assertEquals(
            $this->connections,
            $obj->getWithSeparator('connections')
        );
    }

    /**
     * @depends testSetSeparator
     *
     * @param  SeparatorAwareInterface $obj The SeparatorAwareInterface implementation to test.
     * @return void
     */
    public function testObjReturnsNullOnGetEndKeyPathToNullValue(SeparatorAwareInterface $obj)
    {
        $this->assertNull($obj->getWithSeparator('connections.customer.unix_socket'));
    }

    /**
     * @depends testSetSeparator
     *
     * @param  SeparatorAwareInterface $obj The SeparatorAwareInterface implementation to test.
     * @return void
     */
    public function testObjReturnsNullOnGetNonexistentEndKeyPath(SeparatorAwareInterface $obj)
    {
        $this->assertNull($obj->getWithSeparator('connections.default.server_version'));
    }

    /**
     * @depends testSetSeparator
     *
     * @param  SeparatorAwareInterface $obj The SeparatorAwareInterface implementation to test.
     * @return void
     */
    public function testObjReturnsNullOnGetNonexistentMidKeyPath(SeparatorAwareInterface $obj)
    {
        $this->assertNull($obj->getWithSeparator('connections.analytics.host'));
    }

    /**
     * @depends testSetSeparator
     *
     * @param  SeparatorAwareInterface $obj The SeparatorAwareInterface implementation to test.
     * @return void
     */
    public function testObjReturnsNullOnGetNonexistentBaseKeyPath(SeparatorAwareInterface $obj)
    {
        $this->assertNull($obj->getWithSeparator('logging'));
    }

    /**
     * @used-by self::testGetWithSeparatorWithoutDelimiterInPhp7()
     * @used-by self::testGetWithSeparatorWithoutDelimiterInPhp5()
     *
     * @return void
     */
    public function delegatedTestGetWithSeparatorWithoutDelimiter()
    {
        $this->obj->getWithSeparator('connections.default.host');
    }

    /**
     * @requires PHP >= 7.0
     * @return   void
     */
    public function testGetWithSeparatorWithoutDelimiterInPhp7()
    {
        $this->expectException(ValueError::class);

        $this->delegatedTestGetWithSeparatorWithoutDelimiter();
    }


    // Test SetWithSeparator
    // =========================================================================

    /**
     * @depends testSetSeparator
     *
     * @param  SeparatorAwareInterface $obj The SeparatorAwareInterface implementation to test.
     * @return void
     */
    public function testObjReplacesValueRecursivelyOnSetKeyPath(SeparatorAwareInterface $obj)
    {
        $obj->setWithSeparator('keywords', [ 'php', 'framework', 'charcoal', 'config' ]);
        $obj->setWithSeparator('keywords', [ 1 => 'library', 4 => 'component' ]);
        $this->assertEquals(
            [ 'php', 'library', 'charcoal', 'config', 'component' ],
            $obj->get('keywords')
        );

        $obj->setWithSeparator('keywords.4', 'package');
        $this->assertEquals(
            [ 'php', 'library', 'charcoal', 'config', 'package' ],
            $obj->get('keywords')
        );
    }

    /**
     * @depends testSetSeparator
     *
     * @param  SeparatorAwareInterface $obj The SeparatorAwareInterface implementation to test.
     * @return void
     */
    public function testObjReplacesValueOnSetEndKeyPath(SeparatorAwareInterface $obj)
    {
        $obj->setWithSeparator('connections.default.driver', 'pdo_sqlite');
        $this->assertEquals('pdo_sqlite', $obj->get('connections.default.driver'));
    }

    /**
     * @depends testSetSeparator
     *
     * @param  SeparatorAwareInterface $obj The SeparatorAwareInterface implementation to test.
     * @return void
     */
    public function testObjReplacesValueOnSetMidKeyPath(SeparatorAwareInterface $obj)
    {
        $obj->setWithSeparator('connections.default', [ 'dbname' => 'otherdatabase' ]);
        $this->assertEquals('otherdatabase', $obj->get('connections.default.dbname'));
    }

    /**
     * @depends testSetSeparator
     *
     * @param  SeparatorAwareInterface $obj The SeparatorAwareInterface implementation to test.
     * @return void
     */
    public function testObjReplacesValueOnSetBaseKeyPath(SeparatorAwareInterface $obj)
    {
        $obj->setWithSeparator('connections', [ 'default' => [ 'host' => 'web.otherplace.tld' ] ]);
        $this->assertEquals('web.otherplace.tld', $obj->get('connections.default.host'));
    }

    /**
     * @depends testSetSeparator
     *
     * @param  SeparatorAwareInterface $obj The SeparatorAwareInterface implementation to test.
     * @return void
     */
    public function testObjAddsValueOnSetNonexistentEndKeyPath(SeparatorAwareInterface $obj)
    {
        $obj->setWithSeparator('connections.default.server_version', '5.7');
        $this->assertEquals('5.7', $obj->get('connections.default.server_version'));
    }

    /**
     * @depends testSetSeparator
     *
     * @param  SeparatorAwareInterface $obj The SeparatorAwareInterface implementation to test.
     * @return void
     */
    public function testObjAddsValueOnSetNonexistentMidKeyPath(SeparatorAwareInterface $obj)
    {
        $obj->setWithSeparator('connections.analytics', [ 'driver' => 'pdo_pgsql' ]);
        $this->assertEquals('pdo_pgsql', $obj->get('connections.analytics.driver'));
    }

    /**
     * @depends testSetSeparator
     *
     * @param  SeparatorAwareInterface $obj The SeparatorAwareInterface implementation to test.
     * @return void
     */
    public function testObjAddsValueOnSetNonexistentBaseKeyPath(SeparatorAwareInterface $obj)
    {
        $obj->setWithSeparator('logging', [ 'level' => 'debug' ]);
        $this->assertTrue($obj->has('logging.level'));
        $this->assertArraySubset(
            [ 'level' => 'debug' ],
            $obj->get('logging')
        );
    }

    /**
     * @used-by self::testSetWithSeparatorWithoutDelimiterInPhp7()
     * @used-by self::testSetWithSeparatorWithoutDelimiterInPhp5()
     *
     * @return void
     */
    public function delegatedTestSetWithSeparatorWithoutDelimiter()
    {
        $this->obj->setWithSeparator('connections.default.server_version', '5.7');
    }

    /**
     * @requires PHP >= 7.0
     * @return   void
     */
    public function testSetWithSeparatorWithoutDelimiterInPhp7()
    {
        $this->expectException(ValueError::class);

        $this->delegatedTestSetWithSeparatorWithoutDelimiter();
    }
}

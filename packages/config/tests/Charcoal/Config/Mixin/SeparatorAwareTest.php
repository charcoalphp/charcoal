<?php

namespace Charcoal\Tests\Config\Mixin;

// From 'charcoal-config'
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\AssertionsTrait;
use Charcoal\Tests\Config\Mock\TreeEntity;
use Charcoal\Config\SeparatorAwareInterface;
use Charcoal\Config\SeparatorAwareTrait;
use InvalidArgumentException;

/**
 * Test SeparatorAwareTrait
 */
#[\PHPUnit\Framework\Attributes\CoversTrait(\Charcoal\Config\SeparatorAwareTrait::class)]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\SeparatorAwareTrait::class, 'separator()')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\SeparatorAwareTrait::class, 'setSeparator()')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\SeparatorAwareTrait::class, 'hasWithSeparator()')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\SeparatorAwareTrait::class, 'getWithSeparator()')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\SeparatorAwareTrait::class, 'setWithSeparator()')]
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
     */
    public function createObject(?array $data = null): \Charcoal\Tests\Config\Mock\TreeEntity
    {
        return new TreeEntity($data);
    }

    /**
     * Asserts that the object implements SeparatorAwareInterface.
     */
    #[\PHPUnit\Framework\Attributes\CoversNothing]
    public function testSeparatorAwareInterface(): void
    {
        $this->assertInstanceOf(SeparatorAwareInterface::class, $this->obj);
    }



    // Test Seperator Token
    // =========================================================================
    /**
     * Asserts that the separator is disabled by default.
     */
    public function testDefaultSeparatorIsEmptyString(): void
    {
        $this->assertEmpty($this->obj->separator());
    }

    /**
     * @return TreeEntity
     */
    public function testSetSeparator()
    {
        $obj  = $this->obj;
        $that = $obj->setSeparator('.');

        $this->assertSame($obj, $that);
        $this->assertEquals('.', $obj->separator());

        return $obj;
    }

    public function testMutatedSeparator(): void
    {
        $obj = $this->obj;

        $obj->setSeparator('/');
        $this->assertEquals(
            $this->connections['default']['host'],
            $obj['connections/default/host']
        );
    }

    public function testEmptySeparator(): void
    {
        $obj = $this->obj;

        $obj->setSeparator('');
        $this->assertEquals('', $obj->separator());
    }

    public function testSetSeparatorWithInvalidType(): void
    {
        $this->expectExceptionMessage('Separator must be a string');
        $this->expectException(InvalidArgumentException::class);

        $this->obj->setSeparator(1);
    }

    public function testSetSeparatorWithInvalidToken(): void
    {
        $this->expectExceptionMessage('Separator must be one-character, or empty');
        $this->expectException(InvalidArgumentException::class);

        $this->obj->setSeparator('::');
    }



    // Test HasWithSeparator
    // =========================================================================
    /**
     * @param  SeparatorAwareInterface $obj The SeparatorAwareInterface implementation to test.
     */
    #[\PHPUnit\Framework\Attributes\Depends('testSetSeparator')]
    public function testObjReturnsTrueOnHasEndKeyPath(SeparatorAwareInterface $obj): void
    {
        $this->assertTrue($obj->hasWithSeparator('connections.default.driver'));
    }

    /**
     * @param  SeparatorAwareInterface $obj The SeparatorAwareInterface implementation to test.
     */
    #[\PHPUnit\Framework\Attributes\Depends('testSetSeparator')]
    public function testObjReturnsTrueOnHasMidKeyPath(SeparatorAwareInterface $obj): void
    {
        $this->assertTrue($obj->hasWithSeparator('connections.default'));
    }

    /**
     * @param  SeparatorAwareInterface $obj The SeparatorAwareInterface implementation to test.
     */
    #[\PHPUnit\Framework\Attributes\Depends('testSetSeparator')]
    public function testObjReturnsTrueOnHasBaseKeyPath(SeparatorAwareInterface $obj): void
    {
        $this->assertTrue($obj->hasWithSeparator('connections'));
    }

    /**
     * @param  SeparatorAwareInterface $obj The SeparatorAwareInterface implementation to test.
     */
    #[\PHPUnit\Framework\Attributes\Depends('testSetSeparator')]
    public function testObjReturnsFalseOnHasEndKeyPathToNullValue(SeparatorAwareInterface $obj): void
    {
        $this->assertFalse($obj->hasWithSeparator('connections.customer.unix_socket'));
    }

    /**
     * @param  SeparatorAwareInterface $obj The SeparatorAwareInterface implementation to test.
     */
    #[\PHPUnit\Framework\Attributes\Depends('testSetSeparator')]
    public function testObjReturnsFalseOnHasNonexistentEndKeyPath(SeparatorAwareInterface $obj): void
    {
        $this->assertFalse($obj->hasWithSeparator('connections.default.server_version'));
    }

    /**
     * @param  SeparatorAwareInterface $obj The SeparatorAwareInterface implementation to test.
     */
    #[\PHPUnit\Framework\Attributes\Depends('testSetSeparator')]
    public function testObjReturnsFalseOnHasNonexistentMidKeyPath(SeparatorAwareInterface $obj): void
    {
        $this->assertFalse($obj->hasWithSeparator('connections.analytics.host'));
    }

    /**
     * @param  SeparatorAwareInterface $obj The SeparatorAwareInterface implementation to test.
     */
    #[\PHPUnit\Framework\Attributes\Depends('testSetSeparator')]
    public function testObjReturnsFalseOnHasNonexistentBaseKeyPath(SeparatorAwareInterface $obj): void
    {
        $this->assertFalse($obj->hasWithSeparator('logging'));
    }

    /**
     * @used-by self::testHasWithSeparatorWithoutDelimiterInPhp7()
     * @used-by self::testHasWithSeparatorWithoutDelimiterInPhp5()
     */
    public function delegatedTestHasWithSeparatorWithoutDelimiter(): void
    {
        $this->obj->hasWithSeparator('connections.default.host');
    }

    #[\PHPUnit\Framework\Attributes\RequiresPhp('>= 7.0')]
    public function testHasWithSeparatorWithoutDelimiterInPhp7(): void
    {
        $this->expectError();

        $this->delegatedTestHasWithSeparatorWithoutDelimiter();
    }



    // Test GetWithSeparator
    // =========================================================================
    /**
     * @param  SeparatorAwareInterface $obj The SeparatorAwareInterface implementation to test.
     */
    #[\PHPUnit\Framework\Attributes\Depends('testSetSeparator')]
    public function testObjReturnsValueOnGetEndKeyPath(SeparatorAwareInterface $obj): void
    {
        $this->assertEquals(
            $this->connections['default']['driver'],
            $obj->getWithSeparator('connections.default.driver')
        );
    }

    /**
     * @param  SeparatorAwareInterface $obj The SeparatorAwareInterface implementation to test.
     */
    #[\PHPUnit\Framework\Attributes\Depends('testSetSeparator')]
    public function testObjReturnsValueOnGetMidKeyPath(SeparatorAwareInterface $obj): void
    {
        $this->assertEquals(
            $this->connections['default'],
            $obj->getWithSeparator('connections.default')
        );
    }

    /**
     * @param  SeparatorAwareInterface $obj The SeparatorAwareInterface implementation to test.
     */
    #[\PHPUnit\Framework\Attributes\Depends('testSetSeparator')]
    public function testObjReturnsValueOnGetBaseKeyPath(SeparatorAwareInterface $obj): void
    {
        $this->assertEquals(
            $this->connections,
            $obj->getWithSeparator('connections')
        );
    }

    /**
     * @param  SeparatorAwareInterface $obj The SeparatorAwareInterface implementation to test.
     */
    #[\PHPUnit\Framework\Attributes\Depends('testSetSeparator')]
    public function testObjReturnsNullOnGetEndKeyPathToNullValue(SeparatorAwareInterface $obj): void
    {
        $this->assertNull($obj->getWithSeparator('connections.customer.unix_socket'));
    }

    /**
     * @param  SeparatorAwareInterface $obj The SeparatorAwareInterface implementation to test.
     */
    #[\PHPUnit\Framework\Attributes\Depends('testSetSeparator')]
    public function testObjReturnsNullOnGetNonexistentEndKeyPath(SeparatorAwareInterface $obj): void
    {
        $this->assertNull($obj->getWithSeparator('connections.default.server_version'));
    }

    /**
     * @param  SeparatorAwareInterface $obj The SeparatorAwareInterface implementation to test.
     */
    #[\PHPUnit\Framework\Attributes\Depends('testSetSeparator')]
    public function testObjReturnsNullOnGetNonexistentMidKeyPath(SeparatorAwareInterface $obj): void
    {
        $this->assertNull($obj->getWithSeparator('connections.analytics.host'));
    }

    /**
     * @param  SeparatorAwareInterface $obj The SeparatorAwareInterface implementation to test.
     */
    #[\PHPUnit\Framework\Attributes\Depends('testSetSeparator')]
    public function testObjReturnsNullOnGetNonexistentBaseKeyPath(SeparatorAwareInterface $obj): void
    {
        $this->assertNull($obj->getWithSeparator('logging'));
    }

    /**
     * @used-by self::testGetWithSeparatorWithoutDelimiterInPhp7()
     * @used-by self::testGetWithSeparatorWithoutDelimiterInPhp5()
     */
    public function delegatedTestGetWithSeparatorWithoutDelimiter(): void
    {
        $this->obj->getWithSeparator('connections.default.host');
    }

    #[\PHPUnit\Framework\Attributes\RequiresPhp('>= 7.0')]
    public function testGetWithSeparatorWithoutDelimiterInPhp7(): void
    {
        $this->expectError();

        $this->delegatedTestGetWithSeparatorWithoutDelimiter();
    }


    // Test SetWithSeparator
    // =========================================================================
    /**
     * @param  SeparatorAwareInterface $obj The SeparatorAwareInterface implementation to test.
     */
    #[\PHPUnit\Framework\Attributes\Depends('testSetSeparator')]
    public function testObjReplacesValueRecursivelyOnSetKeyPath(SeparatorAwareInterface $obj): void
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
     * @param  SeparatorAwareInterface $obj The SeparatorAwareInterface implementation to test.
     */
    #[\PHPUnit\Framework\Attributes\Depends('testSetSeparator')]
    public function testObjReplacesValueOnSetEndKeyPath(SeparatorAwareInterface $obj): void
    {
        $obj->setWithSeparator('connections.default.driver', 'pdo_sqlite');
        $this->assertEquals('pdo_sqlite', $obj->get('connections.default.driver'));
    }

    /**
     * @param  SeparatorAwareInterface $obj The SeparatorAwareInterface implementation to test.
     */
    #[\PHPUnit\Framework\Attributes\Depends('testSetSeparator')]
    public function testObjReplacesValueOnSetMidKeyPath(SeparatorAwareInterface $obj): void
    {
        $obj->setWithSeparator('connections.default', [ 'dbname' => 'otherdatabase' ]);
        $this->assertEquals('otherdatabase', $obj->get('connections.default.dbname'));
    }

    /**
     * @param  SeparatorAwareInterface $obj The SeparatorAwareInterface implementation to test.
     */
    #[\PHPUnit\Framework\Attributes\Depends('testSetSeparator')]
    public function testObjReplacesValueOnSetBaseKeyPath(SeparatorAwareInterface $obj): void
    {
        $obj->setWithSeparator('connections', [ 'default' => [ 'host' => 'web.otherplace.tld' ] ]);
        $this->assertEquals('web.otherplace.tld', $obj->get('connections.default.host'));
    }

    /**
     * @param  SeparatorAwareInterface $obj The SeparatorAwareInterface implementation to test.
     */
    #[\PHPUnit\Framework\Attributes\Depends('testSetSeparator')]
    public function testObjAddsValueOnSetNonexistentEndKeyPath(SeparatorAwareInterface $obj): void
    {
        $obj->setWithSeparator('connections.default.server_version', '5.7');
        $this->assertEquals('5.7', $obj->get('connections.default.server_version'));
    }

    /**
     * @param  SeparatorAwareInterface $obj The SeparatorAwareInterface implementation to test.
     */
    #[\PHPUnit\Framework\Attributes\Depends('testSetSeparator')]
    public function testObjAddsValueOnSetNonexistentMidKeyPath(SeparatorAwareInterface $obj): void
    {
        $obj->setWithSeparator('connections.analytics', [ 'driver' => 'pdo_pgsql' ]);
        $this->assertEquals('pdo_pgsql', $obj->get('connections.analytics.driver'));
    }

    /**
     * @param  SeparatorAwareInterface $obj The SeparatorAwareInterface implementation to test.
     */
    #[\PHPUnit\Framework\Attributes\Depends('testSetSeparator')]
    public function testObjAddsValueOnSetNonexistentBaseKeyPath(SeparatorAwareInterface $obj): void
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
     */
    public function delegatedTestSetWithSeparatorWithoutDelimiter(): void
    {
        $this->obj->setWithSeparator('connections.default.server_version', '5.7');
    }

    #[\PHPUnit\Framework\Attributes\RequiresPhp('>= 7.0')]
    public function testSetWithSeparatorWithoutDelimiterInPhp7(): void
    {
        $this->expectError();

        $this->delegatedTestSetWithSeparatorWithoutDelimiter();
    }
}

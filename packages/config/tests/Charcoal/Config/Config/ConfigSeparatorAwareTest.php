<?php

namespace Charcoal\Tests\Config\Config;

// From 'charcoal-config'
use Charcoal\Tests\AssertionsTrait;
use Charcoal\Tests\Config\Config\AbstractConfigTestCase;
use Charcoal\Tests\Config\Mock\MacroConfig;
use Charcoal\Config\AbstractConfig;
use Charcoal\Config\SeparatorAwareInterface;

/**
 * Test SeparatorAwareTrait implementation in AbstractConfig
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Charcoal\Config\AbstractConfig::class)]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\AbstractConfig::class, '__construct()')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\AbstractConfig::class, 'setSeparator()')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\AbstractConfig::class, 'separator()')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\AbstractConfig::class, 'offsetExists()')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\AbstractConfig::class, 'offsetGet()')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\AbstractConfig::class, 'offsetSet()')]
class ConfigSeparatorAwareTest extends AbstractConfigTestCase
{
    use AssertionsTrait;

    /**
     * @var MacroConfig
     */
    public $cfg;

    /**
     * @var array
     */
    public $connections;

    /**
     * Create a MacroConfig instance.
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
        $this->cfg = $this->createConfig([
            'connections' => $this->connections
        ]);
    }

    /**
     * Asserts that the object implements SeparatorAwareInterface.
     */
    #[\PHPUnit\Framework\Attributes\CoversNothing]
    public function testSeparatorAwareInterface(): void
    {
        $this->assertInstanceOf(SeparatorAwareInterface::class, $this->cfg);
    }

    public function testDefaultSeparator(): void
    {
        $cfg = $this->createConfig();
        $this->assertEquals(AbstractConfig::DEFAULT_SEPARATOR, $cfg->separator());
    }



    // Test ArrayAccess on nested properties
    // =========================================================================
    /**
     * Asserts that the container returns TRUE if an endpoint is found
     * {@see SeparatorAwareTrait::hasWithSeparator() in a keypath}.
     */
    public function testOffsetExistsOnEndKeyPath(): void
    {
        $cfg = $this->cfg;

        $this->assertTrue(property_exists($cfg, 'connections'));
        $this->assertTrue(isset($cfg['connections.default.host']));
    }

    /**
     * Asserts that the container returns TRUE if a midpoint is found
     * {@see SeparatorAwareTrait::hasWithSeparator() in a keypath}.
     */
    public function testOffsetExistsOnMidKeyPath(): void
    {
        $cfg = $this->cfg;

        $this->assertTrue(property_exists($cfg, 'connections'));
        $this->assertTrue(isset($cfg['connections.default']));
    }

    /**
     * Asserts that the container returns FALSE if an endpoint is nonexistent
     * {@see SeparatorAwareTrait::hasWithSeparator() in a keypath}.
     */
    public function testOffsetExistsReturnsFalseOnNonexistentEndKeyPath(): void
    {
        $cfg = $this->cfg;

        $this->assertFalse(isset($cfg['connections.default.server_version']));
    }

    /**
     * Asserts that the container returns FALSE if a midpoint is nonexistent
     * {@see SeparatorAwareTrait::hasWithSeparator() in a keypath}.
     */
    public function testOffsetExistsReturnsFalseOnNonexistentMidKeyPath(): void
    {
        $cfg = $this->cfg;

        $this->assertFalse(isset($cfg['connections.analytics.server_version']));
    }

    /**
     * Asserts that the container returns the value of the endpoint found
     * {@see SeparatorAwareTrait::getWithSeparator() in a keypath}.
     */
    public function testOffsetGetOnEndKeyPath(): void
    {
        $cfg = $this->cfg;

        $this->assertEquals(
            $this->connections['default']['host'],
            $cfg['connections.default.host']
        );
    }

    /**
     * Asserts that the container returns the value of the midpoint found
     * {@see SeparatorAwareTrait::getWithSeparator() in a keypath}.
     */
    public function testOffsetGetOnMidKeyPath(): void
    {
        $cfg = $this->cfg;

        $this->assertEquals(
            $this->connections['default'],
            $cfg['connections.default']
        );
    }

    /**
     * Asserts that the container returns NULL if the endpoint is nonexistent
     * {@see SeparatorAwareTrait::getWithSeparator() in a keypath}.
     */
    public function testOffsetGetReturnsNullOnNonexistentEndKeyPath(): void
    {
        $cfg = $this->cfg;

        $this->assertNull($cfg['connections.default.server_version']);
    }

    /**
     * Asserts that the container returns NULL if the midpoint is nonexistent
     * {@see SeparatorAwareTrait::getWithSeparator() in a keypath}.
     */
    public function testOffsetGetReturnsNullOnNonexistentMidKeyPath(): void
    {
        $cfg = $this->cfg;

        $this->assertNull($cfg['connections.analytics.server_version']);
    }

    /**
     * Asserts that the container assigns a value to the endpoint
     * {@see SeparatorAwareTrait::setWithSeparator() of the keypath}.
     */
    public function testOffsetSetOnEndKeyPath(): void
    {
        $cfg = $this->cfg;

        $cfg['connections.default.server_version'] = '5.7';
        $this->assertEquals('5.7', $cfg['connections.default.server_version']);
    }

    /**
     * Asserts that the container assigns a value to the endpoint of a nonexistent midpoint
     * {@see SeparatorAwareTrait::setWithSeparator() in the keypath}.
     */
    public function testOffsetSetOnNonexistentMidKeyPath(): void
    {
        $cfg = $this->cfg;
        $this->assertNull($cfg['connections.analytics']);

        $cfg['connections.analytics.server_version'] = '5.6';
        $this->assertArraySubset([ 'server_version' => '5.6' ], $cfg['connections.analytics']);
    }

    /**
     * Asserts that the container assigns NULL to the endpoint
     * {@see SeparatorAwareTrait::setWithSeparator() of the keypath} to "remove".
     */
    #[\PHPUnit\Framework\Attributes\CoversNothing]
    public function testOffsetUnsetOnEndKeyPath(): void
    {
        $cfg = $this->cfg;

        unset($cfg['connections.default.host']);
        $this->assertNull($cfg['connections.default.host']);
    }

    /**
     * Asserts that the container assigns NULL to the midpoint
     * {@see SeparatorAwareTrait::setWithSeparator() of the keypath} to "remove".
     */
    #[\PHPUnit\Framework\Attributes\CoversNothing]
    public function testOffsetUnsetOnMidKeyPath(): void
    {
        $cfg = $this->cfg;

        unset($cfg['connections.default']);
        $this->assertNull($cfg['connections.default']);
    }
}

<?php

namespace Charcoal\Tests\Config\Config;

// From 'charcoal-config'
use Charcoal\Tests\Config\Config\AbstractConfigTestCase;
use Charcoal\Config\GenericConfig;
use Charcoal\Config\FileAwareInterface;
use InvalidArgumentException;

/**
 * Test FileAwareTrait implementation in AbstractConfig
 *
 * For tests of supported formats, lookup {@see \Charcoal\Tests\Config\Mixin\FileLoader}.
 *
 *
 * @todo ::__construct()
 * @todo ::addFile()
 * @todo ::merge()
 *
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Charcoal\Config\AbstractConfig::class)]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\AbstractConfig::class, '__construct()')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\AbstractConfig::class, 'addFile()')]
class ConfigFileAwareTest extends AbstractConfigTestCase
{
    /**
     * @var GenericConfig
     */
    public $cfg;

    /**
     * Create a concrete GenericConfig instance.
     */
    protected function setUp(): void
    {
        $this->cfg = $this->createConfig();
    }

    /**
     * Create a GenericConfig instance.
     *
     * @param  mixed $data      Data to pre-populate the object.
     * @param  array $delegates Delegates to pre-populate the object.
     */
    #[\Override]
    public function createConfig($data = null, ?array $delegates = null): \Charcoal\Config\GenericConfig
    {
        return new GenericConfig($data, $delegates);
    }

    /**
     * Asserts that the object implements FileAwareInterface.
     */
    #[\PHPUnit\Framework\Attributes\CoversNothing]
    public function testFileAwareInterface(): void
    {
        $this->assertInstanceOf(FileAwareInterface::class, $this->cfg);
    }

    public function testConstructWithSupportedFormat(): void
    {
        $path = $this->getPathToFixture('pass/valid.json');
        $cfg  = $this->createConfig($path);
        $this->assertEquals('localhost', $cfg->get('host'));
    }



    // Test INI
    // =========================================================================
    /**
     * INI: Asserts that the Config supports INI config files.
     */
    #[\PHPUnit\Framework\Attributes\CoversNothing]
    public function testAddIniFile(): void
    {
        $path = $this->getPathToFixture('pass/valid1.ini');
        $this->cfg->addFile($path);

        $this->assertEquals('localhost', $this->cfg['host']);
        $this->assertEquals('11211', $this->cfg['port']);
        $this->assertEquals(
            [
                'pdo_mysql',
                'pdo_pgsql',
                'pdo_sqlite',
            ],
            $this->cfg['drivers']
        );
    }

    /**
     * INI: Asserts that the Config supports key-paths in INI config files.
     */
    #[\PHPUnit\Framework\Attributes\CoversNothing]
    public function testAddIniFileWithDelimitedData(): void
    {
        $path = $this->getPathToFixture('pass/valid2.ini');
        $this->cfg->addFile($path);

        $this->assertEquals('utf8mb4', $this->cfg['database']['charset']);
        $this->assertEquals(
            [
                'pdo_mysql',
                'pdo_pgsql',
                'pdo_sqlite',
            ],
            $this->cfg['database']['drivers']
        );
    }

    /**
     * INI: Asserts that an ordered list is NOT ignored.
     */
    public function testAddIniFileWithInvalidArray(): void
    {
        $this->expectExceptionMessage('Entity array access only supports non-numeric keys');
        $this->expectException(InvalidArgumentException::class);

        $path = $this->getPathToFixture('fail/invalid1.ini');
        $this->cfg->addFile($path);
    }

    /**
     * INI: Asserts that an unparsable file is silently ignored.
     */
    public function testAddUnparsableIniFile(): void
    {
        // phpcs:disable Generic.PHP.NoSilencedErrors.Discouraged
        $path = $this->getPathToFixture('pass/unparsable.ini');
        @$this->cfg->addFile($path);
        // phpcs:enable

        $this->assertEquals([], $this->cfg->data());
    }



    // Test JSON
    // =========================================================================
    /**
     * JSON: Asserts that the Config supports JSON config files.
     */
    #[\PHPUnit\Framework\Attributes\CoversNothing]
    public function testAddJsonFile(): void
    {
        $path = $this->getPathToFixture('pass/valid.json');
        $this->cfg->addFile($path);

        $this->assertEquals('localhost', $this->cfg['host']);
        $this->assertEquals('11211', $this->cfg['port']);
        $this->assertEquals(
            [
                'pdo_mysql',
                'pdo_pgsql',
                'pdo_sqlite',
            ],
            $this->cfg['drivers']
        );
    }

    /**
     * JSON: Asserts that an ordered list is NOT ignored.
     */
    public function testAddJsonFileWithInvalidArray(): void
    {
        $this->expectExceptionMessage('Entity array access only supports non-numeric keys');
        $this->expectException(InvalidArgumentException::class);

        $path = $this->getPathToFixture('fail/invalid1.json');
        $this->cfg->addFile($path);
    }

    /**
     * JSON: Asserts that an invalid file is silently ignored.
     */
    public function testAddJsonFileWithInvalidType(): void
    {
        $path = $this->getPathToFixture('pass/invalid2.json');
        $this->cfg->addFile($path);

        $this->assertEquals([], $this->cfg->data());
    }



    // Test PHP
    // =========================================================================
    /**
     * PHP: Asserts that the Config supports PHP config files.
     */
    #[\PHPUnit\Framework\Attributes\CoversNothing]
    public function testAddPhpFile(): void
    {
        $path = $this->getPathToFixture('pass/valid1.php');
        $this->cfg->addFile($path);

        $this->assertEquals('localhost', $this->cfg['host']);
        $this->assertEquals('11211', $this->cfg['port']);
        $this->assertEquals(
            [
                'pdo_mysql',
                'pdo_pgsql',
                'pdo_sqlite',
            ],
            $this->cfg['drivers']
        );
    }

    /**
     * PHP: Asserts that the scope of PHP config files is bound to the Config.
     */
    #[\PHPUnit\Framework\Attributes\CoversNothing]
    public function testAddPhpFileThatMutatesContext(): void
    {
        $path = $this->getPathToFixture('pass/valid2.php');
        $this->cfg->addFile($path);

        $this->assertEquals('localhost', $this->cfg['host']);
        $this->assertEquals('utf8mb4', $this->cfg['database']['charset']);
        $this->assertEquals(
            [
                'pdo_mysql',
                'pdo_pgsql',
                'pdo_sqlite',
            ],
            $this->cfg['database']['drivers']
        );
    }

    /**
     * PHP: Asserts that an ordered list is NOT ignored.
     */
    public function testAddPhpFileWithInvalidArray(): void
    {
        $this->expectExceptionMessage('Entity array access only supports non-numeric keys');
        $this->expectException(InvalidArgumentException::class);

        $path = $this->getPathToFixture('fail/invalid1.php');
        $this->cfg->addFile($path);
    }

    /**
     * PHP: Asserts that an invalid file is silently ignored.
     */
    public function testAddPhpFileWithInvalidType(): void
    {
        $path = $this->getPathToFixture('pass/invalid2.php');
        $this->cfg->addFile($path);

        $this->assertEquals([], $this->cfg->data());
    }



    // Test YAML
    // =========================================================================
    /**
     * YAML: Asserts that the Config supports '.yml' YAML config files.
     */
    #[\PHPUnit\Framework\Attributes\CoversNothing]
    public function testAddYamlFile(): void
    {
        $path = $this->getPathToFixture('pass/valid1.yml');
        $this->cfg->addFile($path);

        $this->assertEquals('localhost', $this->cfg['host']);
        $this->assertEquals('11211', $this->cfg['port']);
        $this->assertEquals(
            [
                'pdo_mysql',
                'pdo_pgsql',
                'pdo_sqlite',
            ],
            $this->cfg['drivers']
        );
    }

    /**
     * YAML: Asserts that an ordered list is NOT ignored.
     */
    public function testAddYamlFileWithInvalidArray(): void
    {
        $this->expectExceptionMessage('Entity array access only supports non-numeric keys');
        $this->expectException(InvalidArgumentException::class);

        $path = $this->getPathToFixture('fail/invalid1.yml');
        $this->cfg->addFile($path);
    }

    /**
     * YAML: Asserts that an invalid file is silently ignored.
     */
    public function testAddYamlFileWithInvalidType(): void
    {
        $path = $this->getPathToFixture('pass/invalid2.yml');
        $this->cfg->addFile($path);

        $this->assertEquals([], $this->cfg->data());
    }
}

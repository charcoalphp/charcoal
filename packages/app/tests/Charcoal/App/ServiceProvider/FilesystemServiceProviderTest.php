<?php

namespace Charcoal\Tests\App\ServiceProvider;

use Charcoal\App\AppConfig;
use DI\Container;
// Dependencies from `league/flysystem`
use League\Flysystem\MountManager;
use League\Flysystem\Filesystem;
use Charcoal\App\Config\FilesystemConfig;
use Charcoal\App\ServiceProvider\FilesystemServiceProvider;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class FilesystemServiceProviderTest extends AbstractTestCase
{
    private $obj;

    public function setUp(): void
    {
        $this->obj = new FilesystemServiceProvider();
    }

    public function testProvider()
    {
        $container = $this->getContainer([
            'config' => $this->createAppConfig([
                'filesystem' => []
            ])
        ]);

        $this->assertTrue($container->has('filesystem/config'));
        $this->assertTrue($container->has('filesystem/manager'));
        $this->assertTrue($container->has('filesystems'));

        $this->assertInstanceOf(FilesystemConfig::class, $container->get('filesystem/config'));
        $this->assertInstanceOf(MountManager::class, $container->get('filesystem/manager'));
        $this->assertInstanceOf(Container::class, $container->get('filesystems'));
    }

    public function testProviderDefaultAdapters()
    {
        $container = $this->getContainer([
            'config' => $this->createAppConfig([
                'filesystem' => []
            ])
        ]);

        $this->assertTrue(isset($container->get('filesystems')['private']));
        $this->assertTrue(isset($container->get('filesystems')['public']));

        $this->assertInstanceOf(Filesystem::class, $container->get('filesystems')['private']);
        $this->assertInstanceOf(Filesystem::class, $container->get('filesystems')['public']);
    }

    public function testProviderLocalAdapter()
    {
        $container = $this->getContainer([
            'config' => $this->createAppConfig([
                'filesystem' => [
                    'connections' => [
                        'local' => [
                            'type' => 'local',
                            'path' => '/',
                        ]
                    ]
                ]
            ])
        ]);

        $this->assertTrue(isset($container->get('filesystems')['local']));
        $this->assertInstanceOf(Filesystem::class, $container->get('filesystems')['local']);
    }

    public function testProviderS3Adapter()
    {
        $container = $this->getContainer([
            'config' => $this->createAppConfig([
                'filesystem' => [
                    'connections' => [
                        's3' => [
                            'type'   => 's3',
                            'key'    => 'key',
                            'secret' => 'secret',
                            'bucket' => 'bucket',
                            'region' => 'region',
                        ],
                    ],
                ],
            ]),
        ]);

        $this->assertTrue(isset($container->get('filesystems')['s3']));
        $this->assertInstanceOf(Filesystem::class, $container->get('filesystems')['s3']);
    }

    public function testProviderFtpAdapter()
    {
        $container = $this->getContainer([
            'config' => $this->createAppConfig([
                'filesystem' => [
                    'connections' => [
                        'ftp' => [
                            'type'      => 'ftp',
                            'host'      => 'localhost',
                            'username'  => 'username',
                            'password'  => 'password'
                        ]
                    ]
                ]
            ])
        ]);

        $this->assertTrue(isset($container->get('filesystems')['ftp']));
        $this->assertInstanceOf(Filesystem::class, $container->get('filesystems')['ftp']);
    }

    public function testProviderSftpAdapter()
    {
        $container = $this->getContainer([
            'config' => $this->createAppConfig([
                'filesystem' => [
                    'connections' => [
                        'sftp' => [
                            'type'      => 'sftp',
                            'host'      => 'localhost',
                            'username'  => 'username',
                            'password'  => 'password'
                        ]
                    ]
                ]
            ])
        ]);

        $this->assertTrue(isset($container->get('filesystems')['sftp']));
        $this->assertInstanceOf(Filesystem::class, $container->get('filesystems')['sftp']);
    }

    public function testProviderMemorypAdapter()
    {
        $container = $this->getContainer([
            'config' => $this->createAppConfig([
                'filesystem' => [
                    'connections' => [
                        'memory' => [
                            'type'  => 'memory'
                        ]
                    ]
                ]
            ])
        ]);


        $this->assertTrue(isset($container->get('filesystems')['memory']));
        $this->assertInstanceOf(Filesystem::class, $container->get('filesystems')['memory']);
    }

    public function testProviderNullAdapter()
    {
        $container = $this->getContainer([
            'config' => $this->createAppConfig([
                'filesystem' => [
                    'connections' => [
                        'test' => [
                            'type' => 'noop'
                        ]
                    ]
                ]
            ])
        ]);

        $this->assertTrue(isset($container->get('filesystems')['test']));
        $this->assertInstanceOf(Filesystem::class, $container->get('filesystems')['test']);
    }

    public function testConfigWithoutTypeThrowsException()
    {
        $this->expectException('\Exception');
        $container = $this->getContainer([
            'config' => $this->createAppConfig([
                'filesystem' => [
                    'connections' => [
                        'test' => []
                    ]
                ]
            ])
        ]);

        $test = $container->get('filesystem/test');
    }

    private function createAppConfig($defaults = null)
    {
        return new AppConfig(array_replace(['base_path' => sys_get_temp_dir()], $defaults));
    }

    private function getContainer($defaults = null)
    {
        $container = new Container($defaults);
        $this->obj->register($container);

        return $container;
    }
}

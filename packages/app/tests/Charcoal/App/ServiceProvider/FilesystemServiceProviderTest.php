<?php

namespace Charcoal\Tests\App\ServiceProvider;

use Charcoal\App\AppConfig;
use Pimple\Container;

// Dependencies from `league/flysystem`
use League\Flysystem\MountManager;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local as LocalAdapter;
use League\Flysystem\Adapter\Ftp as FtpAdapter;
use League\Flysystem\Adapter\NullAdapter;

// Dependency from `league/flysystem-aws-s3-v3`
use League\Flysystem\AwsS3v3\AwsS3Adapter;

// Dependency from `league/flysystem-dropbox`
use League\Flysystem\Dropbox\DropboxAdapter;

// Dependency from `league/flysystem-sftp`
use League\Flysystem\Sftp\SftpAdapter;

use Charcoal\App\Config\FilesystemConfig;
use Charcoal\App\ServiceProvider\FilesystemServiceProvider;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class FilesystemServiceProviderTest extends AbstractTestCase
{
    private \Charcoal\App\ServiceProvider\FilesystemServiceProvider $obj;

    public function setUp(): void
    {
        $this->obj = new FilesystemServiceProvider();
    }

    public function testProvider(): void
    {
        $container = $this->getContainer([
            'config' => $this->createAppConfig([
                'filesystem' => []
            ])
        ]);

        $this->assertTrue(isset($container['filesystem/config']));
        $this->assertTrue(isset($container['filesystem/manager']));
        $this->assertTrue(isset($container['filesystems']));

        $this->assertInstanceOf(FilesystemConfig::class, $container['filesystem/config']);
        $this->assertInstanceOf(MountManager::class, $container['filesystem/manager']);
        $this->assertInstanceOf(Container::class, $container['filesystems']);
    }

    public function testProviderDefaultAdapters(): void
    {
        $container = $this->getContainer([
            'config' => $this->createAppConfig([
                'filesystem' => []
            ])
        ]);

        $this->assertTrue(isset($container['filesystems']['private']));
        $this->assertTrue(isset($container['filesystems']['public']));

        $this->assertInstanceOf(Filesystem::class, $container['filesystems']['private']);
        $this->assertInstanceOf(Filesystem::class, $container['filesystems']['public']);
    }

    public function testProviderLocalAdapter(): void
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

        $this->assertTrue(isset($container['filesystems']['local']));
        $this->assertInstanceOf(Filesystem::class, $container['filesystems']['local']);
    }

    public function testProviderS3Adapter(): void
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

        $this->assertTrue(isset($container['filesystems']['s3']));
        $this->assertInstanceOf(Filesystem::class, $container['filesystems']['s3']);
    }

    public function testProviderFtpAdapter(): void
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

        $this->assertTrue(isset($container['filesystems']['ftp']));
        $this->assertInstanceOf(Filesystem::class, $container['filesystems']['ftp']);
    }

    public function testProviderSftpAdapter(): void
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

        $this->assertTrue(isset($container['filesystems']['sftp']));
        $this->assertInstanceOf(Filesystem::class, $container['filesystems']['sftp']);
    }

    public function testProviderMemorypAdapter(): void
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


        $this->assertTrue(isset($container['filesystems']['memory']));
        $this->assertInstanceOf(Filesystem::class, $container['filesystems']['memory']);
    }

    public function testProviderNullAdapter(): void
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

        $this->assertTrue(isset($container['filesystems']['test']));
        $this->assertInstanceOf(Filesystem::class, $container['filesystems']['test']);
    }

    public function testConfigWithoutTypeThrowsException(): void
    {
        $this->expectException('\Exception');
        $this->getContainer([
            'config' => $this->createAppConfig([
                'filesystem' => [
                    'connections' => [
                        'test' => []
                    ]
                ]
            ])
        ]);
    }

    private function createAppConfig($defaults = null): \Charcoal\App\AppConfig
    {
        return new AppConfig(array_replace(['base_path' => sys_get_temp_dir()], $defaults));
    }

    private function getContainer($defaults = null): \Pimple\Container
    {
        $container = new Container($defaults);
        $this->obj->register($container);

        return $container;
    }
}

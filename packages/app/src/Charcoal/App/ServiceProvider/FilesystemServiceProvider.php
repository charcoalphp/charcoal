<?php

namespace Charcoal\App\ServiceProvider;

use Exception;
use InvalidArgumentException;
use UnexpectedValueException;
// From Pimple
use Pimple\ServiceProviderInterface;
use Pimple\Container;
// From 'aws/aws-sdk-php'
use Aws\S3\S3Client;
// From 'league/flysystem'
use League\Flysystem\MountManager;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local as LocalAdapter;
use League\Flysystem\Adapter\Ftp as FtpAdapter;
use League\Flysystem\Adapter\NullAdapter;
// From 'league/flysystem-aws-s3-v3'
use League\Flysystem\AwsS3v3\AwsS3Adapter;
// From 'league/flysystem-sftp'
use League\Flysystem\Sftp\SftpAdapter;
// From 'league/flysystem-memory'
use League\Flysystem\Memory\MemoryAdapter;
// From 'charcoal-app'
use Charcoal\App\AppConfig;
use Charcoal\App\Config\FilesystemConfig;

/**
 *
 */
class FilesystemServiceProvider implements ServiceProviderInterface
{
    /**
     * @param  Container $container A service container.
     */
    public function register(Container $container): void
    {
        /**
         * @param  Container $container A service container.
         * @return FilesystemConfig
         */
        $container['filesystem/config'] = function (Container $container): \Charcoal\App\Config\FilesystemConfig {
            $fsConfig = ($container['config']['filesystem'] ?? null);
            return new FilesystemConfig($fsConfig);
        };

        /**
         * @param  Container $container A service container.
         * @return MountManager
         */
        $container['filesystem/manager'] = (fn(): \League\Flysystem\MountManager => new MountManager());

        /**
         * @param  Container $container A service container.
         * @return array<string, Filesystem>
         */
        $container['filesystems'] = function (Container $container): \Pimple\Container {
            $filesystemConfig = $container['filesystem/config'];
            $filesystems = new Container();

            foreach ($filesystemConfig['connections'] as $ident => $connection) {
                $fs = $this->createConnection($connection, $container);
                $filesystems[$ident] = $fs;
                $container['filesystem/manager']->mountFilesystem($ident, $fs);
            }

            return $filesystems;
        };
    }

    /**
     * @param  array $config The driver (adapter) configuration.
     * @param  Container $container A service container.
     * @throws Exception If the filesystem type is not defined in config.
     * @throws UnexpectedValueException If the filesystem type is invalid / unsupported.
     */
    private function createConnection(array $config, Container $container): \League\Flysystem\Filesystem
    {
        if (!isset($config['type'])) {
            throw new Exception(
                'No filesystem type defined'
            );
        }

        $type = $config['type'];

        $adapter = match ($type) {
            'local' => $this->createLocalAdapter($config, $container),
            's3' => $this->createS3Adapter($config),
            'ftp' => $this->createFtpAdapter($config),
            'sftp' => $this->createSftpAdapter($config),
            'memory' => $this->createMemoryAdapter(),
            'noop' => $this->createNullAdapter(),
            default => throw new UnexpectedValueException(
                sprintf('Invalid filesystem type "%s"', $type)
            ),
        };

        return new Filesystem($adapter);
    }

    /**
     * @param  array $config The driver (adapter) configuration.
     * @param  Container $container A service container.
     * @throws InvalidArgumentException If the path is not defined.
     */
    private function createLocalAdapter(array $config, Container $container): \League\Flysystem\Adapter\Local
    {
        if (empty($config['path'])) {
            throw new InvalidArgumentException(
                'No "path" configured for local filesystem.'
            );
        }

        $path = $config['path'];
        if (is_string($path) && (isset($container['config']) && $container['config'] instanceof AppConfig)) {
            $path = $container['config']->resolveValue($path);
        }

        $defaults = [
            'lock'        => null,
            'links'       => null,
            'permissions' => [],
        ];
        $config = array_merge($defaults, $config);

        return new LocalAdapter($path, $config['lock'], $config['links'], $config['permissions']);
    }

    /**
     * @param  array $config The driver (adapter) configuration.
     * @throws InvalidArgumentException If the key, secret or bucket is not defined in config.
     */
    private function createS3Adapter(array $config): \League\Flysystem\AwsS3v3\AwsS3Adapter
    {
        if (!isset($config['key']) || !$config['key']) {
            throw new InvalidArgumentException(
                'No "key" configured for S3 filesystem.'
            );
        }

        if (!isset($config['secret']) || !$config['secret']) {
            throw new InvalidArgumentException(
                'No "secret" configured for S3 filesystem.'
            );
        }

        if (!isset($config['bucket']) || !$config['bucket']) {
            throw new InvalidArgumentException(
                'No "bucket" configured for S3 filesystem.'
            );
        }

        $defaults = [
            'region'  => '',
            'version' => 'latest',
            'prefix'  => null,
        ];
        $config = array_merge($defaults, $config);

        $client = S3Client::factory([
            'credentials' => [
                'key'     => $config['key'],
                'secret'  => $config['secret'],
            ],
            'region'      => $config['region'],
            'version'     => $config['version'],
        ]);

        $permissions = isset($config['public']) && !$config['public'] ? null : [
            'ACL' => 'public-read',
        ];

        return new AwsS3Adapter($client, $config['bucket'], $config['prefix'], $permissions);
    }

    /**
     * @param  array $config The driver (adapter) configuration.
     * @throws InvalidArgumentException If the host, username or password is not defined in config.
     */
    private function createFtpAdapter(array $config): \League\Flysystem\Adapter\Ftp
    {
        if (!$config['host']) {
            throw new InvalidArgumentException(
                'No host configured for FTP filesystem adapter.'
            );
        }

        if (!$config['username']) {
            throw new InvalidArgumentException(
                'No username configured for FTP filesystem adapter.'
            );
        }

        if (!$config['password']) {
            throw new InvalidArgumentException(
                'No password configured for FTP filesystem adapter.'
            );
        }

        $defaults = [
            'port'    => null,
            'root'    => null,
            'passive' => null,
            'ssl'     => null,
            'timeout' => null,
        ];
        $config = array_merge($defaults, $config);

        return new FtpAdapter($config);
    }

    /**
     * @param  array $config The driver (adapter) configuration.
     * @throws InvalidArgumentException If the host, username or password is not defined in config.
     */
    private function createSftpAdapter(array $config): \League\Flysystem\Sftp\SftpAdapter
    {
        if (!$config['host']) {
            throw new InvalidArgumentException(
                'No host configured for SFTP filesystem adapter.'
            );
        }

        if (!$config['username']) {
            throw new InvalidArgumentException(
                'No username configured for SFTP filesystem adapter.'
            );
        }

        if (!$config['password']) {
            throw new InvalidArgumentException(
                'No password configured for SFTP filesystem adapter.'
            );
        }

        $defaults = [
            'port'       => null,
            'privateKey' => null,
            'root'       => null,
            'timeout'    => null,
        ];
        $config = array_merge($defaults, $config);

        return new SftpAdapter($config);
    }

    private function createMemoryAdapter(): \League\Flysystem\Memory\MemoryAdapter
    {
        return new MemoryAdapter();
    }

    private function createNullAdapter(): \League\Flysystem\Adapter\NullAdapter
    {
        return new NullAdapter();
    }
}

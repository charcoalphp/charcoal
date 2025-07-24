<?php

namespace Charcoal\App\ServiceProvider;

use Exception;
use PDO;
use DI\Container;
// From 'charcoal-app'
use Charcoal\App\Config\DatabaseConfig;

/**
 * Database Service Provider. Configures and provides a PDO service to a container.
 *
 * ## Services
 *
 * - `database` The `\PDO` instance.
 * - `databases` Container of all availables `\PDO` databases.
 *
 * ## Helpers
 *
 * - `database/config` A `DatabaseConfig` object containing the DB settings.
 * - `databases/config A container of `DatabaseConfig`
 */
class DatabaseServiceProvider
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param  Container $container A service container.
     * @return void
     */
    public function register(Container $container)
    {
        /**
         * @param  Container $container A service container.
         * @return Container<string, DatabaseConfig> A map of database configsets.
         */
        $container->set('databases/config', function (Container $container) {
            $databases = ($container->get('config')['databases'] ?? []);

            $configs = new Container();
            foreach ($databases as $dbIdent => $dbOptions) {
                /**
                 * @return DatabaseConfig
                 */
                $configs[$dbIdent] = function () use ($dbOptions) {
                    return new DatabaseConfig($dbOptions);
                };
            }

            return $configs;
        });

        /**
         * @param  Container $container A service container.
         * @return Container<string, PDO> A map of database handlers.
         */
        $container->set('databases', function (Container $container) {
            $databases = ($container->get('config')['databases'] ?? []);

            $dbs = new Container();
            foreach (array_keys($databases) as $dbIdent) {
                /**
                 * @return PDO
                 */
                $dbs[$dbIdent] = function () use ($dbIdent, $container) {
                    $dbConfig = $container->get('databases/config')[$dbIdent];

                    $type = $dbConfig['type'];
                    $host = $dbConfig['hostname'];

                    $database = $dbConfig['database'];
                    $username = $dbConfig['username'];
                    $password = $dbConfig['password'];

                    // Set UTf-8 compatibility by default. Disable it if it is set as such in config
                    $extraOptions = null;
                    if (!isset($dbConfig['disable_utf8']) || !$dbConfig['disable_utf8']) {
                        $extraOptions = [
                            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4',
                        ];
                    }

                    if ($type === 'sqlite') {
                        $dsn = $type . ':' . $database;
                    } else {
                        $dsn = $type . ':host=' . $host . ';dbname=' . $database;
                    }

                    $db = new PDO($dsn, $username, $password, $extraOptions);

                    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    if ($type === 'mysql') {
                        $db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
                    }

                    return $db;
                };
            }

            return $dbs;
        });

        /**
         * The default database configuration.
         *
         * @param  Container $container A service container.
         * @throws Exception If the database configset is invalid.
         * @return DatabaseConfig
         */
        $container->set('database/config', function (Container $container) {
            $dbIdent   = ($container->get('config')['default_database'] ?? 'default');
            $dbConfigs = $container->get('databases/config');

            if (!isset($dbConfigs[$dbIdent])) {
                throw new Exception(
                    sprintf('The database config "%s" is not defined in the "databases" configuration.', $dbIdent)
                );
            }

            return $dbConfigs[$dbIdent];
        });

        /**
         * The default database handler.
         *
         * @param  Container $container A service container.
         * @throws Exception If the database configuration is invalid.
         * @return PDO
         */
        $container->set('database', function (Container $container) {
            $dbIdent   = ($container->get('config')['default_database'] ?? 'default');
            $databases = $container->get('databases');

            if (!isset($databases[$dbIdent])) {
                throw new Exception(
                    sprintf('The database "%s" is not defined in the "databases" configuration.', $dbIdent)
                );
            }

            return $databases[$dbIdent];
        });
    }
}

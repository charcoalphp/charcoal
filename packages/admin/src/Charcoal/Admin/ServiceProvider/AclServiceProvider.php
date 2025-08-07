<?php

namespace Charcoal\Admin\ServiceProvider;

use DI\Container;
// From 'laminas/laminas-permissions-acl'
use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Resource\GenericResource as AclResource;
// From 'charcoal-user'
use Charcoal\User\Acl\Manager as AclManager;
use Psr\Container\ContainerInterface;

/**
 * Admin ACL (Access-Control-List) provider.
 *
 * Like all service providers, this class is intended to be registered on a (DI) container.
 *
 * ## Services
 *
 * - `admin/acl` A Laminas ACL instance containing the admin resources / permissions.
 *
 * ## Dependencies
 *
 * This service provider expects a few "global" services to be registered on the container:
 * - `logger`, a PSR-3 logger
 * - `database`, a PDO instance
 * - `admin/config`, a configset of the admin
 */
class AclServiceProvider
{
    /**
     * @param Container $container DI Container.
     * @return void
     */
    public function register(ContainerInterface $container)
    {
        /**
         * Use an AclManager to load default permissions from config and database.
         *
         * @param Container $container DI container
         * @return Acl
         */
        $container->set('admin/acl', function (ContainerInterface $container) {
            $adminConfig = $container->get('admin/config');

            $resourceName = 'admin';
            $tableName = 'charcoal_admin_acl_roles';

            $aclManager = new AclManager([
                'logger' => $container->get('logger')
            ]);

            $acl = new Acl();

             // Add admin resource for ACL
            $acl->addResource(new AclResource($resourceName));

            // Setup default permissions (from admin config)
            $permissions = $adminConfig['acl.permissions'];
            if (!empty($permissions)) {
                $aclManager->loadPermissions($acl, $permissions, $resourceName);
            }

            // Setup roles and permissions from database
            $aclManager->loadDatabasePermissions($acl, $container->get('database'), $tableName, $resourceName);

            return $acl;
        });

        /**
         * Replace default ACL ('charcoal-user') with the Admin ACL.
         *
         * @todo   Do this right!
         * @return Acl
         */
        $container->set('authorizer/acl', function () use ($container) {
            return $container->get('admin/acl');
        });
    }
}

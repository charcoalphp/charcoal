<?php

namespace Charcoal\User\Acl;

use PDO;
use PDOException;
// From PSR-3
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
// From 'laminas/laminas-permissions-acl'
use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Role\GenericRole;

/**
 * Manage ACL roles and permissions from config (arrays) or database.
 */
class Manager implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * Constructor options:
     * - `logger`
     *
     * @param array $data Constructor options.
     */
    public function __construct(array $data)
    {
        $this->setLogger($data['logger']);
    }

    /**
     * @param  Acl    $acl         The Laminas Acl instant to load permissions to.
     * @param  array  $permissions The array of permissions, in [role=>details] array.
     * @param  string $resource    The Acl resource (string identifier) to load roles and permissions into.
     */
    public function loadPermissions(Acl &$acl, array $permissions, $resource = ''): void
    {
        foreach ($permissions as $role => $rolePermissions) {
            $this->addRoleAndPermissions($acl, $role, $rolePermissions, $resource);
        }
    }

    /**
     * @param  Acl    $acl      The Laminas Acl instance to load permissions to.
     * @param  PDO    $dbh      The PDO database instance.
     * @param  string $table    The table where to fetch the roles and permissions.
     * @param  string $resource The Acl resource (string identifier) to load roles and permissions into.
     */
    public function loadDatabasePermissions(Acl &$acl, PDO $dbh, $table, $resource = ''): void
    {
        // Quick-and-dirty sanitization
        $table = preg_replace('/[^A-Za-z0-9_]/', '', $table);

        $query = sprintf(
            'SELECT * FROM `%s` ORDER BY `position` ASC',
            $table
        );

        $this->logger->debug($query);

        // Put inside a try-catch block because ACL is optional; table might not exist.
        try {
            $sth = $dbh->query($query);
            while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
                $this->addRoleAndPermissions($acl, $row['ident'], $row, $resource);
            }
        } catch (PDOException $e) {
            $this->logger->warning('Can not fetch ACL roles: ' . $e->getMessage());
        }
    }

    /**
     * @param  Acl    $acl         The Laminas Acl instant to add permissions to.
     * @param  string $role        The role (string identifier) to add.
     * @param  array  $permissions The permissions details (array) to add.
     * @param  string $resource    The Acl resource (string identifier) to add roles and permissions into.
     */
    private function addRoleAndPermissions(Acl &$acl, $role, array $permissions, $resource): void
    {
        if (!$acl->hasRole($role)) {
            // Add role
            $parentRole = ($permissions['parent'] ?? null);
            $parentRole = $parentRole ?: null;
            $newRole = new GenericRole($role);
            $acl->addRole($newRole, $parentRole);
        }

        if (isset($permissions['superuser']) && $permissions['superuser']) {
            $acl->allow($role);

            return;
        }

        if (isset($permissions['allowed'])) {
            if (is_string($permissions['allowed'])) {
                $allowedPermissions = explode(',', $permissions['allowed']);
            } else {
                $allowedPermissions = $permissions['allowed'];
            }
            foreach ($allowedPermissions as $allowed) {
                $acl->allow($role, $resource, $allowed);
            }
        }

        if (isset($permissions['denied'])) {
            $deniedPermissions = is_string($permissions['denied']) ? explode(',', $permissions['denied']) : $permissions['denied'];
            foreach ($deniedPermissions as $denied) {
                $acl->deny($role, $resource, $denied);
            }
        }
    }
}

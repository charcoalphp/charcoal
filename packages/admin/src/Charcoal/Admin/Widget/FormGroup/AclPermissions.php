<?php

namespace Charcoal\Admin\Widget\FormGroup;

use PDO;
use RuntimeException;
// From Pimple
use Pimple\Container;
// From 'laminas/laminas-permissions-acl'
use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Role\GenericRole as Role;
use Laminas\Permissions\Acl\Resource\GenericResource as Resource;
// From 'charcoal-core'
use Charcoal\Loader\CollectionLoader;
// From 'charcoal-ui'
use Charcoal\Ui\FormGroup\FormGroupInterface;
use Charcoal\Ui\FormGroup\FormGroupTrait;
// From 'charcoal-user'
use Charcoal\User\Acl\Manager as AclManager;
// From 'charcoal-admin'
use Charcoal\Admin\AdminWidget;
use Charcoal\Admin\User\Permission;
use Charcoal\Admin\User\PermissionCategory;

/**
 * ACL Permissions Widget (Form Group)
 */
class AclPermissions extends AdminWidget implements
    FormGroupInterface
{
    use FormGroupTrait;

    private ?\Laminas\Permissions\Acl\Acl $roleAcl = null;

    private ?array $roleAllowed = null;

    private ?array $roleDenied = null;

    /**
     * Store the collection loader for the current class.
     *
     * @var CollectionLoader
     */
    private $collectionLoader;

    /**
     * Store the ACL roles and permissions manager.
     *
     * @var AclManager
     */
    private $aclManager;

    /**
     * Store the database connection.
     *
     * @var PDO
     */
    private $database;

    /**
     * Retrieve the current object ID from the GET parameters.
     *
     * @return string
     */
    public function objId(): string|false|null
    {
        return htmlspecialchars(trim($_GET['obj_id'] ?? ''), ENT_QUOTES, 'UTF-8');
    }

    public function permissionCategories(): array
    {
        $loader = $this->collectionLoader();
        $loader->setModel(PermissionCategory::class);
        $categories = $loader->load();

        $ret = [];
        foreach ($categories as $c) {
            $ret[] = [
                'ident'       => $c->id(),
                'name'        => $c->name(),
                'permissions' => $this->loadCategoryPermissions($c->id())
            ];
        }

        return $ret;
    }

    /**
     * Inject dependencies from a DI Container.
     *
     * @param  Container $container A dependencies container instance.
     * @return void
     */
    #[\Override]
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->database         = $container['database'];
        $this->aclManager       = $container['admin/acl'];
        $this->collectionLoader = $container['model/collection/loader'];
    }

    /**
     * Retrieve the database connection.
     *
     * @throws RuntimeException If the connection is undefined.
     * @return PDO
     */
    protected function db()
    {
        if ($this->database === null) {
            throw new RuntimeException(sprintf(
                'Database Connection is not defined for "%s"',
                static::class
            ));
        }

        return $this->database;
    }

    /**
     * Retrieve the ACL manager.
     *
     * @throws RuntimeException If the manager is undefined.
     * @return AclManager
     */
    protected function adminAcl()
    {
        if ($this->aclManager === null) {
            throw new RuntimeException(sprintf(
                'ACL Manager is not defined for "%s"',
                static::class
            ));
        }

        return $this->aclManager;
    }

    /**
     * Retrieve the model collection loader.
     *
     * @throws RuntimeException If the collection loader was not previously set.
     * @return CollectionLoader
     */
    protected function collectionLoader()
    {
        if ($this->collectionLoader === null) {
            throw new RuntimeException(sprintf(
                'Collection Loader is not defined for "%s"',
                static::class
            ));
        }

        return $this->collectionLoader;
    }



    protected function roleAcl(): \Laminas\Permissions\Acl\Acl
    {
        if (!$this->roleAcl instanceof \Laminas\Permissions\Acl\Acl) {
            $id = $this->objId();

            $this->roleAcl = new Acl();
            $this->roleAcl->addRole(new Role($id));
            $this->roleAcl->addResource(new Resource('admin'));

            $q = '
                SELECT
                    `denied`,
                    `allowed`,
                    `superuser`
                FROM
                    `charcoal_admin_acl_roles`
                WHERE
                    ident = :id';

            $sth = $this->db()->prepare($q);
            $sth->bindParam(':id', $id);
            $sth->execute();
            $permissions = $sth->fetch(PDO::FETCH_ASSOC);

            $this->roleAllowed = explode(',', trim((string)$permissions['allowed']));
            $this->roleDenied  = explode(',', trim((string)$permissions['denied']));

            foreach ($this->roleAllowed as $allowed) {
                $this->roleAcl->allow($id, 'admin', $allowed);
            }

            foreach ($this->roleDenied as $denied) {
                $this->roleAcl->deny($id, 'admin', $denied);
            }
        }
        return $this->roleAcl;
    }

    /**
     * @param string $category The category ident to load permissions from.
     */
    private function loadCategoryPermissions($category): array
    {
        $adminAcl = $this->adminAcl();
        $this->roleAcl();

        $loader = $this->collectionLoader();
        $loader->setModel(Permission::class);
        $loader->addFilter('category', $category);
        $permissions = $loader->load();

        $ret = [];
        foreach ($permissions as $perm) {
            $ident = $perm->id();

            $permission = [
                'ident'     => $ident,
                'name'      => $perm->name(),
                'isDenied'  => false,
                'isUnknown' => false,
                'isAllowed' => false,
            ];

            if (in_array($ident, $this->roleAllowed)) {
                $permission['status']    = 'allowed';
                $permission['isAllowed'] = true;
            } elseif (in_array($ident, $this->roleDenied)) {
                $permission['status']   = 'denied';
                $permission['isDenied'] = true;
            } else {
                $permission['status']    = '';
                $permission['isUnknown'] = true;
            }

            if ($adminAcl->hasResource($ident)) {
                $permission['parent_status'] = $adminAcl->isAllowed($ident, 'admin') ? 'allowed' : 'denied';
            } else {
                $permission['parent_status'] = '';
            }

            $ret[] = $permission;
        }

        return $ret;
    }
}

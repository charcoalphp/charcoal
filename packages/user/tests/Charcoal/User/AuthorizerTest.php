<?php

namespace Charcoal\Tests\User;

// From Pimple
use Pimple\Container;

// From 'laminas/laminas-permissions-acl'
use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Exception\ExceptionInterface as AclExceptionInterface;
use Laminas\Permissions\Acl\Resource\GenericResource as Resource;
use Laminas\Permissions\Acl\Role\GenericRole as Role;

// From 'charcoal-user'
use Charcoal\User\AbstractAuthorizer;
use Charcoal\User\Authorizer;
use Charcoal\User\AuthorizerInterface;
use Charcoal\User\GenericUser;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\ReflectionsTrait;
use Charcoal\Tests\User\ContainerProvider;

/**
 *
 */
class AuthorizerTest extends AbstractTestCase
{
    use ReflectionsTrait;

    /**
     * Tested Class.
     */
    private \Charcoal\User\Authorizer $auth;

    /**
     * Store the ACL manager.
     */
    private \Laminas\Permissions\Acl\Acl $acl;

    /**
     * Store the service container.
     */
    private ?\Pimple\Container $container = null;

    /**
     * Set up the test.
     */
    protected function setUp(): void
    {
        $this->container();

        $this->acl = new Acl();
        $this->acl->addResource('area');
        $this->acl->addRole('guest');

        $this->auth = $this->createAuthorizer();
    }

    /**
     * @return void
     */
    protected function setUpComplexRolesAndPrivileges()
    {
        $this->acl->addRole('staff', 'guest');
        $this->acl->addRole('aide', 'guest');
        $this->acl->addRole('editor', 'staff');
        $this->acl->addRole('admin');

        // Guest may only "view" content
        $this->acl->allow('guest', null, 'view');

        // Assistant inherits "view" privilege from guest,
        // but also needs additional privileges
        $this->acl->allow('aide', null, [ 'edit', 'revise' ]);

        // Staff inherits "view" privilege from guest,
        // but also needs additional privileges
        $this->acl->allow('staff', null, [ 'edit', 'submit', 'revise' ]);

        // Editor inherits "view", "edit", "submit", and "revise" privileges from
        // staff, but also needs additional privileges
        $this->acl->allow('editor', null, [ 'publish', 'archive', 'delete' ]);

        // Administrator is allowed all privileges
        $this->acl->allow('admin');
    }

    /**
     * Create an Authorizer instance.
     *
     * @param  array $data Class dependencies.
     */
    protected function createAuthorizer(array $data = []): \Charcoal\User\Authorizer
    {
        $container = $this->container();

        $data += [
            'logger'    => $container['logger'],
            'acl'       => $this->acl,
            'resource'  => 'area',
        ];

        return new Authorizer($data);
    }

    /**
     * Create a mock Authorizer instance.
     *
     * @param  array $data Class dependencies.
     * @return MockObject&AuthorizerInterface
     */
    protected function mockAuthorizer(array $data = [])
    {
        $container = $this->container();

        $data += [
            'logger'    => $container['logger'],
            'acl'       => $this->acl,
            'resource'  => 'area',
        ];

        return $this->getMockForAbstractClass(AbstractAuthorizer::class, [ $data ]);
    }

    /**
     * Create a user instance.
     *
     * @return GenericUser
     */
    protected function createUser()
    {
        $container = $this->container();

        return $container['model/factory']->create(GenericUser::class);
    }



    // Authorizer
    // =========================================================================
    public function testSetDefaultResourceWithNull(): void
    {
        $container = $this->container();

        $this->createAuthorizer([
            'resource' => null
        ]);
        $auth = new Authorizer([
            'logger'    => $container['logger'],
            'acl'       => $this->acl,
            'resource'  => null,
        ]);

        $this->assertNull($this->callMethod($auth, 'getDefaultResource'));
    }

    public function testSetDefaultResourceWithBadValue(): void
    {
        $container = $this->container();

        $this->expectException(\InvalidArgumentException::class);
        new Authorizer([
            'logger'    => $container['logger'],
            'acl'       => $this->acl,
            'resource'  => 35,
        ]);
    }

    public function testRolesAllowedWithoutPermissions(): void
    {
        $this->assertTrue($this->auth->rolesAllowed([ 'guest' ], []));
    }

    public function testRolesAllowed(): void
    {
        $this->assertFalse($this->auth->rolesAllowed([ 'guest' ], [ 'privilege1' ]));

        $this->acl->allow('guest', 'area', 'privilege1');
        $this->assertTrue($this->auth->rolesAllowed([ 'guest' ], [ 'privilege1' ]));
        $this->assertFalse($this->auth->rolesAllowed([ 'guest' ], [ 'privilege1', 'privilege2' ]));

        $this->assertFalse($this->auth->rolesAllowed([ null ], [ 'privilege1' ]));

        $this->acl->allow(null, 'area');
        $this->assertTrue($this->auth->rolesAllowed([ null ], [ 'privilege1' ]));
    }

    public function testUserAllowedWithoutPermissions(): void
    {
        $user = $this->createUser();
        $user['roles'] = 'guest';

        $this->assertTrue($this->auth->userAllowed($user, []));
    }

    public function testUserAllowed(): void
    {
        $user = $this->createUser();
        $user['roles'] = 'guest';

        $this->assertFalse($this->auth->userAllowed($user, [ 'privilege1' ]));

        $this->acl->allow('guest', 'area', 'privilege1');
        $this->assertTrue($this->auth->userAllowed($user, [ 'privilege1' ]));
        $this->assertFalse($this->auth->userAllowed($user, [ 'privilege1', 'privilege2' ]));
    }

    public function testIsAllowedWithDefaultResource(): void
    {
        $this->acl->allow('guest', 'area', 'privilege1');
        $this->assertFalse($this->auth->isAllowed('guest', null, 'privilege1'));
        $this->assertTrue($this->auth->isAllowed('guest', 'area', 'privilege1'));
        $this->assertTrue($this->auth->isAllowed('guest', Authorizer::DEFAULT_RESOURCE, 'privilege1'));
    }



    // AbstractAuthorizer
    // =========================================================================
    public function testIsRoleGrantedCatchesAclExceptions(): void
    {
        $this->acl->allow('guest', null, [ 'privilege1', 'privilege2', 'privilege3' ]);
        $this->acl->deny('guest', null, [ 'privilege4', 'privilege5' ]);

        $this->assertNull($this->auth->isRoleGrantedAll('stranger', null, 'privilege1'));
        $this->assertNull($this->auth->isRoleGrantedAll('guest', 'nonexistent', 'privilege1'));

        $this->assertNull($this->auth->isRoleGrantedAny('stranger', null, [ 'privilege1', 'privilege4' ]));
        $this->assertNull($this->auth->isRoleGrantedAny('guest', 'nonexistent', [ 'privilege4', 'privilege5' ]));
    }

    public function testIsRoleGrantedAll(): void
    {
        $this->acl->allow('guest', null, [ 'privilege1', 'privilege2', 'privilege3' ]);
        $this->acl->deny('guest', null, 'privilege4');

        $this->assertTrue($this->auth->isRoleGrantedAll('guest', null, [ 'privilege1', 'privilege2' ]));
        $this->assertFalse($this->auth->isRoleGrantedAll('guest', null, [ 'privilege1', 'privilege4' ]));
    }

    public function testAllRolesGrantedAll(): void
    {
        $this->setUpComplexRolesAndPrivileges();

        $this->assertTrue($this->auth->allRolesGrantedAll([ 'aide', 'staff' ], null, [ 'edit', 'revise' ]));
        $this->assertFalse($this->auth->allRolesGrantedAll([ 'aide', 'staff' ], null, [ 'edit', 'submit' ]));
    }

    public function testAnyRolesGrantedAll(): void
    {
        $this->setUpComplexRolesAndPrivileges();

        $this->assertTrue($this->auth->anyRolesGrantedAll([ 'aide', 'staff' ], null, [ 'edit', 'submit' ]));
        $this->assertFalse($this->auth->anyRolesGrantedAll([ 'aide', 'staff' ], null, [ 'edit', 'publish' ]));
    }

    public function testIsRoleGrantedAny(): void
    {
        $this->acl->allow('guest', null, [ 'privilege1', 'privilege2', 'privilege3' ]);
        $this->acl->deny('guest', null, [ 'privilege4', 'privilege5' ]);

        $this->assertTrue($this->auth->isRoleGrantedAny('guest', null, [ 'privilege1', 'privilege4' ]));
        $this->assertFalse($this->auth->isRoleGrantedAny('guest', null, [ 'privilege4', 'privilege5' ]));
    }

    public function testAllRolesGrantedAny(): void
    {
        $this->setUpComplexRolesAndPrivileges();

        $this->assertTrue($this->auth->allRolesGrantedAny([ 'aide', 'staff' ], null, [ 'edit', 'submit' ]));
        $this->assertFalse($this->auth->allRolesGrantedAny([ 'aide', 'staff' ], null, [ 'publish', 'other' ]));
    }

    public function testAnyRolesGrantedAny(): void
    {
        $this->setUpComplexRolesAndPrivileges();

        $this->assertTrue($this->auth->anyRolesGrantedAny([ 'aide', 'staff' ], null, [ 'submit', 'other' ]));
        $this->assertFalse($this->auth->anyRolesGrantedAny([ 'aide', 'staff' ], null, [ 'publish', 'other' ]));
    }

    public function testIsUserGrantedWithoutPermissions(): void
    {
        $user = $this->createUser();
        $user['roles'] = 'guest';

        $this->assertFalse($this->auth->isUserGranted($user, null, null));
    }

    public function testIsUserGranted(): void
    {
        $this->setUpComplexRolesAndPrivileges();

        $user = $this->createUser();
        $user['roles'] = [ 'aide', 'staff' ];

        $this->assertTrue($this->auth->isUserGranted($user, null, [ 'edit', 'submit' ]));
        $this->assertFalse($this->auth->isUserGranted($user, null, [ 'edit', 'publish' ]));
    }



    // ACL
    // =========================================================================
    /**
     * Ensures that by default, Laminas ACL denies access to everything by all.
     */
    public function testDefaultDeny(): void
    {
        $this->assertFalse($this->auth->isAllowed());
    }

    /**
     * Ensures that by default, Laminas ACL can allow access to everything by all.
     */
    public function testDefaultAllow(): void
    {
        $this->acl->allow();
        $this->assertTrue($this->auth->isAllowed());
    }

    public function testProxyMethods(): void
    {
        $this->setUpComplexRolesAndPrivileges();

        $authorizer = $this->createAuthorizer();

        $this->assertTrue($authorizer->hasRole('guest'));
        $this->assertFalse($authorizer->hasRole('visitor'));

        $this->assertTrue($authorizer->hasResource('area'));
        $this->assertFalse($authorizer->hasResource('nonexistent'));

        $this->assertTrue($authorizer->inheritsRole('editor', 'guest'));
        $this->assertFalse($authorizer->inheritsRole('admin', 'guest'));

        $this->assertTrue($authorizer->inheritsRole('editor', 'staff', true));
        $this->assertFalse($authorizer->inheritsRole('editor', 'guest', true));

        $this->acl->addResource('city');
        $this->acl->addResource('building', 'city');
        $this->acl->addResource('room', 'building');

        $this->assertTrue($authorizer->inheritsResource('room', 'city'));
        $this->assertFalse($authorizer->inheritsResource('building', 'room'));

        $this->assertTrue($authorizer->inheritsResource('building', 'city', true));
        $this->assertFalse($authorizer->inheritsResource('room', 'city', true));
    }



    // Dependencies
    // =========================================================================
    /**
     * Set up the service container.
     */
    private function container(): \Pimple\Container
    {
        if (!$this->container instanceof \Pimple\Container) {
            $container = new Container();
            $containerProvider = new ContainerProvider();
            $containerProvider->registerBaseServices($container);
            $containerProvider->registerModelFactory($container);

            $this->container = $container;
        }

        return $this->container;
    }
}

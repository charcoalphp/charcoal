<?php

namespace Charcoal\Tests\App;

use RuntimeException;

use Slim\Interfaces\CallableResolverInterface;

use Charcoal\App\CallableResolverAwareTrait;
use Charcoal\Tests\AbstractTestCase;

class CallableResolverAwareTraitTest extends AbstractTestCase
{
    public function testResolveCallableRequiresResolver()
    {
        $obj = new CallableResolverStub();

        $this->expectException(RuntimeException::class);
        $obj->exposeResolveCallable('self:hello');
    }

    public function testResolveSelfMethod()
    {
        $resolver = $this->createMock(CallableResolverInterface::class);
        $resolver->method('resolve')->willReturnArgument(0);

        $obj = new CallableResolverStub();
        $obj->exposeSetCallableResolver($resolver);

        CallableResolverStub::clearCache();

        $callable = $obj->exposeResolveCallable('self:hello');
        $this->assertTrue(is_callable($callable));
        $this->assertSame('hi', $callable());
    }

    public function testResolveCachesResult()
    {
        $resolver = $this->createMock(CallableResolverInterface::class);
        $resolver->expects($this->once())->method('resolve')->willReturnArgument(0);

        $obj = new CallableResolverStub();
        $obj->exposeSetCallableResolver($resolver);
        CallableResolverStub::clearCache();

        $first  = $obj->exposeResolveCallable('self:hello');
        $second = $obj->exposeResolveCallable('self:hello');

        $this->assertSame($first, $second);
    }

    public function testPassesThroughCallable()
    {
        $resolver = $this->createMock(CallableResolverInterface::class);
        $resolver->method('resolve')->willReturnArgument(0);

        $obj = new CallableResolverStub();
        $obj->exposeSetCallableResolver($resolver);

        $fn = function () {
            return 1;
        };

        $this->assertSame($fn, $obj->exposeResolveCallable($fn));
    }
}

class CallableResolverStub
{
    use CallableResolverAwareTrait;

    public function hello()
    {
        return 'hi';
    }

    public function exposeSetCallableResolver(CallableResolverInterface $resolver)
    {
        return $this->setCallableResolver($resolver);
    }

    public function exposeResolveCallable($callable, $context = null)
    {
        return $this->resolveCallable($callable, $context);
    }

    public static function clearCache()
    {
        static::$resolvedCallableCache = [];
    }
}

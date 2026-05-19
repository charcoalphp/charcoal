<?php

namespace Charcoal\Tests\Admin\Template;

// From PSR-3
use Psr\Log\NullLogger;

// From 'charcoal-admin'
use Charcoal\Admin\Template\LogoutTemplate;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\ReflectionsTrait;

/**
 *
 */
class LogoutTemplateTest extends AbstractTestCase
{
    use ReflectionsTrait;

    /**
     * @var LogoutTemplate
     */
    public $obj;

    public function setUp(): void
    {
        $this->obj = new LogoutTemplate([
            'logger' => new NullLogger()
        ]);
    }

    public function testAuthRequiredIsFalse(): void
    {
        $res = $this->callMethod($this->obj, 'authRequired');
        $this->assertNotTrue($res);
    }
}

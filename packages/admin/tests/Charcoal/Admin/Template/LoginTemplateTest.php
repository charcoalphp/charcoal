<?php

namespace Charcoal\Tests\Admin\Template;

use ReflectionClass;

// From PSR-3
use Psr\Log\NullLogger;

// From 'charcoal-admin'
use Charcoal\Admin\Template\LoginTemplate;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\ReflectionsTrait;

/**
 *
 */
class LoginTemplateTest extends AbstractTestCase
{
    use ReflectionsTrait;

    /**
     * Instance of object under test
     */
    private \Charcoal\Admin\Template\LoginTemplate $obj;

    public function setUp(): void
    {
        $this->obj = new LoginTemplate([
            'logger' => new NullLogger()
        ]);
    }

    public function testAuthRequiredIsFalse(): void
    {
        $res = $this->callMethod($this->obj, 'authRequired');
        $this->assertNotTrue($res);
    }
}

<?php

namespace Charcoal\Tests\Admin\Template\Account;

use ReflectionClass;

// From PSR-3
use Psr\Log\NullLogger;

// From 'charcoal-admin'
use Charcoal\Admin\Template\Account\ResetPasswordTemplate;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\ReflectionsTrait;

/**
 *
 */
class ResetPasswordTemplateTest extends AbstractTestCase
{
    use ReflectionsTrait;

    /**
     * Instance of object under test
     * @var LoginTemplate
     */
    private \Charcoal\Admin\Template\Account\ResetPasswordTemplate $obj;

    public function setUp(): void
    {
        $this->obj = new ResetPasswordTemplate([
            'logger' => new NullLogger()
        ]);
    }

    public function testAuthRequiredIsFalse(): void
    {
        $res = $this->callMethod($this->obj, 'authRequired');
        $this->assertNotTrue($res);
    }
}

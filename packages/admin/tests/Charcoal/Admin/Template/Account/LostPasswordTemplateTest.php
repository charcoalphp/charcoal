<?php

namespace Charcoal\Tests\Admin\Template\Account;

use ReflectionClass;

// From PSR-3
use Psr\Log\NullLogger;

// From 'charcoal-admin'
use Charcoal\Admin\Template\Account\LostPasswordTemplate;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\ReflectionsTrait;

/**
 *
 */
class LostPasswordTemplateTest extends AbstractTestCase
{
    use ReflectionsTrait;

    /**
     * Instance of object under test
     * @var LoginTemplate
     */
    private \Charcoal\Admin\Template\Account\LostPasswordTemplate $obj;

    public function setUp(): void
    {
        $this->obj = new LostPasswordTemplate([
            'logger' => new NullLogger()
        ]);
    }

    public function testAuthRequiredIsFalse(): void
    {
        $res = $this->callMethod($this->obj, 'authRequired');
        $this->assertNotTrue($res);
    }
}

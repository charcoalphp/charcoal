<?php

namespace Charcoal\Tests\Admin\Template\Account;

// From PSR-3
use Psr\Log\NullLogger;
// From 'charcoal-admin'
use Charcoal\Admin\Template\Account\LostPasswordTemplate;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\ReflectionsTrait;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LostPasswordTemplate::class)]
class LostPasswordTemplateTest extends AbstractTestCase
{
    use ReflectionsTrait;

    /**
     * Instance of object under test
     * @var LoginTemplate
     */
    private $obj;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->obj = new LostPasswordTemplate([
            'logger' => new NullLogger()
        ]);
    }

    /**
     * @return void
     */
    public function testAuthRequiredIsFalse()
    {
        $res = $this->callMethod($this->obj, 'authRequired');
        $this->assertNotTrue($res);
    }
}

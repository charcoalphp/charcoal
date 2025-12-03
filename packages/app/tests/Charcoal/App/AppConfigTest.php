<?php

namespace Charcoal\Tests\App;

// From 'charcoal-app'
use Charcoal\App\AppConfig;
use Charcoal\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AppConfig::class)]
class AppConfigTest extends AbstractTestCase
{
    public $obj;

    public function testConstructor()
    {
        $obj = new AppConfig();
        $this->assertInstanceOf(AppConfig::class, $obj);
    }
}

<?php

declare(strict_types=1);

namespace Charcoal\Tests\App;

// From 'charcoal-app'
use Charcoal\App\AppConfig;
use Charcoal\Tests\AbstractTestCase;

class AppConfigTest extends AbstractTestCase
{
    public $obj;

    public function testConstructor(): void
    {
        $obj = new AppConfig();
        $this->assertInstanceOf(AppConfig::class, $obj);
    }
}

<?php

namespace Charcoal\Tests\Admin\Widget;

// From PSR-3
use Psr\Log\NullLogger;

// From 'charcoal-admin'
use Charcoal\Admin\Widget\SearchWidget;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class SearchWidgetTest extends AbstractTestCase
{
    public $obj;
    public function setUp(): void
    {
        $logger = new NullLogger();
        $this->obj = new SearchWidget([
            'logger' => $logger
        ]);
    }

    public function testConstructor(): void
    {
        $this->assertInstanceOf(SearchWidget::class, $this->obj);
    }
}

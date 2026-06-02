<?php

namespace Charcoal\Tests\Admin\Widget;

// From PSR-3
use Psr\Log\NullLogger;

// From 'charcoal-admin'
use Charcoal\Admin\Widget\FormSidebarWidget;
use Charcoal\Admin\Widget\FormWidget;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class FormWidgetTest extends AbstractTestCase
{
    /**
     * Object under test
     */
    private \Charcoal\Admin\Widget\FormWidget $obj;

    public function setUp(): void
    {
        $logger = new NullLogger();
        $this->obj = new FormWidget([
            'logger' => $logger,
        ]);
    }

    public function testConstructor(): void
    {
        $this->assertInstanceOf(FormWidget::class, $this->obj);
    }
}

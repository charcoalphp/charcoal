<?php

declare(strict_types=1);

namespace Charcoal\Email\Tests\Services;


use PHPUnit\Framework\TestCase;

use Charcoal\Email\Email;
use Charcoal\Email\Services\Tracker;

/**
 *
 */
class TrackerTest extends TestCase
{
    private \Charcoal\Email\Services\Tracker $obj;

    /**
     * @var Email
     */
    private mixed $email;

    /**
     *
     */
    public function setUp(): void
    {
        /** GLOBALS['container'] is defined in bootstrap file */
        $container = $GLOBALS['container'];
        $this->obj = new Tracker('', $container['model/factory']);
        $this->email = $container['email'];
    }

    /**
     *
     */
    public function testAddOpenTrackingImageWithBody(): void
    {
        $html = '<html><body class="foo"><p>Hello</p></body></html>';
        $this->email->setMsgHtml($html);
        $id = uniqid();
        $this->obj->addOpenTrackingImage($this->email, $id);
        $this->assertEquals('<html><body class="foo"><img src="email/v1/open/'.$id.'.png" alt="" /><p>Hello</p></body></html>', $this->email->msgHtml());
    }


    /**
     *
     */
    public function testAddOpenTrackingImageWithoutBody(): void
    {
        $html = '<div class="foo"><p>Hello</p></div>';
        $this->email->setMsgHtml($html);
        $id = uniqid();
        $this->obj->addOpenTrackingImage($this->email, $id);
        $this->assertEquals('<div class="foo"><p>Hello</p></div><img src="email/v1/open/'.$id.'.png" alt="" />', $this->email->msgHtml());
    }

    /**
     *
     */
    public function testReplaceLinksWithTracker(): void
    {
        $html = '<div class="foo"><a href="https://example.com/foo">Foo</a></div>';
        $id = uniqid();
        $this->email->setMsgHtml($html);
        $this->obj->replaceLinksWithTracker($this->email, $id);
        $this->assertMatchesRegularExpression('#<div class="foo"><a href="email/v1/link/[0-9a-z]{13}">Foo</a></div>#', $this->email->msgHtml());
    }
}

<?php

namespace Charcoal\Tests\App\Email;

use InvalidArgumentException;

use Charcoal\Email\Email;
use Charcoal\Tests\AbstractTestCase;

/**
 * Test the AbstractEmail methods, through concrete `Email` class.
 */
class EmailTest extends AbstractTestCase
{
    /**
     * @var Email
     */
    public $obj;

    public function setup()
    {
        /** GLOBALS['container'] is defined in bootstrap file */
        $container = $GLOBALS['container'];
        $this->obj = new Email([
            'logger'    => $container['logger'],
            'config'    => $container['email/config'],
            'view'      => $container['email/view'],
            'template_factory' => $container['template/factory'],
            'queue_item_factory' => $container['model/factory'],
            'log_factory' => $container['model/factory']
        ]);
    }

    public function testSetData()
    {
        $obj = $this->obj;
        $ret = $obj->setData([
            'campaign'    => 'foo',
            'to'          => 'test@example.com',
            'cc'          => 'cc@example.com',
            'bcc'         => 'bcc@example.com',
            'from'        => 'from@example.com',
            'reply_to'    => 'reply@example.com',
            'subject'     => 'bar',
            'msg_html'    => 'foo',
            'msg_txt'     => 'baz',
            'attachments' => [
                'foo'
            ],
            'log_enabled'   => true,
            'track_open_enabled' => true,
            'track_links_enabled' => true
        ]);
        $this->assertSame($ret, $obj);

        $this->assertEquals('foo', $obj->campaign());
        $this->assertEquals(['test@example.com'], $obj->to());
        $this->assertEquals(['cc@example.com'], $obj->cc());
        $this->assertEquals(['bcc@example.com'], $obj->bcc());
        $this->assertEquals('from@example.com', $obj->from());
        $this->assertEquals('reply@example.com', $obj->replyTo());
        $this->assertEquals('bar', $obj->subject());
        $this->assertEquals('foo', $obj->msgHtml());
        $this->assertEquals('baz', $obj->msgTxt());
        $this->assertEquals(['foo'], $obj->attachments());
        $this->assertEquals(true, $obj->logEnabled());
        $this->assertEquals(true, $obj->trackOpenEnabled());
        $this->assertEquals(true, $obj->trackLinksEnabled());
    }

    public function testSetCampaign()
    {
        $obj = $this->obj;
        $ret = $obj->setCampaign('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->campaign());
    }

    public function testGenerateCampaign()
    {
        $obj = $this->obj;
        $ret = $obj->campaign();
        $this->assertNotEmpty($ret);
    }

    /**
     * Asserts that the `setTo()` method:
     * - Sets the "to" recipient when using an array of string
     * - Sets the "to" recipient properly when using an email structure (array)
     * - Sets the "to" recipient to an array when setting a single email string
     * - Resets the "to" value before setting it, at every call.
     * - Throws an exception if the to argument is not a string.
     */
    public function testSetTo()
    {
        $obj = $this->obj;

        $ret = $obj->setTo([
            'test@example.com',
            'test2@example.com']);
        $this->assertSame($ret, $obj);
        $this->assertEquals([
            'test@example.com',
            'test2@example.com'
        ], $obj->to());

        $obj->setTo([
            [
                'name'  => 'Test',
                'email' => 'test@example.com'
            ]
        ]);
        $this->assertEquals(['"Test" <test@example.com>'], $obj->to());

        $obj->setTo('test@example.com');
        $this->assertEquals(['test@example.com'], $obj->to());

        $this->expectException('\InvalidArgumentException');
        $obj->setTo(false);

        $this->expectException('\InvalidArgumentException');
        $obj->setTo(false);
    }

    public function testAddTo()
    {
        $obj = $this->obj;
        $ret = $obj->addTo('test@example.com');
        $this->assertSame($ret, $obj);
        $this->assertEquals(['test@example.com'], $obj->to());

        $obj->addTo(['name'=>'Test','email'=>'test@example.com']);
        $this->assertEquals(['test@example.com', '"Test" <test@example.com>'], $obj->to());

        $this->expectException('\InvalidArgumentException');
        $obj->addTo(false);
    }

    public function testSetCc()
    {
        $obj = $this->obj;

        $ret = $obj->setCc(['test@example.com']);
        $this->assertSame($ret, $obj);
        $this->assertEquals(['test@example.com'], $obj->cc());

        $obj->setCc([
            [
                'name'  => 'Test',
                'email' => 'test@example.com'
            ]
        ]);
        $this->assertEquals(['"Test" <test@example.com>'], $obj->cc());

        $obj->setCc('test@example.com');
        $this->assertEquals(['test@example.com'], $obj->cc());

        $this->expectException('\InvalidArgumentException');
        $obj->SetCc(false);
    }

    public function testAddCc()
    {
        $obj = $this->obj;
        $ret = $obj->addCc('test@example.com');
        $this->assertSame($ret, $obj);
        $this->assertEquals(['test@example.com'], $obj->cc());

        $obj->addCc(['name'=>'Test','email'=>'test@example.com']);
        $this->assertEquals(['test@example.com', '"Test" <test@example.com>'], $obj->cc());

        $this->expectException('\InvalidArgumentException');
        $obj->addCc(false);
    }

    public function testSetBcc()
    {
        $obj = $this->obj;

        $ret = $obj->setBcc(['test@example.com']);
        $this->assertSame($ret, $obj);
        $this->assertEquals(['test@example.com'], $obj->bcc());

        $obj->setBcc([
            [
                'name'  => 'Test',
                'email' => 'test@example.com'
            ]
        ]);
        $this->assertEquals(['"Test" <test@example.com>'], $obj->bcc());

        $obj->setBcc('test@example.com');
        $this->assertEquals(['test@example.com'], $obj->bcc());

        $this->expectException('\InvalidArgumentException');
        $obj->setBcc(false);
    }

    public function testAddBcc()
    {
        $obj = $this->obj;
        $ret = $obj->addBcc('test@example.com');
        $this->assertSame($ret, $obj);
        $this->assertEquals(['test@example.com'], $obj->bcc());

        $obj->addBcc(['name'=>'Test','email'=>'test@example.com']);
        $this->assertEquals(['test@example.com', '"Test" <test@example.com>'], $obj->bcc());

        $this->expectException('\InvalidArgumentException');
        $obj->addBcc(false);
    }

    public function testSetFrom()
    {
        $obj = $this->obj;
        //$config = $obj->config()->setDefaultFrom('default@example.com');
        //$this->assertEquals('default@example.com', $obj->from());

        $ret = $obj->setFrom('test@example.com');
        $this->assertSame($ret, $obj);
        $this->assertEquals('test@example.com', $obj->from());

        $obj->setFrom([
            'name'  => 'Test',
            'email' => 'test@example.com'
        ]);
        $this->assertEquals('"Test" <test@example.com>', $obj->from());

        $this->expectException('\InvalidArgumentException');
        $obj->setFrom(false);
    }

    public function testSetReplyTo()
    {
        $obj = $this->obj;
        //$config = $obj->config()->setDefaultReplyTo('default@example.com');
        //$this->assertEquals('default@example.com', $obj->replyTo());

        $ret = $obj->setReplyTo('test@example.com');
        $this->assertSame($ret, $obj);
        $this->assertEquals('test@example.com', $obj->replyTo());

        $obj->setReplyTo([
            'name'  => 'Test',
            'email' => 'test@example.com'
        ]);
        $this->assertEquals('"Test" <test@example.com>', $obj->replyTo());

        $this->expectException('\InvalidArgumentException');
        $obj->setReplyTo(false);
    }

    public function testSetSubject()
    {
        $obj = $this->obj;
        $ret = $obj->setSubject('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->subject());
    }

    public function testSetMsgHtml()
    {
        $obj = $this->obj;
        $ret = $obj->setMsgHtml('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->msgHtml());
    }

    public function testSetMsgTxt()
    {
        $obj = $this->obj;
        $ret = $obj->setMsgTxt('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->msgTxt());
    }

    public function testConvertHtml()
    {
        $obj = $this->obj;
        $html = file_get_contents(__DIR__.'/../../data/example.html');
        $txt = file_get_contents(__DIR__.'/../../data/example.txt');

        $obj->setMsgHtml($html);

        // Next assert add a "\n" because the txt file ends with a newline,
        $this->assertEquals($txt, $obj->msgTxt());
    }

    public function testSetAttachments()
    {
        $obj = $this->obj;
        $ret = $obj->setAttachments(['foo']);
        $this->assertSame($ret, $obj);
        $this->assertEquals(['foo'], $obj->attachments());
    }

    public function testSetLogEnabled()
    {
        $obj = $this->obj;
        // $this->config()->setDefaultLogEnabled(false);
        // $this->assertNotTrue($obj->logEnabled());

        $ret = $obj->setLogEnabled(true);
        $this->assertSame($ret, $obj);
        $this->assertTrue($obj->logEnabled());

        $obj->setLogEnabled(false);
        $this->assertNotTrue($obj->logEnabled());
    }

    public function testSetTrackOpenEnabled()
    {
        $obj = $this->obj;
        // $this->config()->setDefaultTrackOpenEnabled(false);
        // $this->assertNotTrue($obj->trackOpenEnabled());

        $ret = $obj->setTrackOpenEnabled(true);
        $this->assertSame($ret, $obj);
        $this->assertTrue($obj->trackOpenEnabled());

        $obj->setTrackOpenEnabled(false);
        $this->assertNotTrue($obj->trackOpenEnabled());
    }

    public function testSetTrackLinksEnabled()
    {
        $obj = $this->obj;
        // $this->config()->setDefaultTrackLinksEnabled(false);
        // $this->assertNotTrue($obj->trackLinksEnabled());

        $ret = $obj->setTrackLinksEnabled(true);
        $this->assertSame($ret, $obj);
        $this->assertTrue($obj->trackLinksEnabled());

        $obj->setTrackLinksEnabled(false);
        $this->assertNotTrue($obj->trackLinksEnabled());
    }
}

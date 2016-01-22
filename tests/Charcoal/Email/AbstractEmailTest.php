<?php

namespace Charcoal\Tests\App\Email;

use \Charcoal\Email\Email;

/**
 * Test the AbstractEmail methods, through concrete `Email` class.
 */
class EmailTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setup()
    {
        // GLOBALS['app'] is defined in bootstrap file
        $this->obj = new Email([
            'app'    => $GLOBALS['app'],
            'logger' => $GLOBALS['app']->logger
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
            'log'   => true,
            'track' => true
        ]);
        $this->assertSame($ret, $obj);

        $this->assertEquals('foo', $obj->campaign());
        // $this->assertEquals(['test@example.com'], $obj->to());
        // $this->assertEquals(['cc@example.com'], $obj->cc());
        // $this->assertEquals(['bcc@example.com'], $obj->bcc());
        // $this->assertEquals('from@example.com', $obj->from());
        // $this->assertEquals('reply@example.com', $obj->replyTo());
        $this->assertEquals('bar', $obj->subject());
        $this->assertEquals('foo', $obj->msgHtml());
        $this->assertEquals('baz', $obj->msgTxt());
        $this->assertEquals(['foo'], $obj->attachments());
        $this->assertEquals(true, $obj->log());
        $this->assertEquals(true, $obj->track());
    }

    public function testSetCampaign()
    {
        $obj = $this->obj;
        $ret = $obj->setCampaign('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->campaign());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setCampaign([1, 2, 3]);
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
    // public function testSetTo()
    // {
    //     $obj = $this->obj;

    //     $ret = $obj->setTo(['test@example.com', 'test2@example.com']);
    //     $this->assertSame($ret, $obj);
    //     $this->assertEquals(['test@example.com', 'test2@example.com'], $obj->to());

    //     $obj->setTo([
    //         [
    //             'name'  => 'Test',
    //             'email' => 'test@example.com'
    //         ]
    //     ]);
    //     $this->assertEquals(['"Test" <test@example.com>'], $obj->to());

    //     $obj->setTo('test@example.com');
    //     $this->assertEquals(['test@example.com'], $obj->to());

    //     $this->setExpectedException('\InvalidArgumentException');
    //     $obj->setTo(false);
    // }

    // public function testAddTo()
    // {
    //     $obj = $this->obj;
    //     $ret = $obj->addTo('test@example.com');
    //     $this->assertSame($ret, $obj);
    //     $this->assertEquals(['test@example.com'], $obj->to());

    //     $obj->addTo(['name'=>'Test','email'=>'test@example.com']);
    //     $this->assertEquals(['test@example.com', '"Test" <test@example.com>'], $obj->to());

    //     $this->setExpectedException('\InvalidArgumentException');
    //     $obj->addTo(false);
    // }

    // public function testSetCc()
    // {
    //     $obj = $this->obj;

    //     $ret = $obj->setCc(['test@example.com']);
    //     $this->assertSame($ret, $obj);
    //     $this->assertEquals(['test@example.com'], $obj->cc());

    //     $obj->setCc([
    //         [
    //             'name'  => 'Test',
    //             'email' => 'test@example.com'
    //         ]
    //     ]);
    //     $this->assertEquals(['"Test" <test@example.com>'], $obj->cc());

    //     $obj->setCc('test@example.com');
    //     $this->assertEquals(['test@example.com'], $obj->cc());

    //     $this->setExpectedException('\InvalidArgumentException');
    //     $obj->setCc(false);
    // }

    // public function testAddCc()
    // {
    //     $obj = $this->obj;
    //     $ret = $obj->addCc('test@example.com');
    //     $this->assertSame($ret, $obj);
    //     $this->assertEquals(['test@example.com'], $obj->cc());

    //     $obj->addCc(['name'=>'Test','email'=>'test@example.com']);
    //     $this->assertEquals(['test@example.com', '"Test" <test@example.com>'], $obj->cc());

    //     $this->setExpectedException('\InvalidArgumentException');
    //     $obj->addCc(false);
    // }

    // public function testSetBcc()
    // {
    //     $obj = $this->obj;

    //     $ret = $obj->setBcc(['test@example.com']);
    //     $this->assertSame($ret, $obj);
    //     $this->assertEquals(['test@example.com'], $obj->bcc());

    //     $obj->setBcc([
    //         [
    //             'name'  => 'Test',
    //             'email' => 'test@example.com'
    //         ]
    //     ]);
    //     $this->assertEquals(['"Test" <test@example.com>'], $obj->bcc());

    //     $obj->setBcc('test@example.com');
    //     $this->assertEquals(['test@example.com'], $obj->bcc());

    //     $this->setExpectedException('\InvalidArgumentException');
    //     $obj->setBcc(false);
    // }

    // public function testAddBcc()
    // {
    //     $obj = $this->obj;
    //     $ret = $obj->addBcc('test@example.com');
    //     $this->assertSame($ret, $obj);
    //     $this->assertEquals(['test@example.com'], $obj->bcc());

    //     $obj->addBcc(['name'=>'Test','email'=>'test@example.com']);
    //     $this->assertEquals(['test@example.com', '"Test" <test@example.com>'], $obj->bcc());

    //     $this->setExpectedException('\InvalidArgumentException');
    //     $obj->addBcc(false);
    // }

    // public function testSetFrom()
    // {
    //     $obj = $this->obj;
    //     //$config = $obj->config()->setDefaultFrom('default@example.com');
    //     //$this->assertEquals('default@example.com', $obj->from());

    //     $ret = $obj->setFrom('test@example.com');
    //     $this->assertSame($ret, $obj);
    //     $this->assertEquals('test@example.com', $obj->from());

    //     $obj->setFrom([
    //         'name'  => 'Test',
    //         'email' => 'test@example.com'
    //     ]);
    //     $this->assertEquals('"Test" <test@example.com>', $obj->from());

    //     $this->setExpectedException('\InvalidArgumentException');
    //     $obj->setFrom(false);
    // }

    // public function testSetReplyTo()
    // {
    //     $obj = $this->obj;
    //     //$config = $obj->config()->setDefaultReplyTo('default@example.com');
    //     //$this->assertEquals('default@example.com', $obj->replyTo());

    //     $ret = $obj->setReplyTo('test@example.com');
    //     $this->assertSame($ret, $obj);
    //     $this->assertEquals('test@example.com', $obj->replyTo());

    //     $obj->setReplyTo([
    //         'name'  => 'Test',
    //         'email' => 'test@example.com'
    //     ]);
    //     $this->assertEquals('"Test" <test@example.com>', $obj->replyTo());

    //     $this->setExpectedException('\InvalidArgumentException');
    //     $obj->setReplyTo(false);
    // }

    public function testSetSubject()
    {
        $obj = $this->obj;
        $ret = $obj->setSubject('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->subject());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setSubject(null);
    }

    public function testSetMsgHtml()
    {
        $obj = $this->obj;
        $ret = $obj->setMsgHtml('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->msgHtml());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setMsgHtml(null);
    }

    public function testSetMsgText()
    {
        $obj = $this->obj;
        $ret = $obj->setMsgTxt('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->msgTxt());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setMsgTxt(null);
    }

    public function testSetAttachments()
    {
        $obj = $this->obj;
        $ret = $obj->setAttachments(['foo']);
        $this->assertSame($ret, $obj);
        $this->assertEquals(['foo'], $obj->attachments());
    }

    public function testSetLog()
    {
        $obj = $this->obj;
        // $this->config()->setDefaultLog(false);
        // $this->assertNotTrue($obj->log());

        $ret = $obj->setLog(true);
        $this->assertSame($ret, $obj);
        $this->assertTrue($obj->log());

        $obj->setLog(false);
        $this->assertNotTrue($obj->log());

    }

    public function testSetTrack()
    {
        $obj = $this->obj;
        // $this->config()->setDefaultTrack(false);
        // $this->assertNotTrue($obj->track());

        $ret = $obj->setTrack(true);
        $this->assertSame($ret, $obj);
        $this->assertTrue($obj->track());

        $obj->setTrack(false);
        $this->assertNotTrue($obj->track());

    }
}

<?php

namespace Charcoal\Tests\View;

use \Charcoal\View\AbstractView as AbstractView;
use \Charcoal\View\ViewTemplateLoader as ViewTemplateLoader;


class ViewTemplateLoaderTest extends \PHPUnit_Framework_TestCase
{

    public function testSetEngine()
    {
        $obj = new ViewTemplateLoader();
        $this->assertEquals(AbstractView::DEFAULT_ENGINE, $obj->engine());

        $ret = $obj->set_engine('php');
        $this->assertSame($ret, $obj);
        $this->assertEquals('php', $obj->engine());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_engine(false);
    }
    
}

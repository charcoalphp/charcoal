<?php

namespace Charcoal\View;

// Local namespace dependencies
use \Charcoal\View\ViewEngineInterface as ViewEngineInterface;

/**
* Full implementation, as abstract class, of ViewEngineInterface
*/
abstract class AbstractViewEngine implements ViewEngineInterface
{
    /**
    * Holds the list of JS requirements for View
    * @var array $js_requirements
    */
    static private $js_requirements = [];

    /**
    * Holds custom JS scripts to append to View
    * @var string $js
    */
    static private $js = '';

    /**
    * @var array $css_requirements
    */
    static private $css_requirements = [];

    /**
    * @var array $css;
    */
    static private $css = '';

    /**
    * @return string
    */
    abstract public function type();

    /**
    * @param string $filename
    * @return boolean Success / Failure
    */
    abstract public function process_file($filename);

    /**
    * @return string
    */
    abstract public function filename_extension();

    /**
    * @param string $str
    * @return string
    */
    public function _t($str)
    {
        return $str;
    }

    /**
    * @return string
    */
    public function js_requirements()
    {
        $req = array_unique(self::$js_requirements);
        $ret = '';
        foreach ($req as $r) {
            $ret .= $r;
        }
        self::$js_requirements = [];
        return $ret;
    }

    /**
    * @param string $js_requirement
    * @return void
    */
    public function add_js_requirement($js_requirement)
    {
        self::$js_requirements[] = $js_requirement;
    }

    /**
    * @return string
    */
    public function js()
    {
        $js = self::$js;
        self::$js = '';
        return $js;
    }

    /**
    * @param string $js
    * @return void
    */
    public function add_js($js)
    {
        self::$js .= $js;
    }

    /**
    * @return array
    */
    public function css_requirements()
    {
        $req = array_unique(self::$css_requirements);
        $ret = '';
        foreach ($req as $r) {
            $ret .= $r;
        }
        self::$css_requirements = [];
        return $ret;
    }

    /**
    * @param string $css_requirement
    * @return void
    */
    public function add_css_requirement($css_requirement)
    {
        self::$css_requirements[] = $css_requirement;
    }

    /**
    * @return string
    */
    public function css()
    {
        $css = self::$css;
        self::$css = '';
        return $css;
    }

    /**
    * @param string $css
    * @return void
    */
    public function add_css($css)
    {
        self::$css .= $css;
    }
}

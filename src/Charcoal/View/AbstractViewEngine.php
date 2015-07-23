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
    * @var array $_js_requirements
    */
    static private $_js_requirements = [];

    /**
    * Holds custom JS scripts to append to View
    * @var string $_js
    */
    static private $_js = '';

    /**
    * @var array $_css_requirements
    */
    static private $_css_requirements = [];

    /**
    * @var array $_css;
    */
    static private $_css = '';

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
        $req = array_unique(self::$_js_requirements);
        $ret = '';
        foreach ($req as $r) {
            $ret .= $r;
        }
        self::$_js_requirements = [];
        return $ret;
    }

    /**
    * @param string $js_requirement
    * @return void
    */
    public function add_js_requirement($js_requirement)
    {
        self::$_js_requirements[] = $js_requirement;
    }

    /**
    * @return string
    */
    public function js()
    {
        $js = self::$_js;
        self::$_js = '';
        return $js;
    }

    /**
    * @param string $js
    * @return void
    */
    public function add_js($js)
    {
        self::$_js .= $js;
    }

    /**
    * @return array
    */
    public function css_requirements()
    {
        $req = array_unique(self::$_css_requirements);
        $ret = '';
        foreach ($req as $r) {
            $ret .= $r;
        }
        self::$_css_requirements = [];
        return $ret;
    }

    /**
    * @param string $css_requirement
    * @return void
    */
    public function add_css_requirement($css_requirement)
    {
        self::$_css_requirements[] = $css_requirement;
    }

    /**
    * @return string
    */
    public function css()
    {
        $css = self::$_css;
        self::$_css = '';
        return $css;
    }

    /**
    * @param string $css
    * @return void
    */
    public function add_css($css)
    {
        self::$_css .= $css;
    }
}

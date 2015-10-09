<?php

namespace Charcoal\View\Mustache;

use \ArrayIterator;
use \IteratorAggregate;

use \Mustache_LambdaHelper;

/**
* Default mustache render helper. Helpers are global functions available to all the templates.
*/
class GenericHelper implements IteratorAggregate
{
    /**
    * @var string $js
    */
    static private $js = '';

    /**
    * @var array $js_requirements
    */
    static private $js_requirements = [];
   
    /**
    * @var array $css_requirements
    */
    static private $css_requirements = [];
    /**
    * @var string $css;
    */
    static private $css = '';

    /**
    * IteratorAggregate > getIterator
    * Also ensure this class is `Traversable`
    *
    * @return ArrayIterator
    */
    public function getIterator()
    {
        return new ArrayIterator([
            'add_js' => function($js, Mustache_LambdaHelper $helper) {
                return $this->add_js($js, $helper);
            },
            'js' => function() {
                return $this->js();
            },
            'add_js_requirement' => function($js_requirement) {
                return $this->add_js_requirement($js_requirement);
            },
            'add_css' => function($css, Mustache_LambdaHelper $helper) {
                return $this->add_css($css, $helper);
            },
            'css' => function() {
                return $this->css();
            },
            'add_css_requirement' => function($css_requirement) {
                return $this->add_css_requirement($css_requirement);
            }
        ]);
    }

    /**
    * @param string $js
    * @param Mustache_LamdaHelper $helper
    * @return void
    */
    public function add_js($js, Mustache_LambdaHelper $helper = null)
    {
        if ($helper !== null) {
            $js = $helper->render($js);
        }
        self::$js .= $js;
    }

    /**
    * Get the saved js content and purge
    *
    * @return string
    */
    public function js()
    {
        $js = self::$js;
        self::$js = '';
        return $js;
    }

    /**
    * @param string $js_requirement
    * @return void
    */
    public function add_js_requirement($js_requirement)
    {
        if (!in_array($js_requirement, self::$js_requirements)) {
            self::$js_requirements[] = $js_requirement;
        }
    }

    /**
    * @param string $css
    * @return void
    */
    public function add_css($css, Mustache_LambdaHelper $helper = null)
    {
        if ($helper !== null) {
            $css = $helper->render($css);
        }
        self::$css .= $css;
    }

    /**
    * Get the saved css content and purge
    *
    * @return string
    */
    public function css()
    {
        $css = self::$css;
        self::$css = '';
        return $css;
    }

    /**
    * @param string $css_requirement
    * @return string
    */
    public function add_css_requirement($css_requirement)
    {
        if (!in_array($css_requirement, self::$css_requirements)) {
            self::$css_requirements[] = $css_requirement;
        }
    }
}

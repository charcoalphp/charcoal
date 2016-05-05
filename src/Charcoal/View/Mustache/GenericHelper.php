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
     * @var array $jsRequirements
     */
    static private $jsRequirements = [];

    /**
     * @var array $cssRequirements
     */
    static private $cssRequirements = [];
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
            '_t' => function($txt) {
                return $txt;
            },
            'addJs' => function($js, Mustache_LambdaHelper $helper) {
                return $this->addJs($js, $helper);
            },
            'js' => function() {
                return $this->js();
            },
            'addJsRequirement' => function($jsRequirement) {
                return $this->addJsRequirement($jsRequirement);
            },
            'jsRequirements' => function() {
                return $this->jsRequirements();
            },
            'addCss' => function($css, Mustache_LambdaHelper $helper) {
                return $this->addCss($css, $helper);
            },
            'css' => function() {
                return $this->css();
            },
            'addCssRequirement' => function($cssRequirement) {
                return $this->addCssRequirement($cssRequirement);
            },
            'cssRequirements' => function() {
                return $this->cssRequirements();
            }
        ]);
    }

    /**
     * @param string                $js     The javascript to add.
     * @param Mustache_LambdaHelper $helper Lambda helper.
     * @return void
     */
    public function addJs($js, Mustache_LambdaHelper $helper = null)
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
     * @param string $jsRequirement The js requirements.
     * @return void
     */
    public function addJsRequirement($jsRequirement)
    {
        if (!in_array($jsRequirement, self::$jsRequirements)) {
            self::$jsRequirements[] = $jsRequirement;
        }
    }

    /**
     * Ouput and reset JS requirements
     *
     * @return string
     */
    public function jsRequirements()
    {
        $req = implode("\n", self::$jsRequirements);
        self::$jsRequirements = [];
        return $req;
    }

    /**
     * @param string                $css    The CSS string to add.
     * @param Mustache_LambdaHelper $helper Lambda helper.
     * @return void
     */
    public function addCss($css, Mustache_LambdaHelper $helper = null)
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
     * @param string $cssRequirement The CSS requirements.
     * @return void
     */
    public function addCssRequirement($cssRequirement)
    {
        if (!in_array($cssRequirement, self::$cssRequirements)) {
            self::$cssRequirements[] = $cssRequirement;
        }
    }

    /**
     * Ouput and reset CSS requirements
     *
     * @return string
     */
    public function cssRequirements()
    {
        $req = implode("\n", self::$cssRequirements);
        self::$cssRequirements = [];
        return $req;
    }
}

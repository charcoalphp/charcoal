<?php

namespace Charcoal\View\Mustache;

use \ArrayIterator;
use \IteratorAggregate;

use \Mustache_LambdaHelper as LambdaHelper;

/**
 * Default Mustache helpers for rendering.
 *
 * > Helpers can be global variables or objects, closures (e.g. for higher order sections),
 * > or any other valid Mustache context value.
 * — {@link https://github.com/bobthecow/mustache.php/wiki#helpers}
 */
class GenericHelper implements IteratorAggregate
{
    /**
     * A string concatenation of inline `<script>` elements.
     *
     * @var string $js
     */
    private static $js = '';

    /**
     * An array of `<script>` elements referencing external scripts.
     *
     * @var array $jsRequirements
     */
    private static $jsRequirements = [];

    /**
     * An array of `<link>` elements referencing external style sheets.
     *
     * @var array $cssRequirements
     */
    private static $cssRequirements = [];

    /**
     * A string concatenation of inline `<style>` elements.
     *
     * @var string $css;
     */
    private static $css = '';

    /**
     * Retrieve a traversable iterator.
     *
     * @see    IteratorAggregate::getIterator()
     * @return Traversable
     */
    public function getIterator()
    {
        return new ArrayIterator([
            '_t' => function($txt) {
                return $txt;
            },
            'addJs' => function($js, LambdaHelper $helper) {
                return $this->addJs($js, $helper);
            },
            'js' => function() {
                return $this->js();
            },
            'addJsRequirement' => function($js) {
                return $this->addJsRequirement($js);
            },
            'jsRequirements' => function() {
                return $this->jsRequirements();
            },
            'addCss' => function($css, LambdaHelper $helper) {
                return $this->addCss($css, $helper);
            },
            'css' => function() {
                return $this->css();
            },
            'addCssRequirement' => function($css) {
                return $this->addCssRequirement($css);
            },
            'cssRequirements' => function() {
                return $this->cssRequirements();
            }
        ]);
    }

    /**
     * Enqueue (concatenate) inline JavaScript content.
     *
     * Must include `<script>` surrounding element.
     *
     * @param string       $js     The JavaScript to add.
     * @param LambdaHelper $helper For rendering strings in the current context.
     * @return void
     */
    public function addJs($js, LambdaHelper $helper = null)
    {
        if ($helper !== null) {
            $js = $helper->render($js);
        }
        self::$js .= $js;
    }

    /**
     * Get the saved inline JavaScript content and purge the store.
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
     * Enqueue an external JavaScript file.
     *
     * Must include `<script>` surrounding element.
     *
     * @param string $js The JavaScript requirements.
     * @return void
     */
    public function addJsRequirement($js)
    {
        if (!in_array($js, self::$jsRequirements)) {
            self::$jsRequirements[] = $js;
        }
    }

    /**
     * Get the JavaScript requirements and purge the store.
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
     * Enqueue (concatenate) inline CSS content.
     *
     * Must include `<style>` surrounding element.
     *
     * @param string       $css    The CSS string to add.
     * @param LambdaHelper $helper For rendering strings in the current context.
     * @return void
     */
    public function addCss($css, LambdaHelper $helper = null)
    {
        if ($helper !== null) {
            $css = $helper->render($css);
        }
        self::$css .= $css;
    }

    /**
     * Get the saved inline CSS content and purge the store.
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
     * Enqueue an external CSS file.
     *
     * Must include `<link>` surrounding element.
     *
     * @param string $css The CSS requirements.
     * @return void
     */
    public function addCssRequirement($css)
    {
        if (!in_array($css, self::$cssRequirements)) {
            self::$cssRequirements[] = $css;
        }
    }

    /**
     * Get the CSS requirements and purge the store.
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

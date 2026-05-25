<?php

declare(strict_types=1);

namespace Charcoal\View\Mustache;

// From Mustache
use Mustache\LambdaHelper as LambdaHelper;

/**
 * Mustache helpers for rendering CSS and JavaScript.
 */
class AssetsHelpers implements HelpersInterface
{
    /**
     * A string concatenation of inline `<script>` elements.
     */
    private static string $js = '';

    /**
     * An array of `<script>` elements referencing external scripts.
     *
     * @var array
     */
    private static $jsRequirements = [];

    /**
     * A string concatenation of inline `<style>` elements.
     */
    private static string $css = '';

    /**
     * An array of `<link>` elements referencing external style sheets.
     *
     * @var array
     */
    private static $cssRequirements = [];

    /**
     * Retrieve the collection of helpers.
     */
    public function toArray(): array
    {
        return [
            'purgeJs' => function (): void {
                $this->purgeJs();
            },
            'addJs' => function (string $js, LambdaHelper $helper): void {
                $this->addJs($js, $helper);
            },
            'js' => $this->js(...),
            'addJsRequirement' => function (string $js, LambdaHelper $helper): void {
                $this->addJsRequirement($js, $helper);
            },
            'jsRequirements' => $this->jsRequirements(...),
            'addCss' => function (string $css, LambdaHelper $helper): void {
                $this->addCss($css, $helper);
            },
            'purgeCss' => function (): void {
                $this->purgeCss();
            },
            'css' => $this->css(...),
            'addCssRequirement' => function (string $css, LambdaHelper $helper): void {
                $this->addCssRequirement($css, $helper);
            },
            'cssRequirements' => $this->cssRequirements(...),
            'purgeAssets' => function (): void {
                $this->purgeAssets();
            },
        ];
    }

    /**
     * Empty the JS assets queue.
     */
    public function purgeJs(): void
    {
        self::$js = '';
        self::$jsRequirements = [];
    }

    /**
     * Enqueue (concatenate) inline JavaScript content.
     *
     * Must include `<script>` surrounding element.
     *
     * @param string       $js     The JavaScript to add.
     * @param LambdaHelper $helper For rendering strings in the current context.
     */
    public function addJs(string $js, ?LambdaHelper $helper = null): void
    {
        if ($helper instanceof \Mustache_LambdaHelper) {
            $js = $helper->render($js);
        }
        self::$js .= $js;
    }

    /**
     * Get the saved inline JavaScript content and purge the store.
     */
    public function js(): string
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
     * @param string       $js     The JavaScript to add.
     * @param LambdaHelper $helper For rendering strings in the current context.
     */
    public function addJsRequirement(string $js, ?LambdaHelper $helper = null): void
    {
        $js  = trim($js);
        $key = md5($js);

        if (!isset(self::$jsRequirements[$key])) {
            if ($helper instanceof \Mustache_LambdaHelper) {
                $js = $helper->render($js);
            }

            self::$jsRequirements[$key] = $js;
        }
    }

    /**
     * Get the JavaScript requirements and purge the store.
     */
    public function jsRequirements(): string
    {
        $req = implode("\n", self::$jsRequirements);
        self::$jsRequirements = [];
        return $req;
    }

    /**
     * Empty the CSS assets queue.
     */
    public function purgeCss(): void
    {
        self::$css = '';
        self::$cssRequirements = [];
    }

    /**
     * Enqueue (concatenate) inline CSS content.
     *
     * Must include `<style>` surrounding element.
     *
     * @param string       $css    The CSS string to add.
     * @param LambdaHelper $helper For rendering strings in the current context.
     */
    public function addCss(string $css, ?LambdaHelper $helper = null): void
    {
        if ($helper instanceof \Mustache_LambdaHelper) {
            $css = $helper->render($css);
        }
        self::$css .= $css;
    }

    /**
     * Get the saved inline CSS content and purge the store.
     */
    public function css(): string
    {
        $css = self::$css;
        self::$css = '';
        return $css;
    }

    /**
     * Enqueue an external CSS file.
     *
     * Must include `<link />` or surrounding `<style>` element.
     *
     * @param string       $css    The CSS requirements.
     * @param LambdaHelper $helper For rendering strings in the current context.
     */
    public function addCssRequirement(string $css, ?LambdaHelper $helper = null): void
    {
        $css = trim($css);
        $key = md5($css);

        if (!isset(self::$cssRequirements[$key])) {
            if ($helper instanceof \Mustache_LambdaHelper) {
                $css = $helper->render($css);
            }

            self::$cssRequirements[$key] = $css;
        }
    }

    /**
     * Get the CSS requirements and purge the store.
     */
    public function cssRequirements(): string
    {
        $req = implode("\n", self::$cssRequirements);
        self::$cssRequirements = [];
        return $req;
    }

    /**
     * Empty the all asset queues.
     */
    public function purgeAssets(): void
    {
        $this->purgeJs();
        $this->purgeCss();
    }
}

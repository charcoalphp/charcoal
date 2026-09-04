<?php

namespace Charcoal\Tests\Admin\Mustache;

use Assetic\Asset\AssetCollection;
use Assetic\Asset\StringAsset;
use Assetic\AssetManager;
use Charcoal\Admin\Mustache\AssetsHelpers;
use Charcoal\Tests\AbstractTestCase;
use Mustache\Engine as MustacheEngine;

/**
 *
 */
class AssetsHelpersTest extends AbstractTestCase
{
    /**
     * @var AssetsHelpers
     */
    private $obj;

    /**
     * @var AssetManager
     */
    private $assets;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->assets = new AssetManager();
        $this->assets->set('css', new AssetCollection([
            new StringAsset('.login { color: red; }'),
        ]));

        $this->obj = new AssetsHelpers([
            'assets' => $this->assets,
        ]);
    }

    /**
     * @return void
     */
    public function testDottedOutputWithMustache()
    {
        $mustache = new MustacheEngine([
            'helpers'          => $this->obj->toArray(),
            'strict_callables' => true,
        ]);

        $this->assertEquals(
            '.login { color: red; }',
            $mustache->render('{{& assets.output.css }}')
        );
    }

    /**
     * @return void
     */
    public function testDottedOutputOfEmptyCollectionIsEmpty()
    {
        $this->assets->set('js', new AssetCollection());

        $mustache = new MustacheEngine([
            'helpers'          => $this->obj->toArray(),
            'strict_callables' => true,
        ]);

        $this->assertSame('', $mustache->render('{{& assets.output.js }}'));
    }

    /**
     * @return void
     */
    public function testDottedOutputPreservesMustacheLikeJs()
    {
        $this->assets->set('js', new AssetCollection([
            new StringAsset('var x = "{{ name }}";'),
        ]));

        $mustache = new MustacheEngine([
            'helpers'          => $this->obj->toArray(),
            'strict_callables' => true,
        ]);

        $this->assertSame(
            'var x = "{{ name }}";',
            $mustache->render('{{& assets.output.js }}')
        );
    }

    /**
     * @return void
     */
    public function testInvokeWithoutArgumentsReturnsSelf()
    {
        $this->assertSame($this->obj, ($this->obj)());
    }
}

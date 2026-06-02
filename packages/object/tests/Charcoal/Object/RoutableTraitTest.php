<?php

namespace Charcoal\Tests\Object;

use DateTime;

// From Pimple
use Pimple\Container;

// From 'charcoal-translator'
use Charcoal\Translator\Translator;
use Charcoal\Translator\LocalesManager;

// From 'charcoal-object'
use Charcoal\Object\ObjectRoute;
use Charcoal\Object\RoutableTrait;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Object\ContainerProvider;
use Charcoal\Tests\Object\Mocks\RoutableClass as RoutableObject;

/**
 *
 */
class RoutableTraitTest extends AbstractTestCase
{
    /**
     * Tested Class.
     *
     * @var RoutableTrait
     */
    private \Charcoal\Tests\Object\Mocks\RoutableClass $obj;

    /**
     * Store the service container.
     */
    private ?\Pimple\Container $container = null;

    /**
     * Store the translator service.
     */
    private ?\Charcoal\Translator\Translator $translator = null;

    /**
     * Set up the test.
     */
    public function setUp(): void
    {
        $container = $this->container();

        $route = $container['model/factory']->get(ObjectRoute::class);
        if ($route->source()->tableExists() === false) {
            $route->source()->createTable();
        }

        $this->obj = new RoutableObject([
            'factory'    => $container['model/factory'],
            'translator' => $this->translator()
        ]);
    }

    public function testSlugPattern(): void
    {
        // $this->assertEquals('', $this->obj->slugPattern());
        $ret = $this->obj->setSlugPattern('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', (string)$this->obj->slugPattern());

        $this->obj->setSlugPattern(null);
        // $this->assertEquals('', $this->obj->slugPattern());
    }

    public function testSlugPatternRoutable(): void
    {
        $this->obj->setMetadata([
            'routable' => [
                'pattern' => 'foofoo'
            ]
        ]);
        $this->assertEquals('foofoo', $this->obj->slugPattern());
    }

    public function testSlugPatternWithoutRoutable(): void
    {
        $this->obj->setMetadata([
            'routable'     => null,
            'slug_pattern' => 'barbar'
        ]);
        $this->assertEquals('barbar', $this->obj->slugPattern());
    }

    public function testSlugPatternWithoutMetadata(): void
    {
        $this->obj->setMetadata([]);

        $this->expectException('\Exception');
        $this->obj->slugPattern();
    }

    public function testSlugPrefix(): void
    {
        $this->assertEquals('', $this->obj->slugPrefix());

        $this->obj->setMetadata([
            'routable' => [
                'prefix' => 'barfoo'
            ]
        ]);
        $this->assertEquals('barfoo', $this->obj->slugPrefix());
    }

    public function testSlugSuffix(): void
    {
        $this->assertEquals('', $this->obj->slugSuffix());

        $this->obj->setMetadata([
            'routable' => [
                'suffix' => 'barfoo'
            ]
        ]);
        $this->assertEquals('barfoo', $this->obj->slugSuffix());
    }

    public function testIsSlugEditableIsFalseByDefault(): void
    {
        $this->assertFalse($this->obj->isSlugEditable());
    }

    public function testIsSlugEditable(): void
    {
        $this->obj->setMetadata([
            'routable' => [
                'editable' => true
            ]
        ]);
        $this->assertTrue($this->obj->isSlugEditable());
    }

    public function testSlug(): void
    {
        $this->assertNull($this->obj->getSlug());

        $ret = $this->obj->setSlug('test123');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('test123', $this->obj->getSlug());

        $this->obj->setSlug(null);
        $this->assertNull($this->obj->getSlug());
    }

    public function testGenerateSlug(): void
    {
        $this->container();

        $this->obj->setMetadata([
            'routable' => [
                'pattern' => 'FooFoo',
                'prefix'  => 'bar-',
                'suffix'  => '-baz'
            ]
        ]);

        $ret = $this->obj->generateSlug();
        $this->assertEquals('barfoofoobaz', (string)$ret);
    }

    /**
     *
     * @param  string $str  A dirty slug.
     * @param  string $slug A clean $str.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('providerSlugs')]
    public function testSlugify(string $str, string $slug): void
    {
        $this->assertEquals($slug, $this->obj->slugify($str));
    }

    public static function providerSlugs(): array
    {
        return [
            [ 'A B C', 'a-b-c' ],
            [ '_this_is_a_test_', 'this-is-a-test' ],
            [ 'Allö Bébé!', 'allo-bebe' ],
            [ '"Hello-#-{$}-£™¡¢∞§¶•ªº-World"', 'hello-world' ],
            [ '&quot;', 'quot' ],
            [ 'fr/14/Services Santé et Sécurité au Travail', 'fr/14/services-sante-et-securite-au-travail' ],
            [ 'fr/ 14/Services S   anté et Sécurité au Travail', 'fr/14/services-s-ante-et-securite-au-travail' ],
            [ 'ÓóÔô Œœ Ææ', 'oooo-oeoe-aeae']
        ];
    }

    private function translator(): \Charcoal\Translator\Translator
    {
        if (!$this->translator instanceof \Charcoal\Translator\Translator) {
            $this->translator = new Translator([
                'manager' => new LocalesManager([
                    'locales' => [
                        'en'  => [ 'locale' => 'en-US' ],
                        'fr'  => [ 'locale' => 'fr-CA' ]
                    ],
                    'default_language'   => 'en',
                    'fallback_languages' => [ 'en' ]
                ])
            ]);
        }

        return $this->translator;
    }

    /**
     * Set up the service container.
     */
    private function container(): \Pimple\Container
    {
        if (!$this->container instanceof \Pimple\Container) {
            $container = new Container();
            $containerProvider = new ContainerProvider();
            $containerProvider->registerBaseServices($container);
            $containerProvider->registerModelFactory($container);
            $containerProvider->registerModelCollectionLoader($container);

            $this->container = $container;
        }

        return $this->container;
    }
}

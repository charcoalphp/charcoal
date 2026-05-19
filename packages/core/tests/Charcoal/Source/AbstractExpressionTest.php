<?php

namespace Charcoal\Tests\Source;

use stdClass;
use DateTime;
use InvalidArgumentException;

// From 'charcoal-core'
use Charcoal\Source\AbstractExpression;
use Charcoal\Source\ExpressionInterface;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\CoreContainerIntegrationTrait;
use Charcoal\Tests\Source\ExpressionTestTrait;

/**
 * Test {@see AbstractExpression}.
 */
class AbstractExpressionTest extends AbstractTestCase
{
    use CoreContainerIntegrationTrait;

    /**
     * Create expression for testing.
     *
     * @return AbstractExpression
     */
    final protected function createExpression()
    {
        return $this->getMockForAbstractClass(AbstractExpression::class);
    }

    /**
     * Test the "name" property.
     *
     * Assertions:
     * 1. Default state
     * 2. Mutated state
     * 3. Chainable method
     */
    public function testName(): void
    {
        $obj = $this->createExpression();

        /** 1. Default Value */
        $this->assertNull($obj->name());

        /** 2. Mutated Value */
        $that = $obj->setName('foo');
        $this->assertEquals('foo', $obj->name());

        /** 3. Chainable */
        $this->assertSame($obj, $that);
    }

    /**
     * Test "name" property with invalid value.
     */
    public function testNameWithInvalidValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->createExpression()->setName(0);
    }

    /**
     * Test the "active" property.
     *
     * Assertions:
     * 1. Default state
     * 2. Mutated state
     * 3. Chainable method
     * 4. Cast value to boolean
     */
    public function testActive(): void
    {
        $obj = $this->createExpression();

        /** 1. Default Value */
        $this->assertTrue($obj->active());

        /** 2. Mutated Value */
        $that = $obj->setActive(false);
        $this->assertFalse($obj->active());

        /** 3. Chainable */
        $this->assertSame($obj, $that);

        /** 4. Cast value to boolean */
        $obj->setActive(1);
        $this->assertTrue($obj->active());

        $obj->setActive(0);
        $this->assertFalse($obj->active());
    }

    /**
     * Test value parsing.
     *
     *
     * @param  mixed $value    The value to test.
     * @param  mixed $expected The expected result.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideParsableValues')]
    public function testParseValue($value, int|string|bool|null $expected): void
    {
        $obj = $this->createExpression();

        $this->assertEquals($expected, $obj::parseValue($value));
    }

    /**
     * Provide data for value parsing.
     *
     * @used-by self::testParseValue()
     */
    public function provideParsableValues(): array
    {
        $container = $this->getContainer();

        $prop = $container['property/factory']->create('date-time');
        $prop->setVal('13 July 2004');
        $time = new DateTime('8 June 1995');

        return [
            'Null Type'              => [ null, null ],
            'Integer Type'           => [ 42, 42 ],
            'Integer String'         => [ '3', '3' ],
            'Boolean Type'           => [ true, true ],
            'Boolean String (TRUE)'  => [ 'true', true ],
            'Boolean String (FALSE)' => [ 'false', false ],
            'Date/Time Object'       => [ $time, '1995-06-08 00:00:00' ],
            'Date/Time Property'     => [ $prop, '2004-07-13 00:00:00' ],
        ];
    }

    /**
     * Test value quoting.
     *
     *
     * @param mixed $value    The value to test.
     * @param  mixed $expected The expected result.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideQuotableValues')]
    public function testQuoteValue(int|string|bool|\stdClass|array|null $value, int|string|\stdClass|array|null $expected): void
    {
        $obj = $this->createExpression();

        $this->assertEquals($expected, $obj::quoteValue($value));
    }

    /**
     * Provide data for value quoting.
     *
     * @used-by self::testQuoteValue()
     */
    public static function provideQuotableValues(): array
    {
        $obj = new stdClass();

        return [
            'Null Type'       => [ null, null ],
            'Array Type'      => [ [ 42 ], [ 42 ] ],
            'Integer Type'    => [ 42, 42 ],
            'Integer String'  => [ '3', '3' ],
            'Quotable String' => [ 'Foo "Qux" Baz', '"Foo &quot;Qux&quot; Baz"' ],
            'Boolean Type'    => [ true, 1 ],
            'Boolean String'  => [ 'false', 0 ],
            'Object Type'     => [ $obj, $obj ],
        ];
    }

    /**
     * Test field quoting.
     *
     *
     * @param  mixed $fieldName The field name.
     * @param  mixed $tableName The table name.
     * @param  mixed $expected  The expected identifier.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideQuotableIdentifiers')]
    public function testQuoteIdentifier(?string $fieldName, ?string $tableName, string $expected): void
    {
        $obj = $this->createExpression();

        $this->assertEquals($expected, $obj::quoteIdentifier($fieldName, $tableName, $expected));
    }

    /**
     * Provide data for field quoting.
     *
     * @used-by self::testQuoteIdentifier()
     */
    public static function provideQuotableIdentifiers(): array
    {
        return [
            [ null,   null,   ''          ],
            [ '',     null,   ''          ],
            [ '*',    null,   '*'         ],
            [ 'col',  null,   '`col`'     ],
            [ '*',    'tbl',  'tbl.*'     ],
            [ 'col',  'tbl',  'tbl.`col`' ],
        ];
    }

    /**
     * Test field quoting with invalid field name.
     */
    public function testQuoteIdentifierWithInvalidFieldName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $obj = $this->createExpression();
        $obj::quoteIdentifier([]);
    }

    /**
     * Test field quoting with blank table name.
     */
    public function testQuoteIdentifierWithBlankTableName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $obj = $this->createExpression();
        $obj::quoteIdentifier('foo', '');
    }

    /**
     * Test field quoting with invalid table name.
     */
    public function testQuoteIdentifierWithInvalidTableName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $obj = $this->createExpression();
        $obj::quoteIdentifier('foo', []);
    }

    /**
     * Test value differentiation.
     *
     *
     * @param  mixed $a        The custom value.
     * @param  mixed $b        The default value.
     * @param  mixed $expected The expected result.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideDiffValues')]
    public function testDiffValues(int $a, int|string $b, int $expected): void
    {
        $obj = $this->createExpression();

        $this->assertEquals($expected, $obj::diffValues($a, $b));
    }

    /**
     * Provide data for value differentiation.
     *
     * @used-by self::testDiffValues()
     */
    public static function provideDiffValues(): array
    {
        return [
            'Same Type'      => [ 5, 5, 0 ],
            'Different Type' => [ 5, '5', 1 ],
        ];
    }

    /**
     * Test callable detection.
     *
     *
     * @param  mixed $value    The value to test.
     * @param  mixed $expected The expected result.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideCallableValues')]
    public function testIsCallable(string|\Closure|null $value, bool $expected): void
    {
        $obj = $this->createExpression();

        $this->assertEquals($expected, $obj::isCallable($value));
    }

    /**
     * Provide data for callable detection.
     *
     * @used-by self::testIsCallable()
     */
    public static function provideCallableValues(): array
    {
        return [
            'Null Type'   => [ null, false ],
            'String Type' => [ 'strval', false ],
            'Closure'     => [ function (): void {
            }, true ],
        ];
    }
}

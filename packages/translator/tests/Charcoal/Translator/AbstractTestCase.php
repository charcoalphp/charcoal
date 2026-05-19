<?php

declare(strict_types=1);

namespace Charcoal\Tests\Translator;

use Charcoal\Tests\Translator\ReflectionsTrait;
use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Basic Charcoal Test
 */
abstract class AbstractTestCase extends BaseTestCase
{
    use ReflectionsTrait;
}

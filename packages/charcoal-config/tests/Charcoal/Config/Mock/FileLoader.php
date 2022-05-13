<?php

namespace Charcoal\Tests\Config\Mock;

use InvalidArgumentException;

// From 'charcoal-config'
use Charcoal\Config\FileAwareInterface;
use Charcoal\Config\FileAwareTrait;

/**
 * Mock object of {@see \Charcoal\Tests\Config\Mixin\FileAwareTest}
 */
class FileLoader implements FileAwareInterface
{
    use FileAwareTrait;
}

<?php

namespace Charcoal\Tests\Mock;

use Charcoal\Source\StorableInterface;
use Charcoal\Source\StorableTrait;

use Charcoal\Tests\Mock\SourceMock;

class StorableMock implements StorableInterface
{
    use StorableTrait;

    protected function createSource()
    {
        return new SourceMock();
    }
}
